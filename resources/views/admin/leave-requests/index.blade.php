@extends('layouts.app')

@section('title', 'Daftar Pengajuan Izin - Absensi PPNPN')
@section('page-title', 'Pengajuan Izin / Sakit')

@section('sidebar-menu')
    <li class="menu-label">Menu Utama</li>
    <li class="menu-item"><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i> Dashboard</a></li>
    <li class="menu-label">Master Data</li>
    <li class="menu-item"><a href="{{ route('admin.employees.index') }}"><i class="fas fa-users"></i> Data Pegawai</a></li>
    <li class="menu-item"><a href="{{ route('admin.locations.index') }}"><i class="fas fa-map-marker-alt"></i> Lokasi
            Kantor</a></li>
    <li class="menu-item"><a href="{{ route('admin.shifts.index') }}"><i class="fas fa-clock"></i> Data Shift</a></li>
    <li class="menu-label">Laporan</li>
    <li class="menu-item"><a href="{{ route('admin.reports') }}"><i class="fas fa-file-alt"></i> Rekap Absensi</a></li>
    <li class="menu-item"><a href="{{ route('admin.leave-requests.index') }}" class="active"><i
                class="fas fa-envelope-open-text"></i> Pengajuan Izin</a></li>
@endsection

@section('content')
    <div class="card mb-4" style="max-width: 400px;">
        <div class="card-body">
            <form action="{{ route('admin.leave-requests.index') }}" method="GET" style="display: flex; gap: 10px;">
                <select name="status" class="form-control" onchange="this.form.submit()">
                    <option value="">Semua Status Pengajuan</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu Persetujuan
                    </option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div style="overflow-x: auto;">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal Pengajuan</th>
                            <th>Pegawai</th>
                            <th>Tipe / Periode</th>
                            <th>Alasan</th>
                            <th>Lampiran</th>
                            <th>Status</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaveRequests as $req)
                            <tr>
                                <td>{{ $req->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div style="font-weight: 600;">{{ $req->user->name }}</div>
                                </td>
                                <td>
                                    <div><span class="badge badge-info">{{ strtoupper($req->type) }}</span></div>
                                    <small style="color: var(--text-secondary); margin-top: 4px; display: block;">
                                        {{ $req->start_date->format('d/m/Y') }} - {{ $req->end_date->format('d/m/Y') }}
                                    </small>
                                </td>
                                <td>{{ Str::limit($req->reason, 50) }}</td>
                                <td>
                                    @if($req->attachment)
                                        <a href="{{ asset('storage/' . $req->attachment) }}" target="_blank"
                                            class="btn btn-sm btn-outline-primary"><i class="fas fa-file"></i> Lihat File</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $req->status_badge }}">{{ ucfirst($req->status) }}</span>
                                    @if($req->status != 'pending')
                                        <small style="display: block; margin-top: 4px; color: var(--text-secondary);">Oleh:
                                            {{ $req->approvedByUser->name ?? '-' }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($req->status == 'pending')
                                        <div style="display: flex; gap: 6px;">
                                            <form action="{{ route('admin.leave-requests.approve', $req->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" title="Setujui"
                                                    onclick="return confirm('Setujui pengajuan ini?')"><i
                                                        class="fas fa-check"></i></button>
                                            </form>
                                            <button class="btn btn-sm btn-danger" title="Tolak"
                                                onclick="showRejectModal({{ $req->id }})"><i class="fas fa-times"></i></button>
                                        </div>
                                    @else
                                        @if($req->admin_notes)
                                            <button class="btn btn-sm btn-outline-secondary"
                                                onclick="alert('Catatan Penolakan: \n{{ $req->admin_notes }}')"><i
                                                    class="fas fa-comment"></i> Catatan</button>
                                        @else
                                            -
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="empty-state">
                                        <i class="fas fa-envelope-open"></i>
                                        <h5>Tidak ada pengajuan</h5>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($leaveRequests->hasPages())
            <div class="card-body border-top">
                {{ $leaveRequests->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Tolak -->
    <div id="rejectModal"
        style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: white; padding: 24px; border-radius: var(--radius); width: 400px; max-width: 90%;">
            <h5 style="margin: 0 0 20px;">Tolak Pengajuan</h5>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="form-group">
                    <label>Alasan Penolakan</label>
                    <textarea name="admin_notes" class="form-control" rows="3" required
                        placeholder="Berikan alasan spesifik..."></textarea>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn btn-outline-secondary"
                        onclick="document.getElementById('rejectModal').style.display='none'">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak Pengajuan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function showRejectModal(id) {
            document.getElementById('rejectForm').action = "/admin/leave-requests/" + id + "/reject";
            document.getElementById('rejectModal').style.display = 'flex';
        }
    </script>
@endsection