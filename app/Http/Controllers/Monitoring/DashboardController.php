<?php

namespace App\Http\Controllers\Monitoring;

use App\Http\Controllers\Controller;
use App\Attendance;
use App\LeaveRequest;
use App\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $totalPegawai = User::whereHas('role', function ($q) {
            $q->where('name', 'pegawai');
        })->where('is_active', true)->count();

        $hadirHariIni = Attendance::where('date', $today)
            ->whereNotNull('clock_in')
            ->count();

        $terlambatHariIni = Attendance::where('date', $today)
            ->where('status', 'terlambat')
            ->count();

        $belumAbsen = $totalPegawai - $hadirHariIni;

        $sudahPulang = Attendance::where('date', $today)
            ->whereNotNull('clock_out')
            ->count();

        $openAttendances = Attendance::with(['user.role', 'shift', 'clockInLocation'])
            ->whereNotNull('clock_in')
            ->whereNull('clock_out')
            ->whereHas('user', function ($query) {
                $query->where('is_active', true)
                    ->whereHas('role', function ($roleQuery) {
                        $roleQuery->where('name', 'pegawai');
                    });
            })
            ->orderBy('clock_in', 'desc')
            ->get();

        $izinAtauSakit = LeaveRequest::where('status', 'approved')
            ->whereDate('start_date', '<=', $today->toDateString())
            ->whereDate('end_date', '>=', $today->toDateString())
            ->count();

        $todayAttendances = Attendance::with(['user', 'shift', 'clockInLocation', 'clockOutLocation'])
            ->where(function ($query) use ($today) {
                $query->whereDate('date', $today->toDateString())
                    ->orWhere(function ($openQuery) use ($today) {
                        $openQuery->whereDate('date', '<', $today->toDateString())
                            ->whereNotNull('clock_in')
                            ->whereNull('clock_out');
                    });
            })
            ->orderBy('clock_in', 'desc')
            ->get();

        return view('monitoring.dashboard', compact(
            'totalPegawai',
            'hadirHariIni',
            'terlambatHariIni',
            'belumAbsen',
            'sudahPulang',
            'openAttendances',
            'izinAtauSakit',
            'todayAttendances'
        ));
    }
}
