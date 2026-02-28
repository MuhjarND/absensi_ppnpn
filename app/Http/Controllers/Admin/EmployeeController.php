<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\User;
use App\Role;
use App\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['role', 'shift'])
            ->whereHas('role', function ($q) {
                $q->where('name', '!=', 'admin');
            });

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('nip', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $employees = $query->orderBy('name')->paginate(15);

        return view('admin.employees.index', compact('employees'));
    }

    public function create()
    {
        $roles = Role::where('name', '!=', 'admin')->get();
        $shifts = Shift::all();
        return view('admin.employees.create', compact('roles', 'shifts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'nip' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:255',
            'role_id' => 'required|exists:roles,id',
            'is_security' => 'boolean',
            'shift_id' => 'nullable|exists:shifts,id',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'nip' => $request->nip,
            'phone' => $request->phone,
            'position' => $request->position,
            'role_id' => $request->role_id,
            'is_security' => $request->is_security ?? false,
            'shift_id' => $request->shift_id,
            'is_active' => true,
        ]);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Pegawai berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $employee = User::findOrFail($id);
        $roles = Role::where('name', '!=', 'admin')->get();
        $shifts = Shift::all();
        return view('admin.employees.edit', compact('employee', 'roles', 'shifts'));
    }

    public function update(Request $request, $id)
    {
        $employee = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'nip' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:255',
            'role_id' => 'required|exists:roles,id',
            'is_security' => 'boolean',
            'shift_id' => 'nullable|exists:shifts,id',
        ]);

        $data = $request->only(['name', 'email', 'nip', 'phone', 'position', 'role_id', 'shift_id']);
        $data['is_security'] = $request->is_security ?? false;
        $data['is_active'] = $request->is_active ?? true;

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $employee->update($data);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Data pegawai berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $employee = User::findOrFail($id);
        $employee->update(['is_active' => false]);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Pegawai berhasil dinonaktifkan.');
    }

    public function sendCredentials($id)
    {
        $employee = User::findOrFail($id);

        if (!$employee->is_active) {
            return redirect()->back()->with('error', 'Pegawai tidak aktif. Tidak dapat mengirim kredensial.');
        }

        if (empty($employee->phone)) {
            return redirect()->back()->with('error', 'Nomor WhatsApp pegawai belum diisi.');
        }

        $fonnteToken = config('services.fonnte.token');
        $fonnteEndpoint = config('services.fonnte.endpoint');

        if (empty($fonnteToken)) {
            return redirect()->back()->with('error', 'Token Fonnte belum dikonfigurasi.');
        }

        $target = $this->normalizeWhatsappNumber($employee->phone);
        if (empty($target)) {
            return redirect()->back()->with('error', 'Nomor WhatsApp pegawai tidak valid.');
        }

        $plainPassword = Str::random(10);
        $employee->update(['password' => Hash::make($plainPassword)]);

        $message = $this->buildCredentialsMessage($employee, $plainPassword);

        try {
            $response = Http::timeout(20)
                ->withHeaders([
                    'Authorization' => $fonnteToken,
                ])
                ->asForm()
                ->post($fonnteEndpoint, [
                    'target' => $target,
                    'message' => $message,
                ]);

            if (!$response->successful()) {
                Log::warning('Fonnte request failed', [
                    'employee_id' => $employee->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return redirect()->back()->with('error', 'Gagal mengirim WhatsApp: respons API tidak valid.');
            }

            $payload = $response->json();
            $isSent = is_array($payload) ? (bool) ($payload['status'] ?? false) : false;

            if (!$isSent) {
                $reason = is_array($payload) ? ($payload['reason'] ?? $payload['message'] ?? 'Tidak diketahui') : 'Tidak diketahui';
                return redirect()->back()->with('error', 'Gagal mengirim WhatsApp: ' . $reason);
            }
        } catch (Throwable $e) {
            Log::error('Failed to send employee credentials via Fonnte', [
                'employee_id' => $employee->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengirim WhatsApp.');
        }

        return redirect()->back()->with('success', 'Kredensial berhasil dikirim ke WhatsApp pegawai.');
    }

    private function normalizeWhatsappNumber($phone)
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (empty($digits)) {
            return null;
        }

        if (Str::startsWith($digits, '0')) {
            return '62' . substr($digits, 1);
        }

        if (Str::startsWith($digits, '62')) {
            return $digits;
        }

        return $digits;
    }

    private function buildCredentialsMessage(User $employee, $plainPassword)
    {
        $loginUrl = url('/login');

        return "Halo {$employee->name},\n\n" .
            "Akun Absensi PPNPN Anda:\n" .
            "Username: {$employee->email}\n" .
            "Password: {$plainPassword}\n\n" .
            "Login di: {$loginUrl}\n" .
            "Silakan segera login dan ubah password Anda.";
    }
}
