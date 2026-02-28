@extends('layouts.app')

@section('title', 'Ubah Password - PPNPN')
@section('page-title', 'Ubah Password')
@section('page-subtitle', 'Perbarui password akun Anda')

@section('sidebar-menu')
    <li class="menu-label">Menu Utama</li>
    <li class="menu-item"><a href="{{ route('pegawai.dashboard') }}"><i class="fas fa-home"></i> Dashboard</a></li>
    <li class="menu-item"><a href="{{ route('pegawai.attendance') }}"><i class="fas fa-fingerprint"></i> Absensi Hari
            Ini</a></li>
    <li class="menu-label">Laporan</li>
    <li class="menu-item"><a href="{{ route('pegawai.history') }}"><i class="fas fa-history"></i> Riwayat Absensi</a></li>
    <li class="menu-item"><a href="{{ route('pegawai.leave-requests.index') }}"><i class="fas fa-envelope-open-text"></i>
            Pengajuan Izin</a></li>
    <li class="menu-label">Akun</li>
    <li class="menu-item"><a href="{{ route('pegawai.account.password.edit') }}" class="active"><i class="fas fa-key"></i>
            Ubah Password</a></li>
@endsection

@section('content')
    <div class="card" style="max-width: 640px;">
        <div class="card-header">
            <h5>Foto Profil</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('pegawai.account.profile-photo.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
                    <div style="width: 80px; height: 80px; border-radius: 50%; overflow: hidden; background: var(--body-bg); display: flex; align-items: center; justify-content: center; font-size: 26px; font-weight: 700; color: var(--text-secondary);">
                        @if(Auth::user()->profile_photo)
                            <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="Foto Profil"
                                style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        @endif
                    </div>
                    <div style="flex: 1;">
                        <label>Upload Foto Baru</label>
                        <input type="file" name="profile_photo" class="form-control-file"
                            accept="image/jpeg,image/jpg,image/png,image/webp" required>
                        <small style="color: var(--text-secondary);">Format: JPG, PNG, WEBP. Maksimal 2MB.</small>
                    </div>
                </div>

                <div style="text-align: right;">
                    <button type="submit" class="btn btn-outline-primary"><i class="fas fa-image"></i> Simpan Foto</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card" style="max-width: 640px;">
        <div class="card-header">
            <h5>Form Ubah Password</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('pegawai.account.password.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Password Saat Ini <span style="color: var(--danger)">*</span></label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Password Baru <span style="color: var(--danger)">*</span></label>
                    <input type="password" name="password" class="form-control" minlength="6" required>
                    <small style="color: var(--text-secondary);">Minimal 6 karakter dan harus berbeda dari password saat
                        ini.</small>
                </div>

                <div class="form-group">
                    <label>Konfirmasi Password Baru <span style="color: var(--danger)">*</span></label>
                    <input type="password" name="password_confirmation" class="form-control" minlength="6" required>
                </div>

                <div style="margin-top: 24px; text-align: right;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Password</button>
                </div>
            </form>
        </div>
    </div>
@endsection
