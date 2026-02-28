@extends('layouts.app')

@section('title', 'Monitoring Dashboard - Absensi PPNPN')
@section('page-title', 'Dashboard Monitoring')
@section('page-subtitle', 'Pantauan kehadiran hari ini (' . date('d M Y') . ')')

@section('sidebar-menu')
    <li class="menu-label">Menu Utama</li>
    <li class="menu-item"><a href="{{ route('monitoring.dashboard') }}" class="active"><i class="fas fa-desktop"></i> Dashboard</a></li>
    <li class="menu-item"><a href="{{ route('monitoring.reports') }}"><i class="fas fa-file-invoice"></i> Laporan Detail</a></li>
@endsection

@section('content')
<div class="stats-grid">
    <div class="stat-card primary">
        <div class="stat-icon primary">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <h3>{{ $totalPegawai }}</h3>
            <p>Total Pegawai Aktif</p>
        </div>
    </div>
    
    <div class="stat-card success">
        <div class="stat-icon success">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <h3>{{ $hadirHariIni }}</h3>
            <p>Hadir Hari Ini</p>
        </div>
    </div>
    
    <div class="stat-card warning">
        <div class="stat-icon warning">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <h3>{{ $terlambatHariIni }}</h3>
            <p>Terlambat Hari Ini</p>
        </div>
    </div>
    
    <div class="stat-card info">
        <div class="stat-icon info">
            <i class="fas fa-envelope-open"></i>
        </div>
        <div class="stat-content">
            <h3>{{ $izinAtauSakit }}</h3>
            <p>Izin / Sakit Disetujui</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header border-bottom-0">
        <h5>Pantauan Absensi Real-time (Hari Ini)</h5>
    </div>
    <div class="card-body p-0">
        <div style="overflow-x: auto;">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Pegawai</th>
                        <th>Jabatan & Shift</th>
                        <th>Absen Masuk</th>
                        <th>Absen Pulang</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($todayAttendances as $attendance)
                        <tr>
                            <td>
                                <div style="font-weight: 600;">{{ $attendance->user->name }}</div>
                                <small style="color: var(--text-secondary);">NIP: {{ $attendance->user->nip ?? '-' }}</small>
                            </td>
                            <td>
                                <div>{{ $attendance->user->position ?? '-' }}</div>
                                <small class="badge badge-light" style="font-size: 11px;">{{ $attendance->shift ? $attendance->shift->name : 'Reguler' }}</small>
                            </td>
                            <td>
                                @if($attendance->clock_in)
                                    <div style="font-weight: 600; font-size: 16px;">{{ $attendance->clock_in->format('H:i') }}</div>
                                    <small style="color: var(--text-secondary);"><i class="fas fa-map-marker-alt text-danger"></i> {{ $attendance->clockInLocation->name ?? 'Lokasi Valid' }}</small>
                                @else
                                    <span style="color: var(--text-secondary);">-</span>
                                @endif
                            </td>
                            <td>
                                @if($attendance->clock_out)
                                    <div style="font-weight: 600; font-size: 16px;">{{ $attendance->clock_out->format('H:i') }}</div>
                                    @if($attendance->clockOutLocation)
                                    <small style="color: var(--text-secondary);"><i class="fas fa-map-marker-alt text-danger"></i> {{ $attendance->clockOutLocation->name }}</small>
                                    @endif
                                @else
                                    <span style="color: var(--text-secondary);"><i class="fas fa-spinner fa-spin text-warning"></i> Belum Pulang</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $attendance->status_badge }}">{{ ucfirst($attendance->status) }}</span>
                            </td>
                        </tr>
                    @endforeach
                    
                    @if($todayAttendances->count() == 0)
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="empty-state">
                                    <i class="fas fa-eye-slash"></i>
                                    <h5>Belum ada aktivitas</h5>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
