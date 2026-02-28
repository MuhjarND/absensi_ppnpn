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

        $shift = $user->getActiveShift();

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
            'shift',
            'totalHadir',
            'totalTerlambat',
            'totalAlpha',
            'totalIzin'
        ));
    }
}
