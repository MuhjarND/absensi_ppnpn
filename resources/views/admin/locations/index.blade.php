@extends('layouts.app')

@section('title', 'Data Lokasi - Absensi PPNPN')
@section('page-title', 'Data Lokasi Kantor')
@section('page-subtitle', 'Kelola titik geofencing untuk absensi')

@section('sidebar-menu')
    <li class="menu-label">Menu Utama</li>
    <li class="menu-item"><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i> Dashboard</a></li>
    <li class="menu-label">Master Data</li>
    <li class="menu-item"><a href="{{ route('admin.employees.index') }}"><i class="fas fa-users"></i> Data Pegawai</a></li>
    <li class="menu-item"><a href="{{ route('admin.locations.index') }}" class="active"><i
                class="fas fa-map-marker-alt"></i> Lokasi Kantor</a></li>
    <li class="menu-item"><a href="{{ route('admin.shifts.index') }}"><i class="fas fa-clock"></i> Data Shift</a></li>
    <li class="menu-item"><a href="{{ route('admin.security-schedules.index') }}"><i class="fas fa-calendar-alt"></i> Jadwal Security</a></li>
    <li class="menu-label">Laporan</li>
    <li class="menu-item"><a href="{{ route('admin.reports') }}"><i class="fas fa-file-alt"></i> Rekap Absensi</a></li>
    <li class="menu-item"><a href="{{ route('admin.leave-requests.index') }}"><i class="fas fa-envelope-open-text"></i>
            Pengajuan Izin</a></li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5>Daftar Lokasi Absensi</h5>
            <a href="{{ route('admin.locations.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah
                Lokasi</a>
        </div>
        <div class="card-body p-0">
            <div style="overflow-x: auto;">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Nama Lokasi</th>
                            <th>Koordinat (Lat, Lng)</th>
                            <th>Radius (Meter)</th>
                            <th>Status</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($locations as $location)
                            <tr>
                                <td>
                                    <div style="font-weight: 600; color: var(--text-primary);">{{ $location->name }}</div>
                                    <small style="color: var(--text-secondary);"><i
                                            class="fas fa-map-marker-alt text-danger"></i>
                                        {{ Str::limit($location->address, 50) }}</small>
                                </td>
                                <td>
                                    <code
                                        style="background: var(--body-bg); padding: 4px 8px; border-radius: 4px;">{{ $location->latitude }}, {{ $location->longitude }}</code>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $location->radius }} m</span>
                                </td>
                                <td>
                                    @if($location->is_active)
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-danger">Non-Aktif</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.locations.edit', $location->id) }}"
                                        class="btn btn-sm btn-outline-primary" title="Edit Data">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($location->is_active)
                                        <form action="{{ route('admin.locations.destroy', $location->id) }}" method="POST"
                                            style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Non-Aktifkan"
                                                onclick="return confirm('Apakah Anda yakin ingin menonaktifkan lokasi ini?')">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="empty-state">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <h5>Tidak ada data lokasi</h5>
                                        <p>Silakan tambah lokasi kantor terlebih dahulu.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($locations->hasPages())
            <div class="card-body border-top">
                {{ $locations->links() }}
            </div>
        @endif
    </div>
@endsection
