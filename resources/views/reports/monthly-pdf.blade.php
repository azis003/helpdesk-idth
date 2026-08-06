<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Tiket Bulanan — SIHATI</title>
    <style>
        @page { size: A3 landscape; margin: 8mm; }
        * { box-sizing: border-box; }
        body { color: #263a43; font-family: DejaVu Sans, sans-serif; font-size: 6.4px; margin: 0; }
        h1 { color: #17313c; font-size: 14px; margin: 0 0 3px; }
        p { color: #647b84; font-size: 7px; margin: 0 0 8px; }
        table { border-collapse: collapse; table-layout: fixed; width: 100%; }
        thead { display: table-header-group; }
        th { background: #1d5d72; color: #ffffff; font-size: 6.2px; font-weight: bold; padding: 5px 3px; text-align: left; vertical-align: middle; }
        td { border: 0.4px solid #d8e2e6; padding: 4px 3px; vertical-align: top; white-space: pre-wrap; word-wrap: break-word; }
        tbody tr:nth-child(even) { background: #f5f9fa; }
        .footer { color: #84969d; font-size: 6px; margin-top: 6px; }
    </style>
</head>
<body>
    <h1>Laporan Tiket Bulanan</h1>
    <p>Periode {{ $periodLabel }} · SIHATI · Zona waktu Asia/Jakarta</p>
    <table>
        <thead>
            <tr>
                @foreach ($columns as $label)
                    <th>{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($columns as $key => $label)
                        <td>{{ $row[$key] !== '' ? $row[$key] : '—' }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($columns) }}">Belum ada tiket pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>
    <p class="footer">Laporan dibuat dari histori transaksi aplikasi. Nilai kosong berarti data belum tercatat pada tiket.</p>
</body>
</html>
