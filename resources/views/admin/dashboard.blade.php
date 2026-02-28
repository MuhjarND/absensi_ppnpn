@extends('layouts.app')

@section('title', 'Admin Dashboard - Absensi PPNPN')
@section('page-title', 'Dashboard Admin')
@section('page-subtitle', 'Ringkasan kehadiran hari ini (' . date('d M Y') . ')')

@section('sidebar-menu')
    <li class="menu-label">Menu Utama</li>
    <li class="menu-item"><a href="{{ route('admin.dashboard') }}" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
    <li class="menu-label">Master Data</li>
    <li class="menu-item"><a href="{{ route('admin.employees.index') }}"><i class="fas fa-users"></i> Data Pegawai</a></li>
    <li class="menu-item"><a href="{{ route('admin.locations.index') }}"><i class="fas fa-map-marker-alt"></i> Lokasi Kantor</a></li>
    <li class="menu-item"><a href="{{ route('admin.shifts.index') }}"><i class="fas fa-clock"></i> Data Shift</a></li>
    <li class="menu-item"><a href="{{ route('admin.security-schedules.index') }}"><i class="fas fa-calendar-alt"></i> Jadwal Security</a></li>
    <li class="menu-label">Laporan</li>
    <li class="menu-item"><a href="{{ route('admin.reports') }}"><i class="fas fa-file-alt"></i> Rekap Absensi</a></li>
    <li class="menu-item"><a href="{{ route('admin.leave-requests.index') }}"><i class="fas fa-envelope-open-text"></i> Pengajuan Izin</a></li>
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
    
    <div class="stat-card danger">
        <div class="stat-icon danger">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="stat-content">
            <h3>{{ $belumAbsen }}</h3>
            <p>Belum/Tidak Absen</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5>Absensi Terakhir (Hari Ini)</h5>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('admin.security-schedules.index') }}" class="btn btn-sm btn-outline-secondary">Jadwal Security</a>
            <a href="{{ route('admin.reports') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
        </div>
    </div>
    <div class="card-body p-0">
        @if($recentAttendances->count() > 0)
            <div style="overflow-x: auto;">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Pegawai</th>
                            <th>Jam Masuk</th>
                            <th>Jam Pulang</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentAttendances as $attendance)
                            <tr>
                                <td>
                                    <div style="font-weight: 600;">{{ $attendance->user->name }}</div>
                                    <small style="color: var(--text-secondary);">NIP: {{ $attendance->user->nip ?? '-' }}</small>
                                </td>
                                <td>
                                    @if($attendance->clock_in)
                                        <div style="font-weight: 600;">{{ $attendance->clock_in->format('H:i') }}</div>
                                        @if($attendance->clock_in_location_id)
                                            <small style="color: var(--text-secondary);"><i class="fas fa-map-marker-alt"></i> valid</small>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($attendance->clock_out)
                                        <div style="font-weight: 600;">{{ $attendance->clock_out->format('H:i') }}</div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $attendance->status_badge }}">{{ ucfirst($attendance->status) }}</span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewPhoto('{{ $attendance->clock_in_photo }}', '{{ $attendance->clock_out_photo }}')" title="Lihat Foto">
                                        <i class="fas fa-camera"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-fingerprint"></i>
                <h5>Belum ada data absensi</h5>
                <p>Belum ada pegawai yang melakukan absensi hari ini.</p>
            </div>
        @endif
    </div>
</div>

<!-- Modal Foto -->
<div id="photoModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; padding: 24px; border-radius: var(--radius); max-width: 600px; width: 90%;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
            <h5 style="margin: 0;">Foto Absensi</h5>
            <button onclick="document.getElementById('photoModal').style.display='none'" style="background: none; border: none; cursor: pointer; font-size: 20px;"><i class="fas fa-times"></i></button>
        </div>
        <div style="display: flex; gap: 20px;">
            <div style="flex: 1; text-align: center;">
                <div style="font-weight: bold; margin-bottom: 10px;">Absen Masuk</div>
                <img id="photoIn" src="" alt="Foto Masuk" style="width: 100%; border-radius: 8px; background: #eee; min-height: 200px; object-fit: cover;">
            </div>
            <div style="flex: 1; text-align: center;">
                <div style="font-weight: bold; margin-bottom: 10px;">Absen Pulang</div>
                <img id="photoOut" src="" alt="Foto Pulang" style="width: 100%; border-radius: 8px; background: #eee; min-height: 200px; object-fit: cover;">
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function viewPhoto(photoIn, photoOut) {
        document.getElementById('photoIn').src = photoIn ? '/storage/' + photoIn : 'https://via.placeholder.com/300x400?text=Tidak+Ada+Foto';
        document.getElementById('photoOut').src = photoOut ? '/storage/' + photoOut : 'https://via.placeholder.com/300x400?text=Belum+Absen+Pulang';
        document.getElementById('photoModal').style.display = 'flex';
    }
</script>
@endsection
