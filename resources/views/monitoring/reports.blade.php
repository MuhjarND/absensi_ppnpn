@extends('layouts.app')

@section('title', 'Laporan Monitoring - Absensi PTA Papua Barat')
@section('page-title', 'Laporan Rekapitulasi - Monitoring')

@section('styles')
    <style>
        .report-shell {
            display: grid;
            gap: 22px;
        }

        .report-toolbar {
            display: grid;
            grid-template-columns: minmax(0, 1.8fr) minmax(320px, 1fr);
            gap: 20px;
            align-items: stretch;
        }

        .report-intro {
            background:
                radial-gradient(circle at top left, rgba(14, 165, 233, 0.14), transparent 38%),
                linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
            border: 1px solid rgba(79, 70, 229, 0.12);
        }

        .report-intro h5 {
            margin: 0 0 8px;
            font-size: 20px;
            font-weight: 800;
        }

        .report-intro p {
            margin: 0;
            color: var(--text-secondary);
            line-height: 1.6;
            max-width: 640px;
        }

        .report-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            align-content: start;
        }

        .report-actions .btn {
            justify-content: center;
            min-height: 48px;
        }

        .report-filter-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            align-items: end;
        }

        .report-filter-grid .filter-wide {
            grid-column: span 2;
        }

        .report-table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .report-table-header h5 {
            margin: 0;
        }

        .report-table-header p {
            margin: 0;
            color: var(--text-secondary);
        }

        .employee-main {
            display: grid;
            gap: 4px;
        }

        .employee-main strong {
            font-size: 15px;
        }

        .employee-meta {
            color: var(--text-secondary);
            font-size: 13px;
        }

        .metric-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            border-radius: 999px;
            font-weight: 800;
            font-size: 16px;
        }

        .metric-pill.success {
            background: rgba(16, 185, 129, 0.12);
            color: #059669;
        }

        .metric-pill.warning {
            background: rgba(245, 158, 11, 0.14);
            color: #d97706;
        }

        .metric-pill.info {
            background: rgba(6, 182, 212, 0.12);
            color: #0891b2;
        }

        .metric-pill.danger {
            background: rgba(239, 68, 68, 0.12);
            color: #dc2626;
        }

        .report-total {
            font-size: 28px;
            font-weight: 800;
            color: var(--text-primary);
        }

        .report-empty-copy {
            max-width: 380px;
            margin: 8px auto 0;
        }

        @media (max-width: 1200px) {
            .report-toolbar {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 900px) {
            .report-filter-grid {
                grid-template-columns: 1fr 1fr;
            }

            .report-filter-grid .filter-wide {
                grid-column: span 2;
            }
        }

        @media (max-width: 640px) {
            .report-actions {
                grid-template-columns: 1fr;
            }

            .report-filter-grid {
                grid-template-columns: 1fr;
            }

            .report-filter-grid .filter-wide {
                grid-column: span 1;
            }
        }
    </style>
@endsection

@section('sidebar-menu')
    <li class="menu-label">Menu Utama</li>
    <li class="menu-item"><a href="{{ route('monitoring.dashboard') }}"><i class="fas fa-desktop"></i> Dashboard</a></li>
    <li class="menu-item"><a href="{{ route('monitoring.reports') }}" class="active"><i class="fas fa-file-invoice"></i>
            Laporan Detail</a></li>
    <li class="menu-item"><a href="{{ route('monitoring.leave-requests.index') }}"><i class="fas fa-envelope-open-text"></i> Pengajuan Izin</a></li>
@endsection

@section('content')
    @php
        $users = $users ?? ($employees ?? collect());
        $reportQuery = [
            'start_date' => request('start_date', $startDate->format('Y-m-d')),
            'end_date' => request('end_date', $endDate->format('Y-m-d')),
            'search' => request('search'),
        ];
    @endphp
    <div class="report-shell">
        <div class="report-toolbar">
            <div class="card report-intro" style="margin-bottom: 0;">
                <div class="card-body">
                    <h5>Laporan Monitoring</h5>
                    <p>
                        Gunakan filter tanggal untuk menyusun rekap absensi pegawai, lalu unduh atau cetak PDF resmi
                        lengkap dengan kop surat.
                    </p>
                </div>
            </div>
            <div class="card" style="margin-bottom: 0;">
                <div class="card-body">
                    <div class="report-actions">
                        <a href="{{ route('monitoring.reports.export-pdf', $reportQuery) }}"
                            class="btn btn-danger">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </a>
                        <a href="{{ route('monitoring.reports.export-pdf', array_merge($reportQuery, ['print' => 1])) }}"
                            class="btn btn-outline-secondary" target="_blank">
                            <i class="fas fa-print"></i> Cetak PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-0">
            <div class="card-body">
                <form action="{{ route('monitoring.reports') }}" method="GET" class="report-filter-grid">
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
                    <div class="form-group mb-0 filter-wide">
                        <label>Pencarian Pegawai</label>
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                            placeholder="Cari nama atau NIP...">
                    </div>
                    <div class="form-group mb-0">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                            <i class="fas fa-search"></i> Terapkan Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="report-table-header">
                    <h5>Ringkasan Kinerja Pegawai</h5>
                    <p>Klik tombol detail untuk melihat rekap selfie masuk dan pulang per pegawai.</p>
                </div>
            </div>
            <div class="card-body p-0">
                <div style="overflow-x: auto;">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>NIP / Pegawai</th>
                                <th class="text-center">Total Hari Hadir</th>
                                <th class="text-center">Tepat Waktu</th>
                                <th class="text-center">Terlambat</th>
                                <th class="text-center">Izin / Sakit</th>
                                <th class="text-center">Alpha / Kosong</th>
                                <th class="text-center">Jam / Hari</th>
                                <th class="text-center">Jam / Bulan</th>
                                <th width="148">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>
                                        <div class="employee-main">
                                            <strong>{{ $user->name }}</strong>
                                            <div class="employee-meta">{{ $user->nip ?? '-' }} | {{ $user->position ?? 'PPNPN' }}</div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="report-total">{{ $user->total_hadir + $user->total_terlambat }}</div>
                                    </td>
                                    <td class="text-center"><span class="metric-pill success">{{ $user->total_hadir }}</span></td>
                                    <td class="text-center"><span class="metric-pill warning">{{ $user->total_terlambat }}</span></td>
                                    <td class="text-center"><span class="metric-pill info">{{ $user->total_izin }}</span></td>
                                    <td class="text-center"><span class="metric-pill danger">{{ $user->total_alpha }}</span></td>
                                    <td class="text-center">
                                        <div style="font-weight: 700;">{{ $user->average_daily_work_duration }}</div>
                                        <small style="color: var(--text-secondary);">Rata-rata</small>
                                    </td>
                                    <td class="text-center">
                                        <div style="font-weight: 700;">{{ $user->total_work_duration }}</div>
                                        <small style="color: var(--text-secondary);">Akumulasi</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('monitoring.detail', ['userId' => $user->id, 'start_date' => request('start_date', $startDate->format('Y-m-d')), 'end_date' => request('end_date', $endDate->format('Y-m-d'))]) }}"
                                            class="btn btn-sm btn-outline-primary" style="width: 100%; justify-content: center;">
                                            Detail & Selfie <i class="fas fa-arrow-right"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <div class="empty-state">
                                            <i class="fas fa-users-slash"></i>
                                            <h5>Tidak ada data pegawai</h5>
                                            <p class="report-empty-copy">Ubah filter tanggal atau pencarian untuk melihat data rekap pegawai.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if(method_exists($users, 'hasPages') && $users->hasPages())
                <div class="card-body border-top">
                    {{ $users->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
