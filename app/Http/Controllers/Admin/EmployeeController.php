<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\User;
use App\Role;
use App\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
}
