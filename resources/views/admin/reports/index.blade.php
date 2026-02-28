@extends('layouts.app')

@section('title', 'Rekap Absensi - Absensi PPNPN')
@section('page-title', 'Laporan & Rekap Absensi')

@section('sidebar-menu')
    <li class="menu-label">Menu Utama</li>
    <li class="menu-item"><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i> Dashboard</a></li>
    <li class="menu-label">Master Data</li>
    <li class="menu-item"><a href="{{ route('admin.employees.index') }}"><i class="fas fa-users"></i> Data Pegawai</a></li>
    <li class="menu-item"><a href="{{ route('admin.locations.index') }}"><i class="fas fa-map-marker-alt"></i> Lokasi
            Kantor</a></li>
    <li class="menu-item"><a href="{{ route('admin.shifts.index') }}"><i class="fas fa-clock"></i> Data Shift</a></li>
    <li class="menu-item"><a href="{{ route('admin.security-schedules.index') }}"><i class="fas fa-calendar-alt"></i> Jadwal Security</a></li>
    <li class="menu-label">Laporan</li>
    <li class="menu-item"><a href="{{ route('admin.reports') }}" class="active"><i class="fas fa-file-alt"></i> Rekap
            Absensi</a></li>
    <li class="menu-item"><a href="{{ route('admin.leave-requests.index') }}"><i class="fas fa-envelope-open-text"></i>
            Pengajuan Izin</a></li>
@endsection

@section('content')
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.reports') }}" method="GET"
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
                    <label>Pegawai</label>
                    <select name="user_id" class="form-control">
                        <option value="">Semua Pegawai</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ request('user_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">Semua Status</option>
                        <option value="hadir" {{ request('status') == 'hadir' ? 'selected' : '' }}>Hadir Tepat Waktu</option>
                        <option value="terlambat" {{ request('status') == 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                        <option value="alpha" {{ request('status') == 'alpha' ? 'selected' : '' }}>Alpha / Tidak Hadir
                        </option>
                        <option value="izin" {{ request('status') == 'izin' ? 'selected' : '' }}>Izin</option>
                        <option value="sakit" {{ request('status') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                    </select>
                </div>
                <div class="form-group mb-0" style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;"><i class="fas fa-filter"></i>
                        Filter</button>
                    <a href="{{ route('admin.reports.export', request()->all()) }}" class="btn btn-success"
                        title="Export CSV" style="flex: 1;"><i class="fas fa-file-excel"></i> Export</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div style="overflow-x: auto;">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Pegawai</th>
                            <th>Jam Masuk</th>
                            <th>Jam Pulang</th>
                            <th>Shift</th>
                            <th>Status</th>
                            <th width="80">Foto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $attendance)
                            <tr>
                                <td>{{ $attendance->date->format('d M Y') }}</td>
                                <td>
                                    <div style="font-weight: 600;">{{ $attendance->user->name }}</div>
                                    <small style="color: var(--text-secondary);">NIP:
                                        {{ $attendance->user->nip ?? '-' }}</small>
                                </td>
                                <td>
                                    @if($attendance->clock_in)
                                        <div style="font-weight: 600;">{{ $attendance->clock_in->format('H:i') }}</div>
                                        @if($attendance->clock_in_location_id)
                                            <small style="color: var(--text-secondary);"><i class="fas fa-map-marker-alt"></i>
                                                valid</small>
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
                                    {{ $attendance->shift ? $attendance->shift->name : 'Reguler' }}
                                </td>
                                <td>
                                    <span
                                        class="badge badge-{{ $attendance->status_badge }}">{{ ucfirst($attendance->status) }}</span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary"
                                        onclick="viewPhoto('{{ $attendance->clock_in_photo }}', '{{ $attendance->clock_out_photo }}')"
                                        title="Lihat Foto">
                                        <i class="fas fa-camera"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="empty-state">
                                        <i class="fas fa-file-slash"></i>
                                        <h5>Tidak ada data absensi</h5>
                                        <p>Terapkan filter lain atau pastikan ada pegawai yang berabsen.</p>
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

    <!-- Modal Foto -->
    <div id="photoModal"
        style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: white; padding: 24px; border-radius: var(--radius); max-width: 600px; width: 90%;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <h5 style="margin: 0;">Foto Absensi</h5>
                <button onclick="document.getElementById('photoModal').style.display='none'"
                    style="background: none; border: none; cursor: pointer; font-size: 20px;"><i
                        class="fas fa-times"></i></button>
            </div>
            <div style="display: flex; gap: 20px;">
                <div style="flex: 1; text-align: center;">
                    <div style="font-weight: bold; margin-bottom: 10px;">Absen Masuk</div>
                    <img id="photoIn" src="" alt="Foto Masuk"
                        style="width: 100%; border-radius: 8px; background: #eee; min-height: 200px; object-fit: cover;">
                </div>
                <div style="flex: 1; text-align: center;">
                    <div style="font-weight: bold; margin-bottom: 10px;">Absen Pulang</div>
                    <img id="photoOut" src="" alt="Foto Pulang"
                        style="width: 100%; border-radius: 8px; background: #eee; min-height: 200px; object-fit: cover;">
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
