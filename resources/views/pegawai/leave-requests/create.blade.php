@extends('layouts.app')

@section('title', 'Buat Pengajuan - PPNPN')
@section('page-title', 'Form Pengajuan Baru')

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
    <div class="card" style="max-width: 600px;">
        <div class="card-header">
            <h5>Form Izin / Sakit</h5>
            <a href="{{ route('pegawai.leave-requests.index') }}" class="btn btn-sm btn-outline-primary"><i
                    class="fas fa-arrow-left"></i> Kembali</a>
        </div>
        <div class="card-body">
            <form action="{{ route('pegawai.leave-requests.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group" style="background: var(--body-bg); padding: 15px; border-radius: var(--radius-sm);">
                    <label>Tipe Pengajuan <span style="color: var(--danger)">*</span></label>
                    <div style="display: flex; gap: 20px; margin-top: 10px;">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="type" id="type_izin" value="izin" {{ old('type') == 'izin' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="type_izin" style="font-weight: 600; cursor: pointer;">
                                Izin
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="type" id="type_sakit" value="sakit" {{ old('type') == 'sakit' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="type_sakit" style="font-weight: 600; cursor: pointer;">
                                Sakit
                            </label>
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Tanggal Mulai <span style="color: var(--danger)">*</span></label>
                        <input type="date" name="start_date" id="start_date" class="form-control"
                            value="{{ old('start_date') }}" required onchange="syncDates()">
                    </div>
                    <div class="form-group">
                        <label>Tanggal Selesai <span style="color: var(--danger)">*</span></label>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ old('end_date') }}"
                            required min="{{ old('start_date') }}">
                        <small style="color: var(--text-secondary); display: block; margin-top: 4px;">Pilih tanggal yang
                            sama jika hanya 1 hari.</small>
                    </div>
                </div>

                <div class="form-group">
                    <label>Alasan Lengkap <span style="color: var(--danger)">*</span></label>
                    <textarea name="reason" class="form-control" rows="4" required
                        placeholder="Jelaskan alasan izin / sakit secara detail...">{{ old('reason') }}</textarea>
                </div>

                <div class="form-group">
                    <label>Surat Keterangan / Lampiran Foto <span style="color: var(--danger)">*</span></label>
                    <div
                        style="border: 1px dashed var(--border); padding: 20px; border-radius: var(--radius-sm); background: var(--body-bg);">
                        <input type="file" name="attachment" class="form-control-file"
                            accept="image/jpeg,image/png,image/jpg,application/pdf" required>
                        <small style="color: var(--text-secondary); display: block; margin-top: 8px;">*Wajib melampirkan
                            foto bukti atau surat dokter (Maks 2MB, format: JPG, PNG, PDF)</small>
                    </div>
                </div>

                <div style="margin-top: 30px; text-align: right;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Kirim
                        Pengajuan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function syncDates() {
            var start = document.getElementById('start_date').value;
            var end = document.getElementById('end_date').value;

            document.getElementById('end_date').min = start;

            if (!end || end < start) {
                document.getElementById('end_date').value = start;
            }
        }
    </script>
@endsection
