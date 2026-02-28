<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Landing page
Route::get('/', function () {
    return redirect('/login');
});

// Public file access without symbolic link (serves files from storage/app/public).
Route::get('/storage/{path}', 'PublicStorageController@show')
    ->where('path', '.*')
    ->name('public.storage');

// Auth routes (register disabled - admin creates users)
Auth::routes(['register' => false]);

// Redirect based on role
Route::get('/home', 'HomeController@index')->name('home');

// =====================================================================
// ADMIN Routes
// =====================================================================
Route::prefix('admin')->middleware(['auth', 'role:admin'])->namespace('Admin')->group(function () {
    Route::get('/dashboard', 'DashboardController@index')->name('admin.dashboard');

    // CRUD Pegawai
    Route::resource('employees', 'EmployeeController')->names([
        'index' => 'admin.employees.index',
        'create' => 'admin.employees.create',
        'store' => 'admin.employees.store',
        'edit' => 'admin.employees.edit',
        'update' => 'admin.employees.update',
        'destroy' => 'admin.employees.destroy',
    ]);
    Route::post('/employees/{id}/send-credentials', 'EmployeeController@sendCredentials')
        ->name('admin.employees.send-credentials');

    // CRUD Lokasi
    Route::resource('locations', 'LocationController')->names([
        'index' => 'admin.locations.index',
        'create' => 'admin.locations.create',
        'store' => 'admin.locations.store',
        'edit' => 'admin.locations.edit',
        'update' => 'admin.locations.update',
        'destroy' => 'admin.locations.destroy',
    ]);

    // CRUD Shift
    Route::resource('shifts', 'ShiftController')->names([
        'index' => 'admin.shifts.index',
        'create' => 'admin.shifts.create',
        'store' => 'admin.shifts.store',
        'edit' => 'admin.shifts.edit',
        'update' => 'admin.shifts.update',
        'destroy' => 'admin.shifts.destroy',
    ]);

    // Jadwal Shift Security Mingguan
    Route::get('/security-schedules', 'SecurityShiftScheduleController@index')->name('admin.security-schedules.index');
    Route::post('/security-schedules/weekly-template', 'SecurityShiftScheduleController@storeWeeklyTemplate')->name('admin.security-schedules.weekly.store');

    // Laporan Absensi
    Route::get('/reports', 'AttendanceReportController@index')->name('admin.reports');
    Route::get('/reports/export', 'AttendanceReportController@export')->name('admin.reports.export');

    // Kelola Izin/Sakit
    Route::get('/leave-requests', 'LeaveRequestController@index')->name('admin.leave-requests.index');
    Route::post('/leave-requests/{id}/approve', 'LeaveRequestController@approve')->name('admin.leave-requests.approve');
    Route::post('/leave-requests/{id}/reject', 'LeaveRequestController@reject')->name('admin.leave-requests.reject');
});

// =====================================================================
// PEGAWAI Routes
// =====================================================================
Route::prefix('pegawai')->middleware(['auth', 'role:pegawai'])->namespace('Pegawai')->group(function () {
    Route::get('/dashboard', 'DashboardController@index')->name('pegawai.dashboard');

    // Absensi
    Route::get('/attendance', 'AttendanceController@index')->name('pegawai.attendance');
    Route::post('/attendance/clock-in', 'AttendanceController@clockIn')->name('pegawai.clockin');
    Route::post('/attendance/clock-out', 'AttendanceController@clockOut')->name('pegawai.clockout');

    // Riwayat
    Route::get('/history', 'AttendanceController@history')->name('pegawai.history');

    // Izin/Sakit
    Route::get('/leave-requests', 'LeaveRequestController@index')->name('pegawai.leave-requests.index');
    Route::get('/leave-requests/create', 'LeaveRequestController@create')->name('pegawai.leave-requests.create');
    Route::post('/leave-requests', 'LeaveRequestController@store')->name('pegawai.leave-requests.store');

    // Akun
    Route::get('/account/password', 'AccountController@editPassword')->name('pegawai.account.password.edit');
    Route::put('/account/password', 'AccountController@updatePassword')->name('pegawai.account.password.update');
    Route::post('/account/profile-photo', 'AccountController@updateProfilePhoto')->name('pegawai.account.profile-photo.update');
});

// =====================================================================
// MONITORING Routes
// =====================================================================
Route::prefix('monitoring')->middleware(['auth', 'role:monitoring'])->namespace('Monitoring')->group(function () {
    Route::get('/dashboard', 'DashboardController@index')->name('monitoring.dashboard');
    Route::get('/reports', 'ReportController@index')->name('monitoring.reports');
    Route::get('/reports/{userId}', 'ReportController@detail')->name('monitoring.detail');
    Route::get('/leave-requests', 'LeaveRequestController@index')->name('monitoring.leave-requests.index');
    Route::post('/leave-requests/{id}/approve', 'LeaveRequestController@approve')->name('monitoring.leave-requests.approve');
    Route::post('/leave-requests/{id}/reject', 'LeaveRequestController@reject')->name('monitoring.leave-requests.reject');
});
