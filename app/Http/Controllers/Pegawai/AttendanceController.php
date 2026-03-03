<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Attendance;
use App\Location;
use App\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $todayAttendance = $user->todayAttendance();
        $pendingClockOutAttendance = $user->pendingClockOutAttendance();
        $shift = $user->getActiveShift();
        $isOffToday = $user->isScheduledOffByDate(now());
        $locations = Location::active()->get();

        return view('pegawai.attendance', compact(
            'user',
            'todayAttendance',
            'pendingClockOutAttendance',
            'shift',
            'locations',
            'isOffToday'
        ));
    }

    public function clockIn(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'required|string', // base64 image
        ]);

        $user = Auth::user();
        $pendingClockOutAttendance = $user->pendingClockOutAttendance();

        if ($pendingClockOutAttendance) {
            return response()->json([
                'error' => 'Anda masih punya absen masuk yang belum ditutup. Silakan absen pulang terlebih dahulu.'
            ], 422);
        }

        if ($user->isScheduledOffByDate(now())) {
            return response()->json(['error' => 'Hari ini Anda dijadwalkan libur, absen masuk tidak diperlukan.'], 422);
        }

        // Check if already clocked in today
        if ($user->todayAttendance()) {
            return response()->json(['error' => 'Anda sudah melakukan absen masuk hari ini.'], 422);
        }

        // Validate location
        $locations = Location::active()->get();
        $validLocation = null;

        foreach ($locations as $location) {
            if ($location->isWithinRadius($request->latitude, $request->longitude)) {
                $validLocation = $location;
                break;
            }
        }

        if (!$validLocation) {
            $nearestLocation = $locations->first();
            $distance = $nearestLocation ? $nearestLocation->getDistanceFrom($request->latitude, $request->longitude) : 0;
            return response()->json([
                'error' => 'Anda tidak berada dalam jangkauan lokasi absensi. Jarak Anda: ' . $distance . ' meter dari lokasi terdekat.'
            ], 422);
        }

        // Save selfie photo
        $photoPath = $this->savePhoto($request->photo, $user->id, 'in');

        // Determine shift
        $shift = $user->getActiveShift();
        $now = Carbon::now();

        // Determine status
        $status = 'hadir';
        if ($shift) {
            $shiftStart = Carbon::today()->setTimeFromTimeString($shift->start_time);
            if ($now->gt($shiftStart)) {
                $status = 'terlambat';
            }
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $now->toDateString(),
            'clock_in' => $now,
            'clock_in_latitude' => $request->latitude,
            'clock_in_longitude' => $request->longitude,
            'clock_in_photo' => $photoPath,
            'clock_in_location_id' => $validLocation->id,
            'shift_id' => $shift ? $shift->id : null,
            'status' => $status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absen masuk berhasil dicatat pada ' . $now->format('H:i:s'),
            'status' => $status,
            'attendance' => $attendance,
        ]);
    }

    public function clockOut(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'required|string',
        ]);

        $user = Auth::user();
        $attendance = $user->pendingClockOutAttendance();

        if (!$attendance) {
            return response()->json(['error' => 'Tidak ada data absen masuk yang perlu dipulangkan.'], 422);
        }

        // Validate location
        $locations = Location::active()->get();
        $validLocation = null;

        foreach ($locations as $location) {
            if ($location->isWithinRadius($request->latitude, $request->longitude)) {
                $validLocation = $location;
                break;
            }
        }

        if (!$validLocation) {
            $nearestLocation = $locations->first();
            $distance = $nearestLocation ? $nearestLocation->getDistanceFrom($request->latitude, $request->longitude) : 0;
            return response()->json([
                'error' => 'Anda tidak berada dalam jangkauan lokasi absensi. Jarak Anda: ' . $distance . ' meter dari lokasi terdekat.'
            ], 422);
        }

        // Save selfie photo
        $photoPath = $this->savePhoto($request->photo, $user->id, 'out');

        $now = Carbon::now();

        $attendance->update([
            'clock_out' => $now,
            'clock_out_latitude' => $request->latitude,
            'clock_out_longitude' => $request->longitude,
            'clock_out_photo' => $photoPath,
            'clock_out_location_id' => $validLocation->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absen pulang berhasil dicatat pada ' . $now->format('H:i:s'),
        ]);
    }

    public function history(Request $request)
    {
        $user = Auth::user();

        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $attendances = Attendance::byUser($user->id)
            ->byMonth($year, $month)
            ->orderBy('date', 'desc')
            ->get();

        return view('pegawai.history', compact('attendances', 'month', 'year'));
    }

    private function savePhoto($base64Image, $userId, $type)
    {
        $image = str_replace('data:image/png;base64,', '', $base64Image);
        $image = str_replace('data:image/jpeg;base64,', '', $image);
        $image = str_replace('data:image/webp;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = 'attendance_' . $userId . '_' . $type . '_' . now()->format('Ymd_His') . '.jpg';

        $path = 'attendances/' . $imageName;
        Storage::disk('public')->put($path, base64_decode($image));

        return $path;
    }
}
