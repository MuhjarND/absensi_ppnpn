<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\LeaveRequest;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class LeaveRequestController extends Controller
{
    public function index()
    {
        $requests = LeaveRequest::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('pegawai.leave-requests.index', compact('requests'));
    }

    public function create()
    {
        return view('pegawai.leave-requests.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:izin,sakit',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $data = [
            'user_id' => Auth::id(),
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
        ];

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('leave-attachments', 'public');
        }

        $leaveRequest = LeaveRequest::create($data);

        $notificationResult = $this->notifyMonitoringUsers($leaveRequest);
        $message = 'Pengajuan izin/sakit berhasil dikirim.';
        if ($notificationResult['has_error']) {
            $message .= ' Notifikasi WhatsApp ke monitoring belum seluruhnya terkirim.';
        }

        return redirect()->route('pegawai.leave-requests.index')
            ->with('success', $message);
    }

    private function notifyMonitoringUsers(LeaveRequest $leaveRequest)
    {
        $fonnteToken = config('services.fonnte.token');
        $fonnteEndpoint = config('services.fonnte.endpoint');

        if (empty($fonnteToken)) {
            Log::warning('Fonnte token is missing. Skip monitoring notification.');
            return ['has_error' => true];
        }

        $monitoringUsers = User::whereHas('role', function ($query) {
            $query->where('name', 'monitoring');
        })
            ->where('is_active', true)
            ->whereNotNull('phone')
            ->get();

        if ($monitoringUsers->isEmpty()) {
            return ['has_error' => false];
        }

        $pegawai = $leaveRequest->user()->first();
        $message = $this->buildMonitoringMessage($leaveRequest, $pegawai ? $pegawai->name : 'Pegawai');

        $hasError = false;

        foreach ($monitoringUsers as $monitoringUser) {
            $target = $this->normalizeWhatsappNumber($monitoringUser->phone);

            if (empty($target)) {
                $hasError = true;
                continue;
            }

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
                    $hasError = true;
                    Log::warning('Failed to send leave request notification to monitoring', [
                        'monitoring_user_id' => $monitoringUser->id,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    continue;
                }

                $payload = $response->json();
                $isSent = is_array($payload) ? (bool) ($payload['status'] ?? false) : false;
                if (!$isSent) {
                    $hasError = true;
                    Log::warning('Fonnte response indicates failure for monitoring notification', [
                        'monitoring_user_id' => $monitoringUser->id,
                        'payload' => $payload,
                    ]);
                }
            } catch (Throwable $e) {
                $hasError = true;
                Log::error('Exception when sending leave request notification to monitoring', [
                    'monitoring_user_id' => $monitoringUser->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return ['has_error' => $hasError];
    }

    private function normalizeWhatsappNumber($phone)
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (empty($digits)) {
            return null;
        }

        if (substr($digits, 0, 1) === '0') {
            return '62' . substr($digits, 1);
        }

        if (substr($digits, 0, 2) === '62') {
            return $digits;
        }

        return $digits;
    }

    private function buildMonitoringMessage(LeaveRequest $leaveRequest, $pegawaiName)
    {
        $startDate = $leaveRequest->start_date ? $leaveRequest->start_date->format('d/m/Y') : '-';
        $endDate = $leaveRequest->end_date ? $leaveRequest->end_date->format('d/m/Y') : '-';
        $detailUrl = url('/monitoring/leave-requests');

        return "Notifikasi Pengajuan Izin/Sakit\n\n" .
            "Pegawai: {$pegawaiName}\n" .
            "Tipe: " . strtoupper($leaveRequest->type) . "\n" .
            "Periode: {$startDate} - {$endDate}\n" .
            "Status: PENDING\n\n" .
            "Silakan review pada:\n{$detailUrl}";
    }
}
