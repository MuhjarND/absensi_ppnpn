<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Attendance;
use App\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceReportController extends Controller
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

        return view('admin.reports.index', compact('attendances', 'employees', 'startDate', 'endDate'));
    }

    public function export(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();

        $attendances = Attendance::with(['user', 'shift'])
            ->byDateRange($startDate->toDateString(), $endDate->toDateString())
            ->orderBy('date', 'desc')
            ->get();

        $filename = 'rekap_absensi_' . $startDate->format('Ymd') . '_' . $endDate->format('Ymd') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($attendances) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No', 'Tanggal', 'Nama', 'NIP', 'Jam Masuk', 'Jam Pulang', 'Status', 'Shift']);

            $no = 1;
            foreach ($attendances as $att) {
                fputcsv($file, [
                    $no++,
                    $att->date->format('d/m/Y'),
                    $att->user->name,
                    $att->user->nip ?? '-',
                    $att->clock_in ? $att->clock_in->format('H:i:s') : '-',
                    $att->clock_out ? $att->clock_out->format('H:i:s') : '-',
                    ucfirst($att->status),
                    $att->shift ? $att->shift->name : 'Reguler',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
