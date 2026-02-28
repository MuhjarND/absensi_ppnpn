@extends('layouts.app')

@section('title', 'Laporan Monitoring - Absensi PPNPN')
@section('page-title', 'Laporan Rekapitulasi - Monitoring')

@section('sidebar-menu')
    <li class="menu-label">Menu Utama</li>
    <li class="menu-item"><a href="{{ route('monitoring.dashboard') }}"><i class="fas fa-desktop"></i> Dashboard</a></li>
    <li class="menu-item"><a href="{{ route('monitoring.reports') }}" class="active"><i class="fas fa-file-invoice"></i>
            Laporan Detail</a></li>
    <li class="menu-item"><a href="{{ route('monitoring.leave-requests.index') }}"><i class="fas fa-envelope-open-text"></i> Pengajuan Izin</a></li>
@endsection

@section('content')
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('monitoring.reports') }}" method="GET"
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
                <div class="form-group mb-0">
                    <label>Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control"
                        value="{{ request('start_date', $startDate->format('Y-m-d')) }}">
                </div>
                <div class="form-group mb-0">
                    <label>Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control"
                        value="{{ request('end_date', $endDate->format('Y-m-d')) }}">
                </div>
                <div class="form-group mb-0">
                    <label>Pencarian Pegawai</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                        placeholder="Cari nama atau NIP...">
                </div>
                <div class="form-group mb-0" style="display: flex;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;"><i class="fas fa-search"></i> Cari
                        Data</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-bottom-0 pb-0">
            <h5>Ringkasan Kinerja Pegawai (Berdasarkan Filter Tgl)</h5>
            <p style="color: var(--text-secondary); margin-bottom: 0;">Klik pada nama pegawai untuk melihat detail foto
                absensi.</p>
        </div>
        <div class="card-body p-0">
            <div style="overflow-x: auto;">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>NIP / Pegawai</th>
                            <th class="text-center">Total Hari Hadir</th>
                            <th class="text-center">Tepat Waktu</th>
                            <th class="text-center">Terlambat</th>
                            <th class="text-center">Izin / Sakit</th>
                            <th class="text-center">Alpha / Kosong</th>
                            <th width="100">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <div style="font-weight: 600;">{{ $user->name }}</div>
                                    <small style="color: var(--text-secondary);">{{ $user->nip ?? '-' }} |
                                        {{ $user->position ?? 'PPNPN' }}</small>
                                </td>
                                <td class="text-center" style="font-weight: bold; font-size: 16px;">
                                    {{ $user->total_hadir + $user->total_terlambat }}
                                </td>
                                <td class="text-center"><span class="badge badge-success"
                                        style="font-size: 14px; padding: 6px 10px;">{{ $user->total_hadir }}</span></td>
                                <td class="text-center"><span class="badge badge-warning"
                                        style="font-size: 14px; padding: 6px 10px;">{{ $user->total_terlambat }}</span></td>
                                <td class="text-center"><span class="badge badge-info"
                                        style="font-size: 14px; padding: 6px 10px;">{{ $user->total_izin }}</span></td>
                                <td class="text-center"><span class="badge badge-danger"
                                        style="font-size: 14px; padding: 6px 10px;">{{ $user->total_alpha }}</span></td>
                                <td>
                                    <a href="{{ route('monitoring.employee.detail', ['id' => $user->id, 'start_date' => request('start_date', $startDate->format('Y-m-d')), 'end_date' => request('end_date', $endDate->format('Y-m-d'))]) }}"
                                        class="btn btn-sm btn-outline-primary" style="width: 100%;">
                                        Detail <i class="fas fa-arrow-right"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="empty-state">
                                        <i class="fas fa-users-slash"></i>
                                        <h5>Tidak ada data pegawai</h5>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($users->hasPages())
            <div class="card-body border-top">
                {{ $users->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
@endsection
