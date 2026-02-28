@extends('layouts.app')

@section('title', 'Data Shift - Absensi PPNPN')
@section('page-title', 'Manajemen Jam Kerja Shift')

@section('sidebar-menu')
    <li class="menu-label">Menu Utama</li>
    <li class="menu-item"><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i> Dashboard</a></li>
    <li class="menu-label">Master Data</li>
    <li class="menu-item"><a href="{{ route('admin.employees.index') }}"><i class="fas fa-users"></i> Data Pegawai</a></li>
    <li class="menu-item"><a href="{{ route('admin.locations.index') }}"><i class="fas fa-map-marker-alt"></i> Lokasi
            Kantor</a></li>
    <li class="menu-item"><a href="{{ route('admin.shifts.index') }}" class="active"><i class="fas fa-clock"></i> Data
            Shift</a></li>
    <li class="menu-label">Laporan</li>
    <li class="menu-item"><a href="{{ route('admin.reports') }}"><i class="fas fa-file-alt"></i> Rekap Absensi</a></li>
    <li class="menu-item"><a href="{{ route('admin.leave-requests.index') }}"><i class="fas fa-envelope-open-text"></i>
            Pengajuan Izin</a></li>
@endsection

@section('content')
    <div class="card" style="max-width: 900px;">
        <div class="card-header">
            <h5>Daftar Shift Kerja</h5>
            <a href="{{ route('admin.shifts.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah
                Shift</a>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Nama Shift</th>
                        <th>Jam Masuk - Pulang</th>
                        <th>Tipe</th>
                        <th>Jml Pegawai</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shifts as $shift)
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: var(--text-primary);">{{ $shift->name }}</div>
                            </td>
                            <td>
                                <div
                                    style="font-family: monospace; font-size: 15px; background: var(--body-bg); padding: 4px 8px; border-radius: 4px; display: inline-block;">
                                    {{ date('H:i', strtotime($shift->start_time)) }} <i
                                        class="fas fa-arrow-right text-secondary" style="font-size: 10px; margin: 0 4px;"></i>
                                    {{ date('H:i', strtotime($shift->end_time)) }}
                                </div>
                            </td>
                            <td>
                                @if($shift->is_overnight)
                                    <span class="badge badge-warning"><i class="fas fa-moon"></i> Lintas Malam</span>
                                @else
                                    <span class="badge badge-success"><i class="fas fa-sun"></i> Reguler</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $shift->users_count }} Pegawai</span>
                            </td>
                            <td>
                                <a href="{{ route('admin.shifts.edit', $shift->id) }}" class="btn btn-sm btn-outline-primary"
                                    title="Edit Data">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($shift->users_count == 0 && strtolower($shift->name) !== 'reguler')
                                    <form action="{{ route('admin.shifts.destroy', $shift->id) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus shift ini?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="alert alert-info mt-4" style="max-width: 900px;">
        <i class="fas fa-info-circle fa-2x"></i>
        <div>
            <strong>Catatan Sistem Shift:</strong><br>
            Shift yang sudah di-assign ke pegawai secara aktif <strong>tidak dapat dihapus</strong>. Shift "Reguler" adalah
            shift default untuk pegawai non-security dan tidak boleh dihapus.
        </div>
    </div>
@endsection