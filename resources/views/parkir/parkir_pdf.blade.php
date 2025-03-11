<!DOCTYPE html>
<html>
<head>
    <title>Laporan Data Parkir masuk{{ \Carbon\Carbon::now()->format('F-Y') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h1>Laporan Data Parkir Golden Hill {{\Carbon\Carbon::now()->locale('id')->isoFormat('MMMM-Y')}}</h1>
    <table>
        <thead>
            <tr>
                <th>No Kartu</th>
                <th>Nomor Plat</th>
                <th>Jenis Kendaraan</th>
                <th>Waktu Masuk</th>
            </tr>
        </thead>
        <tbody>
            @foreach($parkirs as $parkir)
                <tr>
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