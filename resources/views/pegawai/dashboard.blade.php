@extends('layouts.app')

@section('title', 'Dashboard - Pegawai')
@section('page-title', 'Dashboard Pegawai')
@section('page-subtitle', 'Selamat datang, ' . $user->name)

@section('sidebar-menu')
    <li class="menu-label">Menu Utama</li>
    <li class="menu-item"><a href="{{ route('pegawai.dashboard') }}" class="active"><i class="fas fa-home"></i>
            Dashboard</a></li>
    <li class="menu-item"><a href="{{ route('pegawai.attendance') }}"><i class="fas fa-fingerprint"></i> Absensi Hari
            Ini</a></li>
    <li class="menu-label">Laporan</li>
    <li class="menu-item"><a href="{{ route('pegawai.history') }}"><i class="fas fa-history"></i> Riwayat Absensi</a></li>
    <li class="menu-item"><a href="{{ route('pegawai.leave-requests.index') }}"><i class="fas fa-envelope-open-text"></i>
            Pengajuan Izin</a></li>
    <li class="menu-label">Akun</li>
    <li class="menu-item"><a href="{{ route('pegawai.account.password.edit') }}" class="{{ request()->routeIs('pegawai.account.password.*') ? 'active' : '' }}"><i class="fas fa-key"></i> Ubah Password</a></li>
@endsection

@section('content')
    <div class="row"
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; margin-bottom: 24px;">

        <!-- Profil Card -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-body text-center" style="display: flex; flex-direction: column; align-items: center;">
                <div
                    style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: bold; margin-bottom: 16px;">
                    @if($user->profile_photo)
                        <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="Foto Profil"
                            style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    @else
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    @endif
                </div>
                <h4 style="margin: 0 0 4px; font-weight: 700;">{{ $user->name }}</h4>
                <div style="color: var(--text-secondary); margin-bottom: 4px;">{{ $user->nip ?? 'NIP: -' }}</div>
                <div style="color: var(--text-secondary); margin-bottom: 16px;">{{ $user->position ?? 'PPNPN' }}</div>

                <div
                    style="background: var(--body-bg); padding: 10px 20px; border-radius: var(--radius-sm); font-size: 14px; width: 100%;">
                    <div style="margin-bottom: 8px;"><strong>Shift Aktif Anda:</strong></div>
                    @if($isOffToday)
                        <div><span class="badge badge-warning" style="font-size: 14px;">Libur</span></div>
                    @elseif($shift)
                        <div><span class="badge badge-primary" style="font-size: 14px;">{{ $shift->name }}</span></div>
                        <div style="margin-top: 4px; font-family: monospace;">{{ date('H:i', strtotime($shift->start_time)) }} -
                            {{ date('H:i', strtotime($shift->end_time)) }}</div>
                    @else
                        <div><span class="badge badge-secondary">Menunggu Assign Shift</span></div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Status Kehadiran Hari Ini -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header border-bottom-0 pb-0">
                <h5>Status Absensi Hari Ini</h5>
                <small style="color: var(--text-secondary);">{{ now()->translatedFormat('l, d F Y') }}</small>
            </div>
            <div class="card-body">
                @if(!$todayAttendance)
                    <div class="text-center" style="padding: 20px 0;">
                        <div
                            style="width: 64px; height: 64px; border-radius: 50%; background: rgba(239, 68, 68, 0.1); color: var(--danger); display: flex; align-items: center; justify-content: center; font-size: 24px; margin: 0 auto 16px;">
                            <i class="fas fa-times"></i>
                        </div>
                        <h5 style="margin: 0 0 8px;">Anda Belum Absen</h5>
                        <p style="color: var(--text-secondary); font-size: 14px; margin-bottom: 20px;">Silakan lakukan absen
                            masuk sekarang.</p>
                        <a href="{{ route('pegawai.attendance') }}" class="btn btn-primary btn-lg"
                            style="width: 100%; justify-content: center;">
                            <i class="fas fa-sign-in-alt"></i> Absen Sekarang
                        </a>
                    </div>
                @else
                    <div style="display: grid; grid-template-columns: 1fr; gap: 16px;">
                        <div
                            style="display: flex; align-items: center; justify-content: space-between; background: var(--body-bg); padding: 16px; border-radius: var(--radius-sm); border-left: 4px solid var(--success);">
                            <div>
                                <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 4px;">Jam Masuk</div>
                                <div style="font-weight: 700; font-size: 18px; color: var(--text-primary);">
                                    {{ $todayAttendance->clock_in ? $todayAttendance->clock_in->format('H:i:s') : '-' }}</div>
                            </div>
                            <div
                                style="background: rgba(16, 185, 129, 0.1); padding: 10px; border-radius: 50%; color: var(--success);">
                                <i class="fas fa-sign-in-alt"></i></div>
                        </div>

                        <div
                            style="display: flex; align-items: center; justify-content: space-between; background: var(--body-bg); padding: 16px; border-radius: var(--radius-sm); border-left: 4px solid {{ $todayAttendance->clock_out ? 'var(--primary)' : 'var(--warning)' }};">
                            <div>
                                <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 4px;">Jam Pulang</div>
                                @if($todayAttendance->clock_out)
                                    <div style="font-weight: 700; font-size: 18px; color: var(--text-primary);">
                                        {{ $todayAttendance->clock_out->format('H:i:s') }}</div>
                                @else
                                    <div style="font-weight: 700; font-size: 14px; color: var(--warning);">Belum Absen Pulang</div>
                                @endif
                            </div>
                            <div
                                style="background: {{ $todayAttendance->clock_out ? 'rgba(79, 70, 229, 0.1)' : 'rgba(245, 158, 11, 0.1)' }}; padding: 10px; border-radius: 50%; color: {{ $todayAttendance->clock_out ? 'var(--primary)' : 'var(--warning)' }};">
                                <i class="fas fa-sign-out-alt"></i></div>
                        </div>

                        <div
                            style="display: flex; align-items: center; justify-content: space-between; padding-top: 10px; border-top: 1px dashed var(--border);">
                            <span style="font-size: 14px; font-weight: 600;">Status Hari Ini</span>
                            <span class="badge badge-{{ $todayAttendance->status_badge }}"
                                style="font-size: 14px; padding: 6px 14px;">{{ ucfirst($todayAttendance->status) }}</span>
                        </div>

                        @if(!$todayAttendance->clock_out)
                            <a href="{{ route('pegawai.attendance') }}" class="btn btn-primary"
                                style="width: 100%; justify-content: center; margin-top: 10px;">
                                <i class="fas fa-sign-out-alt"></i> Absen Pulang Sekarang
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <h5 style="margin-bottom: 20px; font-weight: 700;">Rekap Bulan Ini ({{ now()->translatedFormat('F Y') }})</h5>
    <div class="stats-grid">
        <div class="stat-card success">
            <div class="stat-icon success"><i class="fas fa-calendar-check"></i></div>
            <div class="stat-content">
                <h3>{{ $totalHadir }}</h3>
                <p>Total Hadir</p>
            </div>
        </div>
        <div class="stat-card warning">
            <div class="stat-icon warning"><i class="fas fa-clock"></i></div>
            <div class="stat-content">
                <h3>{{ $totalTerlambat }}</h3>
                <p>Terlambat</p>
            </div>
        </div>
        <div class="stat-card danger">
            <div class="stat-icon danger"><i class="fas fa-times-circle"></i></div>
            <div class="stat-content">
                <h3>{{ $totalAlpha }}</h3>
                <p>Alpha / Tidak Hadir</p>
            </div>
        </div>
        <div class="stat-card info">
            <div class="stat-icon info"><i class="fas fa-envelope-open"></i></div>
            <div class="stat-content">
                <h3>{{ $totalIzin }}</h3>
                <p>Izin / Sakit</p>
            </div>
        </div>
    </div>
@endsection
