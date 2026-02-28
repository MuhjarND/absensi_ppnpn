<?php

namespace App\Http\Controllers\Monitoring;

use App\Http\Controllers\Controller;
use App\Attendance;
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

        $recentAttendances = Attendance::with(['user', 'shift'])
            ->where('date', $today)
            ->orderBy('clock_in', 'desc')
            ->get();

        return view('monitoring.dashboard', compact(
            'totalPegawai',
            'hadirHariIni',
            'terlambatHariIni',
            'belumAbsen',
            'sudahPulang',
            'recentAttendances'
        ));
    }
}
