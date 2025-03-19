<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Dashboard Parkir</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .section {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Dashboard Parkir</h2>
        <p>Periode: {{ $periode }}</p>
    </div>

    <div class="section">
        <h3>Statistik Umum</h3>
        <table>
            <tr>
                <th>Kendaraan Aktif</th>
                <td>{{ $stats['total_kendaraan_aktif'] }}</td>
                <th>Total Masuk</th>
                <td>{{ $stats['total_masuk'] }}</td>
            </tr>
            <tr>
                <th>Total Keluar</th>
                <td>{{ $stats['total_keluar'] }}</td>
                <th>Rata-rata Durasi</th>
                <td>{{ $stats['rata_rata_durasi'] }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Statistik Kendaraan Hari Ini</h3>
        <table>
            <tr>
                <th>Jenis</th>
                <th>Jumlah</th>
            </tr>
            <tr>
                <td>Mobil</td>
                <td>{{ $stats['mobil_hari_ini'] ?? 0 }}</td>
            </tr>
            <tr>
                <td>Motor</td>
                <td>{{ $stats['motor_hari_ini'] ?? 0 }}</td>
            </tr>
            <tr>
                <td>Bus</td>
                <td>{{ $stats['bus_hari_ini'] ?? 0 }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Riwayat Parkir Terbaru</h3>
        <table>
            <thead>
                <tr>
                    <th>No Kartu</th>
                    <th>Plat Nomor</th>
                    <th>Jenis</th>
                    <th>Masuk</th>
                    <th>Keluar</th>
                    <th>Durasi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($parkirHistory as $parkir)
                <tr>
                    <td>{{ $parkir->nomor_kartu }}</td>
                    <td>{{ strtoupper($parkir->plat_nomor) }}</td>
                    <td>{{ $parkir->jenis_kendaraan }}</td>
                    <td>{{ \Carbon\Carbon::parse($parkir->waktu_masuk)->format('d/m/Y H:i') }}</td>
                    <td>{{ $parkir->waktu_keluar ? \Carbon\Carbon::parse($parkir->waktu_keluar)->format('d/m/Y H:i') : '-' }}</td>
                    <td>{{ $parkir->durasi }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>