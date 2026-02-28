<?php

namespace App\Http\Controllers\Monitoring;

use App\Http\Controllers\Controller;
use App\Attendance;
use App\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();

        $query = Attendance::with(['user', 'shift'])
            ->byDateRange($startDate->toDateString(), $endDate->toDateString());

        if ($request->user_id) {
            $query->byUser($request->user_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $attendances = $query->orderBy('date', 'desc')->paginate(20);

        $employees = User::whereHas('role', function ($q) {
            $q->where('name', 'pegawai');
        })->where('is_active', true)->orderBy('name')->get();

        return view('monitoring.reports', compact('attendances', 'employees', 'startDate', 'endDate'));
    }

    public function detail($userId)
    {
        $employee = User::with('role', 'shift')->findOrFail($userId);

        $month = request('month', now()->month);
        $year = request('year', now()->year);

        $attendances = Attendance::byUser($userId)
            ->byMonth($year, $month)
            ->orderBy('date', 'desc')
            ->get();

        $summary = [
            'hadir' => $attendances->where('status', 'hadir')->count(),
            'terlambat' => $attendances->where('status', 'terlambat')->count(),
            'alpha' => $attendances->where('status', 'alpha')->count(),
            'izin' => $attendances->where('status', 'izin')->count(),
            'sakit' => $attendances->where('status', 'sakit')->count(),
        ];

        return view('monitoring.detail', compact('employee', 'attendances', 'summary', 'month', 'year'));
    }
}
