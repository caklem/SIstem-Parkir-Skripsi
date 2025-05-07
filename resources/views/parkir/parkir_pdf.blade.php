<!DOCTYPE html>
<html>
<head>
    <title>Laporan Parkir Masuk Hotel Golden Hill</title>
    <style>
        @page {
            margin: 0.5cm 1cm;
        }
        body {
            font-family: Arial, sans-serif;
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
        .table {
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
            background-color: #f4f4f4;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Laporan Parkir Masuk Hotel Golden Hill</div>
        <div class="subtitle">Periode: {{ \Carbon\Carbon::now()->locale('id')->isoFormat('MMMM Y') }}</div>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>No Kartu</th>
                <th>Plat Nomor</th>
                <th>Jenis Kendaraan</th>
                <th>Waktu Masuk</th>
            </tr>
        </thead>
        <tbody>
            @foreach($parkirs as $index => $parkir)
                <tr>
                    <td>{{ $index + 1}}</td>
                    <td>{{ $parkir->nomor_kartu }}</td>
                    <td>{{ $parkir->plat_nomor }}</td>
                    <td>{{ $parkir->jenis_kendaraan }}</td>
                    <td>{{ $parkir->waktu_masuk }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>