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
        $search = trim((string) $request->search);

        $users = User::whereHas('role', function ($q) {
            $q->where('name', 'pegawai');
        })
            ->where('is_active', true)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('nip', 'like', '%' . $search . '%');
                });
            })
            ->withCount([
                'attendances as total_hadir' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                        ->where('status', 'hadir');
                },
                'attendances as total_terlambat' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                        ->where('status', 'terlambat');
                },
                'attendances as total_izin' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                        ->whereIn('status', ['izin', 'sakit']);
                },
                'attendances as total_alpha' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                        ->where('status', 'alpha');
                },
            ])
            ->orderBy('name')
            ->paginate(20);

        return view('monitoring.reports', compact('users', 'startDate', 'endDate'));
    }

    public function detail(Request $request, $userId)
    {
        $employee = User::with('role', 'shift')->findOrFail($userId);

        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();

        $attendances = Attendance::with(['clockInLocation', 'clockOutLocation'])
            ->byUser($userId)
            ->byDateRange($startDate->toDateString(), $endDate->toDateString())
            ->orderBy('date', 'desc')
            ->paginate(20);

        return view('monitoring.employee-detail', compact('employee', 'attendances', 'startDate', 'endDate'));
    }
}
