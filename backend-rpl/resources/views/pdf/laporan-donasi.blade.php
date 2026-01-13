<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Donasi</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }
        h2 {
            text-align: center;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
        }
        th {
            background: #eee;
        }
        .summary td {
            border: none;
            padding: 4px;
        }
    </style>
</head>
<body>

    @php
$namaBulan = [
  1 => 'Januari',
  2 => 'Februari',
  3 => 'Maret',
  4 => 'April',
  5 => 'Mei',
  6 => 'Juni',
  7 => 'Juli',
  8 => 'Agustus',
  9 => 'September',
  10 => 'Oktober',
  11 => 'November',
  12 => 'Desember',
];
@endphp

<h2>
  Laporan Donasi Bulan {{ $namaBulan[(int)$bulan] }} Tahun {{ $tahun }}
</h2>

<table class="summary">
    <tr><td>Total Donasi Masuk</td><td>: Rp {{ number_format($donasiMasuk, 0, ',', '.') }}</td></tr>
    <tr><td>Bantuan Tersalurkan</td><td>: Rp {{ number_format($tersalurkan, 0, ',', '.') }}</td></tr>
    <tr><td>Sisa Dana</td><td>: Rp {{ number_format($sisaDana, 0, ',', '.') }}</td></tr>
    <tr><td>Donasi Ditolak</td><td>: Rp {{ number_format($donasiDitolak, 0, ',', '.') }}</td></tr>
</table>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Donatur</th>
            <th>Tanggal</th>
            <th>Jenis</th>
            <th>Jumlah</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($donasis as $d)
        <tr>
            <td>{{ $d->id }}</td>
            <td>{{ $d->donatur->nama ?? '-' }}</td>
            <td>{{ $d->tanggal }}</td>
            <td>{{ $d->jenis_donasi }}</td>
            <td>Rp {{ number_format($d->jumlah, 0, ',', '.') }}</td>
            <td>{{ $d->status }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
