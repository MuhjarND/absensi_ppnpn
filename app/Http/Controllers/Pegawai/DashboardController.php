<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Attendance;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $todayAttendance = $user->todayAttendance();
        $pendingClockOutAttendance = $user->pendingClockOutAttendance();

        $shift = $user->getActiveShift();
        $isOffToday = $user->isScheduledOffByDate(now());

        // Monthly summary
        $monthlyAttendances = Attendance::byUser($user->id)
            ->byMonth($today->year, $today->month)
            ->get();

        $totalHadir = $monthlyAttendances->whereIn('status', ['hadir', 'terlambat'])->count();
        $totalTerlambat = $monthlyAttendances->where('status', 'terlambat')->count();
        $totalAlpha = $monthlyAttendances->where('status', 'alpha')->count();
        $totalIzin = $monthlyAttendances->whereIn('status', ['izin', 'sakit'])->count();

        return view('pegawai.dashboard', compact(
            'user',
            'todayAttendance',
            'pendingClockOutAttendance',
            'shift',
            'isOffToday',
            'totalHadir',
            'totalTerlambat',
            'totalAlpha',
            'totalIzin'
        ));
    }
}
