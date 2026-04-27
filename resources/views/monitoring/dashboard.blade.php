@extends('layouts.app')

@section('title', 'Monitoring Dashboard - Absensi PTA Papua Barat')
@section('page-title', 'Dashboard Monitoring')
@section('page-subtitle', 'Pantauan kehadiran hari ini (' . date('d M Y') . ')')

@section('sidebar-menu')
    <li class="menu-label">Menu Utama</li>
    <li class="menu-item"><a href="{{ route('monitoring.dashboard') }}" class="active"><i class="fas fa-desktop"></i> Dashboard</a></li>
    <li class="menu-item"><a href="{{ route('monitoring.reports') }}"><i class="fas fa-file-invoice"></i> Laporan Detail</a></li>
    <li class="menu-item"><a href="{{ route('monitoring.leave-requests.index') }}"><i class="fas fa-envelope-open-text"></i> Pengajuan Izin</a></li>
@endsection

@section('content')
@php
    $izinAtauSakit = $izinAtauSakit ?? 0;
    $todayAttendances = $todayAttendances ?? ($recentAttendances ?? collect());
    $openAttendances = $openAttendances ?? collect();
@endphp

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

    <div class="stat-card danger">
        <div class="stat-icon danger">
            <i class="fas fa-sign-out-alt"></i>
        </div>
        <div class="stat-content">
            <h3>{{ $openAttendances->count() }}</h3>
            <p>Belum Absen Pulang</p>
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
    <div class="card-header">
        <h5>Pegawai Belum Absen Pulang</h5>
        <span class="badge badge-warning" style="font-size: 13px;">{{ $openAttendances->count() }} orang</span>
    </div>
    <div class="card-body p-0">
        <div style="overflow-x: auto;">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Pegawai</th>
                        <th>Jabatan & Shift</th>
                        <th>Absen Masuk</th>
                        <th>Lokasi Masuk</th>
                        <th>Lama Berjalan</th>
                        <th>Selfie</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($openAttendances as $attendance)
                        <tr>
                            <td>
                                <div style="font-weight: 600;">{{ $attendance->user->name ?? '-' }}</div>
                                <small style="color: var(--text-secondary);">NIP: {{ $attendance->user->nip ?? '-' }}</small>
                            </td>
                            <td>
                                <div>{{ $attendance->user->position ?? '-' }}</div>
                                <small class="badge badge-light" style="font-size: 11px;">{{ $attendance->shift ? $attendance->shift->name : 'Reguler' }}</small>
                            </td>
                            <td>
                                @if($attendance->clock_in)
                                    <div style="font-weight: 600; font-size: 16px;">{{ $attendance->clock_in->format('H:i') }}</div>
                                    <small style="color: var(--text-secondary); display: block;">
                                        {{ $attendance->clock_in->translatedFormat('d M Y') }}
                                    </small>
                                @else
                                    <span style="color: var(--text-secondary);">-</span>
                                @endif
                            </td>
                            <td>
                                <small style="color: var(--text-secondary);">
                                    <i class="fas fa-map-marker-alt text-danger"></i>
                                    {{ $attendance->clockInLocation->name ?? 'Lokasi Valid' }}
                                </small>
                            </td>
                            <td>
                                @if($attendance->clock_in)
                                    <span class="badge badge-warning" style="font-size: 12px;">
                                        {{ $attendance->clock_in->diffForHumans(null, true) }}
                                    </span>
                                    <small style="color: var(--warning); display: block; margin-top: 4px;">
                                        Menunggu absen pulang
                                    </small>
                                @else
                                    <span style="color: var(--text-secondary);">-</span>
                                @endif
                            </td>
                            <td>
                                @if($attendance->clock_in_photo || $attendance->clock_out_photo)
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick='viewSelfie(@json($attendance->clock_in_photo), @json($attendance->clock_out_photo), @json($attendance->user->name ?? "-"), @json($attendance->date->format("d/m/Y")))'>
                                        <i class="fas fa-camera"></i> Lihat
                                    </button>
                                @else
                                    <span style="color: var(--text-secondary);">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="empty-state">
                                    <i class="fas fa-check-circle"></i>
                                    <h5>Semua pegawai sudah absen pulang</h5>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
                        <th>Selfie</th>
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
                                    <small style="color: var(--text-secondary); display: block;">
                                        {{ $attendance->clock_in->translatedFormat('d M Y') }}
                                    </small>
                                    <small style="color: var(--text-secondary);"><i class="fas fa-map-marker-alt text-danger"></i> {{ $attendance->clockInLocation->name ?? 'Lokasi Valid' }}</small>
                                @else
                                    <span style="color: var(--text-secondary);">-</span>
                                @endif
                            </td>
                            <td>
                                @if($attendance->clock_out)
                                    <div style="font-weight: 600; font-size: 16px;">{{ $attendance->clock_out->format('H:i') }}</div>
                                    <small style="color: var(--text-secondary); display: block;">
                                        {{ $attendance->clock_out->translatedFormat('d M Y') }}
                                    </small>
                                    @if($attendance->clockOutLocation)
                                    <small style="color: var(--text-secondary);"><i class="fas fa-map-marker-alt text-danger"></i> {{ $attendance->clockOutLocation->name }}</small>
                                    @endif
                                @else
                                    <span style="color: var(--text-secondary);"><i class="fas fa-spinner fa-spin text-warning"></i> Belum Pulang</span>
                                    @if($attendance->clock_in)
                                        <small style="color: var(--warning); display: block;">
                                            Masuk sejak {{ $attendance->clock_in->translatedFormat('d M Y H:i') }}
                                        </small>
                                    @endif
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $attendance->status_badge }}">{{ ucfirst($attendance->status) }}</span>
                            </td>
                            <td>
                                @if($attendance->clock_in_photo || $attendance->clock_out_photo)
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick='viewSelfie(@json($attendance->clock_in_photo), @json($attendance->clock_out_photo), @json($attendance->user->name), @json($attendance->date->format("d/m/Y")))'>
                                        <i class="fas fa-camera"></i> Lihat
                                    </button>
                                @else
                                    <span style="color: var(--text-secondary);">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    
                    @if($todayAttendances->count() == 0)
                        <tr>
                            <td colspan="6" class="text-center py-4">
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

<div id="selfieModal"
    style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.75); z-index:9999; align-items:center; justify-content:center; padding:16px;">
    <div style="background:#fff; border-radius:var(--radius); width:min(920px, 96vw); max-height:90vh; overflow:auto; padding:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h5 id="selfieModalTitle" style="margin:0;">Selfie Absensi</h5>
            <button type="button" onclick="closeSelfieModal()"
                style="border:none; background:transparent; font-size:20px; cursor:pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap:16px;">
            <div>
                <div style="font-weight:600; margin-bottom:8px;">Foto Masuk</div>
                <img id="selfiePhotoIn" alt="Selfie Masuk"
                    style="width:100%; border-radius:8px; background:#f3f4f6; min-height:240px; object-fit:cover;">
            </div>
            <div>
                <div style="font-weight:600; margin-bottom:8px;">Foto Pulang</div>
                <img id="selfiePhotoOut" alt="Selfie Pulang"
                    style="width:100%; border-radius:8px; background:#f3f4f6; min-height:240px; object-fit:cover;">
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const storageBaseUrl = @json(asset('storage'));
    const emptyClockInPhoto = 'https://via.placeholder.com/300x400?text=Tidak+Ada+Foto+Masuk';
    const emptyClockOutPhoto = 'https://via.placeholder.com/300x400?text=Tidak+Ada+Foto+Pulang';

    function buildPhotoUrl(path, fallback) {
        return path ? (storageBaseUrl + '/' + path) : fallback;
    }

    function viewSelfie(photoIn, photoOut, employeeName, dateLabel) {
        const modal = document.getElementById('selfieModal');
        document.getElementById('selfieModalTitle').innerText = 'Selfie Absensi - ' + employeeName + ' (' + dateLabel + ')';
        document.getElementById('selfiePhotoIn').src = buildPhotoUrl(photoIn, emptyClockInPhoto);
        document.getElementById('selfiePhotoOut').src = buildPhotoUrl(photoOut, emptyClockOutPhoto);
        modal.style.display = 'flex';
    }

    function closeSelfieModal() {
        document.getElementById('selfieModal').style.display = 'none';
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeSelfieModal();
        }
    });
</script>
@endsection

