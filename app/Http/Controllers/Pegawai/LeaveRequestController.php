<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LeaveRequestController extends Controller
{
    public function index()
    {
        $leaveRequests = LeaveRequest::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('pegawai.leave-requests.index', compact('leaveRequests'));
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

        LeaveRequest::create($data);

        return redirect()->route('pegawai.leave-requests.index')
            ->with('success', 'Pengajuan izin/sakit berhasil dikirim.');
    }
}
