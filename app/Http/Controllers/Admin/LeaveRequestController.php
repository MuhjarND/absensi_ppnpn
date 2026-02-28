<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = LeaveRequest::with(['user', 'approvedByUser']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $leaveRequests = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.leave-requests.index', compact('leaveRequests'));
    }

    public function approve($id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);
        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Pengajuan izin/sakit disetujui.');
    }

    public function reject(Request $request, $id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);
        $leaveRequest->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->back()->with('success', 'Pengajuan izin/sakit ditolak.');
    }
}
