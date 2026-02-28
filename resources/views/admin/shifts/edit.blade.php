@extends('layouts.app')

@section('title', 'Edit Shift - Absensi PPNPN')
@section('page-title', 'Edit Data Shift')

@section('sidebar-menu')
    <li class="menu-label">Menu Utama</li>
    <li class="menu-item"><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i> Dashboard</a></li>
    <li class="menu-label">Master Data</li>
    <li class="menu-item"><a href="{{ route('admin.employees.index') }}"><i class="fas fa-users"></i> Data Pegawai</a></li>
    <li class="menu-item"><a href="{{ route('admin.locations.index') }}"><i class="fas fa-map-marker-alt"></i> Lokasi
            Kantor</a></li>
    <li class="menu-item"><a href="{{ route('admin.shifts.index') }}" class="active"><i class="fas fa-clock"></i> Data
            Shift</a></li>
@endsection

@section('content')
    <div class="card" style="max-width: 600px;">
        <div class="card-header">
            <h5>Edit Form ({{ $shift->name }})</h5>
            <a href="{{ route('admin.shifts.index') }}" class="btn btn-sm btn-outline-primary"><i
                    class="fas fa-arrow-left"></i> Kembali</a>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.shifts.update', $shift->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Nama Shift <span style="color: var(--danger)">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $shift->name) }}" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Jam Masuk <span style="color: var(--danger)">*</span></label>
                        <input type="time" name="start_time" class="form-control"
                            value="{{ old('start_time', date('H:i', strtotime($shift->start_time))) }}" required>
                    </div>

                    <div class="form-group">
                        <label>Jam Pulang <span style="color: var(--danger)">*</span></label>
                        <input type="time" name="end_time" class="form-control"
                            value="{{ old('end_time', date('H:i', strtotime($shift->end_time))) }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-check p-3"
                        style="background: var(--body-bg); border-radius: var(--radius-sm); border: 1px solid var(--border);">
                        <input class="form-check-input" type="checkbox" name="is_overnight" id="is_overnight" value="1" {{ old('is_overnight', $shift->is_overnight) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_overnight">
                            <strong style="display: block;">Shift Lintas Malam</strong>
                            <span style="color: var(--text-secondary); font-size: 12px; font-weight: normal;">Centang opsi
                                ini jika jam pulang melewati jam 12 malam ke hari berikutnya (Misal: Shift malam jam 22:00 -
                                06:00).</span>
                        </label>
                    </div>
                </div>

                <div style="margin-top: 30px; text-align: right;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Perbarui Data</button>
                </div>
            </form>
        </div>
    </div>
@endsection