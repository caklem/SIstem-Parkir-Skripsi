<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Parkir Keluar</title>
    <style>
        @page {
            margin: 0.5cm 1cm;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .subtitle {
            font-size: 14px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">LAPORAN DATA PARKIR KELUAR</div>
        <div class="subtitle">Periode: {{ \Carbon\Carbon::now()->locale('id')->isoFormat('MMMM Y') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 15%">No Kartu</th>
                <th style="width: 20%">Plat Nomor</th>
                <th style="width: 20%">Jenis Kendaraan</th>
                <th style="width: 20%">Waktu Masuk</th>
                <th style="width: 20%">Waktu Keluar</th>
            </tr>
        </thead>
        <tbody>
            @foreach($parkirKeluar as $index => $parkir)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $parkir->nomor_kartu }}</td>
                <td>{{ $parkir->plat_nomor }}</td>
                <td>{{ $parkir->jenis_kendaraan }}</td>
                <td>{{ $parkir->waktu_masuk }}</td>
                <td>{{ $parkir->waktu_keluar }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>