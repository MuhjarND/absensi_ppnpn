@extends('layouts.app')

@section('title', 'Jadwal Security - Absensi PPNPN')
@section('page-title', 'Jadwal Security')
@section('page-subtitle', 'Template mingguan per hari (Senin-Minggu)')

@section('sidebar-menu')
    <li class="menu-label">Menu Utama</li>
    <li class="menu-item"><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i> Dashboard</a></li>
    <li class="menu-label">Master Data</li>
    <li class="menu-item"><a href="{{ route('admin.employees.index') }}"><i class="fas fa-users"></i> Data Pegawai</a></li>
    <li class="menu-item"><a href="{{ route('admin.locations.index') }}"><i class="fas fa-map-marker-alt"></i> Lokasi Kantor</a></li>
    <li class="menu-item"><a href="{{ route('admin.shifts.index') }}"><i class="fas fa-clock"></i> Data Shift</a></li>
    <li class="menu-item"><a href="{{ route('admin.security-schedules.index') }}" class="active"><i class="fas fa-calendar-alt"></i> Jadwal Security</a></li>
    <li class="menu-label">Laporan</li>
    <li class="menu-item"><a href="{{ route('admin.reports') }}"><i class="fas fa-file-alt"></i> Rekap Absensi</a></li>
    <li class="menu-item"><a href="{{ route('admin.leave-requests.index') }}"><i class="fas fa-envelope-open-text"></i> Pengajuan Izin</a></li>
@endsection

@section('content')
    <div class="card mb-4">
        <div class="card-header">
            <h5>Template Mingguan Security</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.security-schedules.weekly.store') }}" method="POST"
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px;">
                @csrf
                <div class="form-group mb-0">
                    <label>Petugas Security <span style="color: var(--danger)">*</span></label>
                    <select name="weekly_user_id" class="form-control" required>
                        <option value="">-- Pilih Security --</option>
                        @foreach($securityEmployees as $security)
                            <option value="{{ $security->id }}" {{ old('weekly_user_id', $weeklyPreset['user_id']) == $security->id ? 'selected' : '' }}>
                                {{ $security->name }} ({{ $security->nip ?? 'NIP -' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @foreach($dayOptions as $day)
                    <div class="form-group mb-0">
                        <label>
                            Shift {{ $day['label'] }}
                            @if($day['index'] !== 0)
                                <span style="color: var(--danger)">*</span>
                            @else
                                <small style="color: var(--text-secondary);">(Opsional)</small>
                            @endif
                        </label>
                        @php
                            $selectedValue = old($day['field'], $weeklyPreset[$day['field']]);
                        @endphp
                        <select name="{{ $day['field'] }}" class="form-control" {{ $day['index'] !== 0 ? 'required' : '' }}>
                            <option value="" {{ ($selectedValue === null || $selectedValue === '') ? 'selected' : '' }}>
                                {{ $day['index'] === 0 ? '-- Tidak Diatur --' : '-- Pilih Shift / Libur --' }}
                            </option>
                            <option value="off" {{ (string) $selectedValue === 'off' ? 'selected' : '' }}>Libur</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}" {{ (string) $selectedValue === (string) $shift->id ? 'selected' : '' }}>
                                    {{ $shift->name }} ({{ date('H:i', strtotime($shift->start_time)) }} -
                                    {{ date('H:i', strtotime($shift->end_time)) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
                <div style="grid-column: 1 / -1; text-align: right;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Template Mingguan
                    </button>
                </div>
            </form>
        </div>
        <div class="card-body border-top p-0">
            <div style="overflow-x: auto;">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Security</th>
                            @foreach($dayOptions as $day)
                                <th>{{ $day['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($weeklyTemplates as $template)
                            <tr>
                                <td>
                                    <div style="font-weight: 600;">{{ $template['user']->name }}</div>
                                    <small style="color: var(--text-secondary);">{{ $template['user']->nip ?? 'NIP -' }}</small>
                                </td>
                                @foreach($dayOptions as $day)
                                    <td>{{ $template['days'][$day['field']] ?? '-' }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($dayOptions) + 1 }}" class="text-center py-4">
                                    <div class="empty-state">
                                        <i class="fas fa-calendar-week"></i>
                                        <h5>Template mingguan belum diatur</h5>
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
