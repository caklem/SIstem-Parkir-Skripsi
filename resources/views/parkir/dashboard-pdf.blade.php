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
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Dashboard Parkir Golden Hill</h2>
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
        <h3>Statistik Kendaraan {{ $periode }}</h3>
        <table>
            <tr>
                <th>Jenis</th>
                <th>Jumlah</th>
                <th>Persentase</th>
            </tr>
            <tr>
                <td>Mobil</td>
                <td>{{ $stats['distribusi_kendaraan']['mobil'] }}</td>
                <td>{{ $stats['total_kendaraan_periode'] > 0 ? round(($stats['distribusi_kendaraan']['mobil'] / $stats['total_kendaraan_periode']) * 100, 1) . '%' : '0%' }}</td>
            </tr>
            <tr>
                <td>Motor</td>
                <td>{{ $stats['distribusi_kendaraan']['motor'] }}</td>
                <td>{{ $stats['total_kendaraan_periode'] > 0 ? round(($stats['distribusi_kendaraan']['motor'] / $stats['total_kendaraan_periode']) * 100, 1) . '%' : '0%' }}</td>
            </tr>
            <tr>
                <td>Bus</td>
                <td>{{ $stats['distribusi_kendaraan']['bus'] }}</td>
                <td>{{ $stats['total_kendaraan_periode'] > 0 ? round(($stats['distribusi_kendaraan']['bus'] / $stats['total_kendaraan_periode']) * 100, 1) . '%' : '0%' }}</td>
            </tr>
            <tr style="font-weight: bold; background-color: #f9f9f9;">
                <td>Total</td>
                <td>{{ $stats['total_kendaraan_periode'] }}</td>
                <td>100%</td>
            </tr>
        </table>
    </div>

    @if($filterType == 'day')
    <!-- Statistik Jam Sibuk (hanya untuk filter hari ini) -->
    <div class="section">
        <h3>Jam Sibuk Hari Ini</h3>
        <table>
            <tr>
                <th>Jam</th>
                <th>Jumlah Kendaraan Masuk</th>
            </tr>
            @foreach($stats['jam_sibuk'] ?? [] as $jam => $jumlah)
            <tr>
                <td>{{ $jam }}</td>
                <td>{{ $jumlah }}</td>
            </tr>
            @endforeach
        </table>
    </div>
    @endif

    @if($filterType != 'day' && isset($stats['grafik_harian']) && count($stats['grafik_harian']) > 0)
    <!-- Statistik Per Hari (untuk filter selain hari ini) -->
    <div class="section">
        <h3>Statistik Harian</h3>
        <table>
            <tr>
                <th>Tanggal</th>
                <th>Total</th>
                <th>Mobil</th>
                <th>Motor</th>
                <th>Bus</th>
            </tr>
            @foreach($stats['grafik_harian'] as $item)
            <tr>
                <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                <td>{{ $item->total }}</td>
                <td>{{ $item->mobil }}</td>
                <td>{{ $item->motor }}</td>
                <td>{{ $item->bus }}</td>
            </tr>
            @endforeach
        </table>
    </div>
    @endif

    <div class="section">
        <h3>Riwayat Parkir{{ $filterType == 'day' ? ' Hari Ini' : ' Terbaru' }}</h3>
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
                @forelse($parkirHistory as $parkir)
                <tr>
                    <td>{{ $parkir->nomor_kartu }}</td>
                    <td>{{ strtoupper($parkir->plat_nomor) }}</td>
                    <td>{{ $parkir->jenis_kendaraan }}</td>
                    <td>{{ \Carbon\Carbon::parse($parkir->waktu_masuk)->format('d/m/Y H:i') }}</td>
                    <td>{{ $parkir->waktu_keluar ? \Carbon\Carbon::parse($parkir->waktu_keluar)->format('d/m/Y H:i') : '-' }}</td>
                    <td>{{ $parkir->durasi }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data parkir pada periode ini</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <p style="font-size: 12px; text-align: center; color: #666;">
            Laporan ini dicetak pada {{ now()->format('d/m/Y H:i:s') }}
        </p>
    </div>
</body>
</html>