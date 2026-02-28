<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\User;
use App\Attendance;
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

        $recentAttendances = Attendance::with('user')
            ->where('date', $today)
            ->orderBy('clock_in', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalPegawai',
            'hadirHariIni',
            'terlambatHariIni',
            'belumAbsen',
            'recentAttendances'
        ));
    }
}
