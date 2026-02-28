@extends('layouts.app')

@section('title', 'Edit Pegawai - Absensi PPNPN')
@section('page-title', 'Edit Data Pegawai')

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
            <h5>Edit Form ({{ $employee->name }})</h5>
            <a href="{{ route('admin.employees.index') }}" class="btn btn-sm btn-outline-primary"><i
                    class="fas fa-arrow-left"></i> Kembali</a>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.employees.update', $employee->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <!-- Kiri -->
                    <div>
                        <h6
                            style="color: var(--primary); font-weight: 600; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
                            Informasi Pribadi</h6>

                        <div class="form-group">
                            <label>Nama Lengkap <span style="color: var(--danger)">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $employee->name) }}"
                                required>
                        </div>

                        <div class="form-group">
                            <label>NIP PPNPN</label>
                            <input type="text" name="nip" class="form-control" value="{{ old('nip', $employee->nip) }}">
                        </div>

                        <div class="form-group">
                            <label>No. Telepon/WhatsApp</label>
                            <input type="text" name="phone" class="form-control"
                                value="{{ old('phone', $employee->phone) }}">
                        </div>

                        <div class="form-group">
                            <label>Jabatan/Posisi</label>
                            <input type="text" name="position" class="form-control"
                                value="{{ old('position', $employee->position) }}">
                        </div>

                        <div class="form-group">
                            <label>Status Akun</label>
                            <select name="is_active" class="form-control">
                                <option value="1" {{ old('is_active', $employee->is_active) ? 'selected' : '' }}>Aktif
                                </option>
                                <option value="0" {{ !old('is_active', $employee->is_active) ? 'selected' : '' }}>Non-Aktif
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Kanan -->
                    <div>
                        <h6
                            style="color: var(--primary); font-weight: 600; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
                            Akses Aplikasi</h6>

                        <div class="form-group">
                            <label>Email <span style="color: var(--danger)">*</span></label>
                            <input type="email" name="email" class="form-control"
                                value="{{ old('email', $employee->email) }}" required>
                        </div>

                        <div class="form-group">
                            <label>Password (Kosongkan jika tidak diubah)</label>
                            <input type="password" name="password" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Role Akses <span style="color: var(--danger)">*</span></label>
                            <select name="role_id" class="form-control" required>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id', $employee->role_id) == $role->id ? 'selected' : '' }}>{{ $role->display_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group"
                            style="background: var(--body-bg); padding: 15px; border-radius: var(--radius-sm);">
                            <div class="form-check" style="margin-bottom: 15px;">
                                <input type="checkbox" name="is_security" id="is_security" value="1" {{ old('is_security', $employee->is_security) ? 'checked' : '' }} onchange="toggleShift()">
                                <label for="is_security" style="margin: 0; font-weight: bold; cursor: pointer;">Pekerja
                                    Shift (Misal: Security)</label>
                            </div>

                            <div id="shift_container"
                                style="display: {{ old('is_security', $employee->is_security) ? 'block' : 'none' }};">
                                <label>Pilih Shift</label>
                                <select name="shift_id" class="form-control">
                                    <option value="">-- Plilih Shift --</option>
                                    @foreach($shifts as $shift)
                                        <option value="{{ $shift->id }}" {{ old('shift_id', $employee->shift_id) == $shift->id ? 'selected' : '' }}>
                                            {{ $shift->name }} ({{ date('H:i', strtotime($shift->start_time)) }} -
                                            {{ date('H:i', strtotime($shift->end_time)) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 30px; text-align: right;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Perbarui Data</button>
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