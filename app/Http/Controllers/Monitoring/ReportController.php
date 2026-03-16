<?php

namespace App\Http\Controllers\Monitoring;

use App\Http\Controllers\Controller;
use App\Attendance;
use App\User;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        list($startDate, $endDate, $search) = $this->resolveReportFilters($request);

        $users = $this->buildReportUsersQuery($startDate, $endDate, $search)
            ->paginate(20);

        return view('monitoring.reports', compact('users', 'startDate', 'endDate'));
    }

    public function exportPdf(Request $request)
    {
        list($startDate, $endDate, $search) = $this->resolveReportFilters($request);

        $users = $this->buildReportUsersQuery($startDate, $endDate, $search)->get();
        $kopImageData = $this->getKopImageData();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
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

        return view('monitoring.employee-detail', compact('employee', 'attendances', 'startDate', 'endDate'));
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
            ->orderBy('name');
    }

    private function getKopImageData()
    {
        $kopPath = public_path('kop.jpeg');

        if (!file_exists($kopPath)) {
            return null;
        }

        $mimeType = function_exists('mime_content_type') ? mime_content_type($kopPath) : 'image/jpeg';

        return 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($kopPath));
    }
}
