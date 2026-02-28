@extends('layouts.app')

@section('title', 'Data Pegawai - Absensi PPNPN')
@section('page-title', 'Data Pegawai')
@section('page-subtitle', 'Kelola data PPNPN dan role akses')

@section('sidebar-menu')
    <li class="menu-label">Menu Utama</li>
    <li class="menu-item"><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i> Dashboard</a></li>
    <li class="menu-label">Master Data</li>
    <li class="menu-item"><a href="{{ route('admin.employees.index') }}" class="active"><i class="fas fa-users"></i> Data
            Pegawai</a></li>
    <li class="menu-item"><a href="{{ route('admin.locations.index') }}"><i class="fas fa-map-marker-alt"></i> Lokasi
            Kantor</a></li>
    <li class="menu-item"><a href="{{ route('admin.shifts.index') }}"><i class="fas fa-clock"></i> Data Shift</a></li>
    <li class="menu-label">Laporan</li>
    <li class="menu-item"><a href="{{ route('admin.reports') }}"><i class="fas fa-file-alt"></i> Rekap Absensi</a></li>
    <li class="menu-item"><a href="{{ route('admin.leave-requests.index') }}"><i class="fas fa-envelope-open-text"></i>
            Pengajuan Izin</a></li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <form action="{{ route('admin.employees.index') }}" method="GET"
                style="display: flex; gap: 10px; width: 300px;">
                <input type="text" name="search" class="form-control" placeholder="Cari nama atau NIP..."
                    value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
            </form>
            <a href="{{ route('admin.employees.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah
                Pegawai</a>
        </div>
        <div class="card-body p-0">
            <div style="overflow-x: auto;">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>NIP / Nama</th>
                            <th>Kontak</th>
                            <th>Role / Shift</th>
                            <th>Status</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $employee)
                            <tr>
                                <td>
                                    <div style="font-weight: 600; color: var(--text-primary);">{{ $employee->name }}</div>
                                    <small style="color: var(--text-secondary);">{{ $employee->nip ?? 'Belum ada NIP' }}</small>
                                </td>
                                <td>
                                    <div><i class="fas fa-envelope text-secondary" style="width: 20px;"></i>
                                        {{ $employee->email }}</div>
                                    <div><i class="fas fa-phone text-secondary" style="width: 20px;"></i>
                                        {{ $employee->phone ?? '-' }}</div>
                                </td>
                                <td>
                                    <div><span class="badge badge-info">{{ $employee->role->display_name ?? 'User' }}</span>
                                    </div>
                                    @if($employee->is_security && $employee->shift)
                                        <div style="margin-top: 4px;"><small
                                                class="badge badge-secondary">{{ $employee->shift->name }}</small></div>
                                    @endif
                                </td>
                                <td>
                                    @if($employee->is_active)
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-danger">Non-Aktif</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.employees.edit', $employee->id) }}"
                                        class="btn btn-sm btn-outline-primary" title="Edit Data">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($employee->is_active)
                                        <form action="{{ route('admin.employees.destroy', $employee->id) }}" method="POST"
                                            style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Non-Aktifkan"
                                                onclick="return confirm('Apakah Anda yakin ingin menonaktifkan pegawai ini?')">
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
                                        <i class="fas fa-users-slash"></i>
                                        <h5>Tidak ada data ditemukan</h5>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($employees->hasPages())
            <div class="card-body border-top">
                {{ $employees->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
@endsection