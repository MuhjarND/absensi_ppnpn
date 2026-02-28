<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\SecurityShiftWeeklySchedule;
use App\Shift;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SecurityShiftScheduleController extends Controller
{
    public function index(Request $request)
    {
        $selectedSecurityId = $request->security_id ? (int) $request->security_id : null;
        $dayOptions = $this->getDayOptions();

        $securityEmployees = User::where('is_security', true)
            ->where('is_active', true)
            ->whereHas('role', function ($query) {
                $query->where('name', 'pegawai');
            })
            ->orderBy('name')
            ->get();

        $shifts = Shift::orderBy('name')->get();
        $weeklyTemplates = $this->buildWeeklyTemplates($securityEmployees, $selectedSecurityId, $dayOptions);
        $weeklyPreset = $this->buildWeeklyPreset($selectedSecurityId, $dayOptions);

        return view('admin.security-schedules.index', compact(
            'securityEmployees',
            'shifts',
            'selectedSecurityId',
            'weeklyTemplates',
            'weeklyPreset',
            'dayOptions'
        ));
    }

    public function storeWeeklyTemplate(Request $request)
    {
        $dayOptions = $this->getDayOptions();
        $allowedShiftValues = ['off'];

        foreach (Shift::pluck('id')->all() as $shiftId) {
            $allowedShiftValues[] = (string) $shiftId;
        }

        $rules = [
            'weekly_user_id' => 'required|exists:users,id',
        ];

        foreach ($dayOptions as $day) {
            if ($day['index'] === 0) {
                $rules[$day['field']] = ['nullable', Rule::in($allowedShiftValues)];
            } else {
                $rules[$day['field']] = ['required', Rule::in($allowedShiftValues)];
            }
        }

        $request->validate($rules);

        $securityEmployee = $this->resolveSecurityEmployee($request->weekly_user_id);

        if (!$securityEmployee) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['weekly_user_id' => 'Pegawai yang dipilih bukan security aktif.']);
        }

        DB::transaction(function () use ($request, $securityEmployee) {
            foreach ($this->getDayOptions() as $day) {
                $selectedValue = $request->input($day['field']);

                // Minggu boleh kosong. Jika kosong, hapus jadwal Minggu yang ada.
                if ($day['index'] === 0 && ($selectedValue === null || $selectedValue === '')) {
                    SecurityShiftWeeklySchedule::where('user_id', $securityEmployee->id)
                        ->where('day_of_week', 0)
                        ->delete();
                    continue;
                }

                $isOff = $selectedValue === 'off';
                $shiftId = $isOff ? null : (int) $selectedValue;

                SecurityShiftWeeklySchedule::updateOrCreate(
                    [
                        'user_id' => $securityEmployee->id,
                        'day_of_week' => $day['index'],
                    ],
                    [
                        'shift_id' => $shiftId,
                        'is_off' => $isOff,
                        'created_by' => Auth::id(),
                    ]
                );
            }
        }, 3);

        return redirect()->back()->with('success', 'Template mingguan security berhasil disimpan.');
    }

    private function resolveSecurityEmployee($userId)
    {
        return User::where('id', $userId)
            ->where('is_security', true)
            ->where('is_active', true)
            ->whereHas('role', function ($query) {
                $query->where('name', 'pegawai');
            })
            ->first();
    }

    private function buildWeeklyPreset($selectedSecurityId, $dayOptions)
    {
        $preset = ['user_id' => $selectedSecurityId];
        foreach ($dayOptions as $day) {
            $preset[$day['field']] = null;
        }

        if (!$selectedSecurityId) {
            return $preset;
        }

        $weeklySchedules = SecurityShiftWeeklySchedule::where('user_id', $selectedSecurityId)
            ->get()
            ->keyBy('day_of_week');

        foreach ($dayOptions as $day) {
            if (isset($weeklySchedules[$day['index']])) {
                $schedule = $weeklySchedules[$day['index']];
                $preset[$day['field']] = $schedule->is_off ? 'off' : $schedule->shift_id;
            }
        }

        return $preset;
    }

    private function buildWeeklyTemplates($securityEmployees, $selectedSecurityId, $dayOptions)
    {
        $query = SecurityShiftWeeklySchedule::with('shift');

        if ($selectedSecurityId) {
            $query->where('user_id', $selectedSecurityId);
        }

        $weeklyByUser = $query->get()->groupBy('user_id');
        $rows = [];

        foreach ($securityEmployees as $security) {
            if ($selectedSecurityId && (int) $security->id !== (int) $selectedSecurityId) {
                continue;
            }

            $schedulesByDay = $weeklyByUser
                ->get($security->id, collect())
                ->keyBy('day_of_week');

            $days = [];
            foreach ($dayOptions as $day) {
                $schedule = $schedulesByDay->get($day['index']);

                if (!$schedule) {
                    $days[$day['field']] = '-';
                } elseif ($schedule->is_off) {
                    $days[$day['field']] = 'Libur';
                } elseif ($schedule->shift) {
                    $days[$day['field']] = $schedule->shift->name;
                } else {
                    $days[$day['field']] = '-';
                }
            }

            $rows[] = [
                'user' => $security,
                'days' => $days,
            ];
        }

        return $rows;
    }

    private function getDayOptions()
    {
        return [
            ['index' => 1, 'field' => 'monday_shift_id', 'label' => 'Senin'],
            ['index' => 2, 'field' => 'tuesday_shift_id', 'label' => 'Selasa'],
            ['index' => 3, 'field' => 'wednesday_shift_id', 'label' => 'Rabu'],
            ['index' => 4, 'field' => 'thursday_shift_id', 'label' => 'Kamis'],
            ['index' => 5, 'field' => 'friday_shift_id', 'label' => 'Jumat'],
            ['index' => 6, 'field' => 'saturday_shift_id', 'label' => 'Sabtu'],
            ['index' => 0, 'field' => 'sunday_shift_id', 'label' => 'Minggu'],
        ];
    }
}
