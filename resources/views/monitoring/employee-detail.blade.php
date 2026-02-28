@extends('layouts.app')

@section('title', 'Detail Pegawai - Absensi PTA Papua Barat')
@section('page-title', 'Detail Absensi Pegawai')
@section('page-subtitle', $employee->name . ' (' . ($employee->nip ?? 'NIP: -') . ')')

@section('sidebar-menu')
    <li class="menu-label">Menu Utama</li>
    <li class="menu-item"><a href="{{ route('monitoring.dashboard') }}"><i class="fas fa-desktop"></i> Dashboard</a></li>
    <li class="menu-item"><a href="{{ route('monitoring.reports') }}" class="active"><i class="fas fa-file-invoice"></i> Laporan Detail</a></li>
    <li class="menu-item"><a href="{{ route('monitoring.leave-requests.index') }}"><i class="fas fa-envelope-open-text"></i> Pengajuan Izin</a></li>
@endsection

@section('content')
<div class="row" style="display: grid; grid-template-columns: 300px 1fr; gap: 24px; align-items: start;">
    
    <!-- Profil Card -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-body text-center" style="display: flex; flex-direction: column; align-items: center;">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: bold; margin-bottom: 16px;">
                {{ strtoupper(substr($employee->name, 0, 1)) }}
            </div>
            <h5 style="margin: 0 0 4px; font-weight: 700;">{{ $employee->name }}</h5>
            <div style="color: var(--text-secondary); margin-bottom: 4px;">{{ $employee->nip ?? '-' }}</div>
            <div style="color: var(--text-secondary); margin-bottom: 16px;">{{ $employee->position ?? 'PPNPN' }}</div>
            
            <div style="width: 100%; border-top: 1px solid var(--border); padding-top: 15px; text-align: left; font-size: 14px;">
                <div style="margin-bottom: 8px;">
                    <i class="fas fa-envelope text-secondary" style="width: 25px;"></i> {{ $employee->email }}
                </div>
                <div style="margin-bottom: 8px;">
                    <i class="fas fa-phone text-secondary" style="width: 25px;"></i> {{ $employee->phone ?? '-' }}
                </div>
                <div style="margin-bottom: 8px;">
                    <i class="fas fa-clock text-secondary" style="width: 25px;"></i> {{ $employee->shift ? $employee->shift->name : 'Reguler' }}
                </div>
            </div>

            <a href="{{ route('monitoring.reports', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" class="btn btn-outline-secondary mt-3" style="width: 100%;">
                <i class="fas fa-arrow-left"></i> Kembali ke Rekap
            </a>
        </div>
    </div>

    <!-- Riwayat Absensi -->
    <div>
        <div class="card mb-4">
            <div class="card-header border-bottom-0 pb-0" style="display: flex; justify-content: space-between; align-items: center;">
                <h5>Riwayat Absensi</h5>
                <span class="badge badge-light" style="font-size: 14px;">Periode: {{ $startDate->format('d M') }} - {{ $endDate->format('d M Y') }}</span>
            </div>
            <div class="card-body p-0">
                <div style="overflow-x: auto;">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jam Masuk</th>
                                <th>Jam Pulang</th>
                                <th>Status</th>
                                <th width="80">Foto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $attendance)
                                <tr>
                                    <td>{{ $attendance->date->translatedFormat('l, d M Y') }}</td>
                                    <td>
                                        @if($attendance->clock_in)
                                            <div style="font-weight: 600;">{{ $attendance->clock_in->format('H:i') }}</div>
                                            @if($attendance->clock_in_location_id)
                                                <small style="color: var(--text-secondary); display: block;"><i class="fas fa-map-marker-alt"></i> {{ Str::limit($attendance->clockInLocation->name, 20) }}</small>
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
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="empty-state">
                                            <i class="fas fa-calendar-times"></i>
                                            <h5>Riwayat Kosong</h5>
                                            <p>Tidak ada data absensi pada periode ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($attendances->hasPages())
            <div class="card-body border-top">
                {{ $attendances->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Foto -->
<div id="photoModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; padding: 24px; border-radius: var(--radius); max-width: 600px; width: 90%;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
            <h5 style="margin: 0;">Foto Absensi - {{ $employee->name }}</h5>
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
    const storageBaseUrl = @json(asset('storage'));

    function viewPhoto(photoIn, photoOut) {
        document.getElementById('photoIn').src = photoIn ? (storageBaseUrl + '/' + photoIn) : 'https://via.placeholder.com/300x400?text=Tidak+Ada+Foto';
        document.getElementById('photoOut').src = photoOut ? (storageBaseUrl + '/' + photoOut) : 'https://via.placeholder.com/300x400?text=Belum+Absen+Pulang';
        document.getElementById('photoModal').style.display = 'flex';
    }
</script>
@endsection
