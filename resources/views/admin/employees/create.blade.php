@extends('layouts.app')

@section('title', 'Tambah Pegawai - Absensi PPNPN')
@section('page-title', 'Tambah Pegawai Baru')

@section('sidebar-menu')
    <li class="menu-label">Menu Utama</li>
    <li class="menu-item"><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i> Dashboard</a></li>
    <li class="menu-label">Master Data</li>
    <li class="menu-item"><a href="{{ route('admin.employees.index') }}" class="active"><i class="fas fa-users"></i> Data
            Pegawai</a></li>
    <li class="menu-item"><a href="{{ route('admin.locations.index') }}"><i class="fas fa-map-marker-alt"></i> Lokasi
            Kantor</a></li>
    <li class="menu-item"><a href="{{ route('admin.shifts.index') }}"><i class="fas fa-clock"></i> Data Shift</a></li>
@endsection

@section('content')
    <div class="card" style="max-width: 800px;">
        <div class="card-header">
            <h5>Form Data Pegawai</h5>
            <a href="{{ route('admin.employees.index') }}" class="btn btn-sm btn-outline-primary"><i
                    class="fas fa-arrow-left"></i> Kembali</a>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.employees.store') }}" method="POST">
                @csrf

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <!-- Kiri -->
                    <div>
                        <h6
                            style="color: var(--primary); font-weight: 600; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
                            Informasi Pribadi</h6>

                        <div class="form-group">
                            <label>Nama Lengkap <span style="color: var(--danger)">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>

                        <div class="form-group">
                            <label>NIP PPNPN</label>
                            <input type="text" name="nip" class="form-control" value="{{ old('nip') }}">
                        </div>

                        <div class="form-group">
                            <label>No. Telepon/WhatsApp</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                        </div>

                        <div class="form-group">
                            <label>Jabatan/Posisi</label>
                            <input type="text" name="position" class="form-control" value="{{ old('position') }}">
                        </div>
                    </div>

                    <!-- Kanan -->
                    <div>
                        <h6
                            style="color: var(--primary); font-weight: 600; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
                            Akses Aplikasi</h6>

                        <div class="form-group">
                            <label>Email <span style="color: var(--danger)">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        </div>

                        <div class="form-group">
                            <label>Password <span style="color: var(--danger)">*</span></label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                        </div>

                        <div class="form-group">
                            <label>Role Akses <span style="color: var(--danger)">*</span></label>
                            <select name="role_id" class="form-control" required>
                                <option value="">-- Pilih Role --</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                        {{ $role->display_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group"
                            style="background: var(--body-bg); padding: 15px; border-radius: var(--radius-sm);">
                            <div class="form-check" style="margin-bottom: 15px;">
                                <input type="checkbox" name="is_security" id="is_security" value="1" {{ old('is_security') ? 'checked' : '' }} onchange="toggleShift()">
                                <label for="is_security" style="margin: 0; font-weight: bold; cursor: pointer;">Pekerja
                                    Shift (Misal: Security)</label>
                            </div>

                            <div id="shift_container" style="display: {{ old('is_security') ? 'block' : 'none' }};">
                                <label>Pilih Shift</label>
                                <select name="shift_id" class="form-control">
                                    <option value="">-- Plilih Shift --</option>
                                    @foreach($shifts as $shift)
                                        <option value="{{ $shift->id }}" {{ old('shift_id') == $shift->id ? 'selected' : '' }}>
                                            {{ $shift->name }} ({{ date('H:i', strtotime($shift->start_time)) }} -
                                            {{ date('H:i', strtotime($shift->end_time)) }})
                                        </option>
                                    @endforeach
                                </select>
                                <small style="color: var(--text-secondary); display: block; margin-top: 5px;">*Jika tidak
                                    dicentang, pegawai masuk dengan shift Reguler default.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 30px; text-align: right;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Pegawai</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function toggleShift() {
            var isChecked = document.getElementById('is_security').checked;
            document.getElementById('shift_container').style.display = isChecked ? 'block' : 'none';
        }
    </script>
@endsection