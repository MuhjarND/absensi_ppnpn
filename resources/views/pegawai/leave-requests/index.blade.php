@extends('layouts.app')

@section('title', 'Pengajuan Izin - PPNPN')
@section('page-title', 'Pengajuan Izin / Sakit')

@section('sidebar-menu')
    <li class="menu-label">Menu Utama</li>
    <li class="menu-item"><a href="{{ route('pegawai.dashboard') }}"><i class="fas fa-home"></i> Dashboard</a></li>
    <li class="menu-item"><a href="{{ route('pegawai.attendance') }}"><i class="fas fa-fingerprint"></i> Absensi Hari
            Ini</a></li>
    <li class="menu-label">Laporan</li>
    <li class="menu-item"><a href="{{ route('pegawai.history') }}"><i class="fas fa-history"></i> Riwayat Absensi</a></li>
    <li class="menu-item"><a href="{{ route('pegawai.leave-requests.index') }}" class="active"><i
                class="fas fa-envelope-open-text"></i> Pengajuan Izin</a></li>
    <li class="menu-label">Akun</li>
    <li class="menu-item"><a href="{{ route('pegawai.account.password.edit') }}" class="{{ request()->routeIs('pegawai.account.password.*') ? 'active' : '' }}"><i class="fas fa-key"></i> Ubah Password</a></li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5>Riwayat Pengajuan</h5>
            <a href="{{ route('pegawai.leave-requests.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Buat
                Pengajuan Baru</a>
        </div>
        <div class="card-body p-0">
            <div style="overflow-x: auto;">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Tipe</th>
                            <th>Periode (Tgl Mulai - Selesai)</th>
                            <th>Alasan</th>
                            <th>Lampiran</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                            <tr>
                                <td>
                                    <div><span class="badge badge-info"
                                            style="font-size: 13px;">{{ strtoupper($req->type) }}</span></div>
                                    <small style="display: block; margin-top: 4px; color: var(--text-secondary);">Diajukan:
                                        {{ $req->created_at->format('d/m/y H:i') }}</small>
                                </td>
                                <td>
                                    <strong>{{ $req->start_date->format('d/m/Y') }}</strong> <span
                                        style="color: var(--text-secondary);">sampai</span>
                                    <strong>{{ $req->end_date->format('d/m/Y') }}</strong>
                                </td>
                                <td>{{ Str::limit($req->reason, 50) }}</td>
                                <td>
                                    @if($req->attachment)
                                        <a href="{{ asset('storage/' . $req->attachment) }}" target="_blank"
                                            class="btn btn-sm btn-outline-primary"><i class="fas fa-file-alt"></i> Lihat File</a>
                                    @else
                                        <span style="color: var(--text-secondary); font-size: 13px;">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $req->status_badge }}">{{ ucfirst($req->status) }}</span>
                                    @if($req->status == 'rejected' && $req->admin_notes)
                                        <div
                                            style="margin-top: 6px; font-size: 12px; color: var(--danger); background: rgba(239, 68, 68, 0.1); padding: 4px 8px; border-radius: 4px;">
                                            <strong>Catatan:</strong> {{ $req->admin_notes }}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="empty-state">
                                        <i class="fas fa-folder-open"></i>
                                        <h5>Belum ada pengajuan</h5>
                                        <p>Anda belum pernah membuat pengajuan izin atau sakit.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
