<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Parkir Masuk</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background: #f4f4f4; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Data Parkir Masuk</h2>
        <p>Periode: {{ now()->format('F Y') }}</p>
    </div>
    
    <table>
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
            @foreach($parkirs as $key => $parkir)
                <tr>
                    <td>{{ $key + 1 }}</td>
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