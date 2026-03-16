<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Monitoring PDF</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
            margin: 0;
        }

        .header {
            margin-bottom: 16px;
        }

        .header img {
            width: 100%;
            max-height: 120px;
            object-fit: contain;
        }

        .title {
            text-align: center;
            margin-bottom: 14px;
        }

        .title h2 {
            margin: 0 0 4px;
            font-size: 18px;
        }

        .title p {
            margin: 0;
            font-size: 11px;
            color: #4b5563;
        }

        .meta {
            margin-bottom: 12px;
            padding: 10px 12px;
            background: #f3f4f6;
            border: 1px solid #d1d5db;
        }

        .meta p {
            margin: 3px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #9ca3af;
            padding: 7px 6px;
            vertical-align: top;
        }

        th {
            background: #e5e7eb;
            text-align: center;
            font-weight: bold;
        }

        td.number {
            text-align: center;
        }

        .footer {
            margin-top: 16px;
            font-size: 10px;
            color: #6b7280;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        @if($kopImageData)
            <img src="{{ $kopImageData }}" alt="Kop Surat">
        @endif
    </div>

    <div class="title">
        <h2>Laporan Rekapitulasi Monitoring</h2>
        <p>Aplikasi Absensi PTA Papua Barat</p>
    </div>

    <div class="meta">
        <p><strong>Periode:</strong> {{ $startDate->translatedFormat('d F Y') }} s/d {{ $endDate->translatedFormat('d F Y') }}</p>
        <p><strong>Pencarian:</strong> {{ $search !== '' ? $search : 'Semua Pegawai' }}</p>
        <p><strong>Tanggal Cetak:</strong> {{ $generatedAt->translatedFormat('d F Y H:i') }} WIT</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 28px;">No</th>
                <th>Nama / NIP</th>
                <th>Jabatan</th>
                <th style="width: 70px;">Hadir</th>
                <th style="width: 70px;">Tepat Waktu</th>
                <th style="width: 70px;">Terlambat</th>
                <th style="width: 70px;">Izin/Sakit</th>
                <th style="width: 70px;">Alpha</th>
                <th style="width: 82px;">Jam/Hari</th>
                <th style="width: 82px;">Jam/Bulan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $index => $user)
                <tr>
                    <td class="number">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $user->name }}</strong><br>
                        {{ $user->nip ?? '-' }}
                    </td>
                    <td>{{ $user->position ?? 'PPNPN' }}</td>
                    <td class="number">{{ $user->total_hadir + $user->total_terlambat }}</td>
                    <td class="number">{{ $user->total_hadir }}</td>
                    <td class="number">{{ $user->total_terlambat }}</td>
                    <td class="number">{{ $user->total_izin }}</td>
                    <td class="number">{{ $user->total_alpha }}</td>
                    <td class="number">{{ $user->average_daily_work_duration }}</td>
                    <td class="number">{{ $user->total_work_duration }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="number">Tidak ada data pegawai pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dokumen ini dibuat otomatis oleh sistem.
    </div>
</body>
</html>
