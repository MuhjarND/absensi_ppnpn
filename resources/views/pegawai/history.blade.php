@extends('layouts.app')

@section('title', 'Riwayat Absensi - PPNPN')
@section('page-title', 'Riwayat Absensi Anda')

@section('sidebar-menu')
    <li class="menu-label">Menu Utama</li>
    <li class="menu-item"><a href="{{ route('pegawai.dashboard') }}"><i class="fas fa-home"></i> Dashboard</a></li>
    <li class="menu-item"><a href="{{ route('pegawai.attendance') }}"><i class="fas fa-fingerprint"></i> Absensi Hari
            Ini</a></li>
    <li class="menu-label">Laporan</li>
    <li class="menu-item"><a href="{{ route('pegawai.history') }}" class="active"><i class="fas fa-history"></i> Riwayat
            Absensi</a></li>
    <li class="menu-item"><a href="{{ route('pegawai.leave-requests.index') }}"><i class="fas fa-envelope-open-text"></i>
            Pengajuan Izin</a></li>
@endsection

@section('content')
    <div class="card mb-4" style="max-width: 600px;">
        <div class="card-body">
            <form action="{{ route('pegawai.history') }}" method="GET"
                style="display: flex; gap: 10px; align-items: flex-end;">
                <div class="form-group mb-0" style="flex: 1;">
                    <label>Pilih Bulan</label>
                    <input type="month" name="month" class="form-control"
                        value="{{ request('month', now()->format('Y-m')) }}" onchange="this.form.submit()">
                </div>
                <a href="{{ route('pegawai.history') }}" class="btn btn-outline-secondary" title="Reset"><i
                        class="fas fa-sync"></i></a>
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
                                <td>{{ $attendance->date->translatedFormat('l, d M Y') }}</td>
                                <td>
                                    @if($attendance->clock_in)
                                        <div style="font-weight: 600;">{{ $attendance->clock_in->format('H:i') }}</div>
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
                                <td colspan="6" class="text-center py-4">
                                    <div class="empty-state">
                                        <i class="fas fa-calendar-times"></i>
                                        <h5>Riwayat Kosong</h5>
                                        <p>Tidak ada data absensi untuk bulan ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
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