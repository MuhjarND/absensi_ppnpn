<?php

namespace App\Http\Controllers\Monitoring;

use App\Http\Controllers\Controller;
use App\Attendance;
use App\User;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        list($startDate, $endDate, $search) = $this->resolveReportFilters($request);

        $users = $this->buildReportUsersQuery($startDate, $endDate, $search)
            ->paginate(20);
        $this->appendWorkDurationSummaries($users);

        return view('monitoring.reports', compact('users', 'startDate', 'endDate'));
    }

    public function exportPdf(Request $request)
    {
        if (!class_exists(Dompdf::class)) {
            return redirect()->route('monitoring.reports', $request->query())
                ->with('error', 'Library PDF belum tersedia di server. Jalankan composer install terlebih dahulu.');
        }

        list($startDate, $endDate, $search) = $this->resolveReportFilters($request);

        $users = $this->buildReportUsersQuery($startDate, $endDate, $search)->get();
        $this->appendWorkDurationSummaries($users);
        $kopImageData = $this->getKopImageData();

        $dompdf = new Dompdf();
        if (method_exists($dompdf, 'getOptions') && $dompdf->getOptions()) {
            $dompdf->getOptions()->set('isHtml5ParserEnabled', true);
            $dompdf->getOptions()->set('isRemoteEnabled', true);
        }
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->loadHtml(view('monitoring.reports-pdf', [
            'users' => $users,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'search' => $search,
            'kopImageData' => $kopImageData,
            'generatedAt' => now(),
        ])->render());
        $dompdf->render();

        $fileName = 'laporan-monitoring-' . $startDate->format('Ymd') . '-' . $endDate->format('Ymd') . '.pdf';
        $disposition = $request->boolean('print') ? 'inline' : 'attachment';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition . '; filename="' . $fileName . '"',
        ]);
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

        $workDurationSummary = $this->calculateWorkDurationSummary(
            Attendance::byUser($userId)
                ->byDateRange($startDate->toDateString(), $endDate->toDateString())
                ->get(['clock_in', 'clock_out'])
        );

        return view('monitoring.employee-detail', compact(
            'employee',
            'attendances',
            'startDate',
            'endDate',
            'workDurationSummary'
        ));
    }

    private function resolveReportFilters(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();
        $search = trim((string) $request->search);

        return [$startDate, $endDate, $search];
    }

    private function buildReportUsersQuery(Carbon $startDate, Carbon $endDate, $search = '')
    {
        return User::whereHas('role', function ($q) {
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
            ->with([
                'attendances' => function ($query) use ($startDate, $endDate) {
                    $query->select('id', 'user_id', 'clock_in', 'clock_out')
                        ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
                },
            ])
            ->orderBy('name');
    }

    private function appendWorkDurationSummaries($users)
    {
        $collection = method_exists($users, 'getCollection') ? $users->getCollection() : $users;

        $collection->transform(function ($user) {
            $summary = $this->calculateWorkDurationSummary($user->attendances ?? collect());
            $user->average_daily_work_minutes = $summary['average_daily_minutes'];
            $user->average_daily_work_duration = $summary['average_daily_duration'];
            $user->total_work_minutes = $summary['total_minutes'];
            $user->total_work_duration = $summary['total_duration'];

            return $user;
        });

        if (method_exists($users, 'setCollection')) {
            $users->setCollection($collection);
        }

        return $users;
    }

    private function calculateWorkDurationSummary($attendances)
    {
        $completedAttendances = $attendances->filter(function ($attendance) {
            return $attendance->clock_in && $attendance->clock_out;
        });

        $totalMinutes = $completedAttendances->sum(function ($attendance) {
            return $attendance->clock_out->diffInMinutes($attendance->clock_in);
        });
        $completedDays = $completedAttendances->count();
        $averageDailyMinutes = $completedDays > 0 ? (int) round($totalMinutes / $completedDays) : 0;

        return [
            'total_minutes' => $totalMinutes,
            'total_duration' => Attendance::formatWorkDuration($totalMinutes),
            'average_daily_minutes' => $averageDailyMinutes,
            'average_daily_duration' => Attendance::formatWorkDuration($averageDailyMinutes),
        ];
    }

    private function getKopImageData()
    {
        $kopPath = public_path('kop_laporan.jpg');

        if (!file_exists($kopPath)) {
            return null;
        }

        $mimeType = function_exists('mime_content_type') ? mime_content_type($kopPath) : 'image/jpeg';

        return 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($kopPath));
    }
}
