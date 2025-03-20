@extends('layouts.main')

@section('title', 'Dashboard')

@section('content')
<!-- Simple Navbar -->
<div class="navbar bg-white py-2 px-4 border-bottom d-flex align-items-center">
    <!-- Tombol Sidebar -->
    <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
        <i class="bi bi-list"></i>
    </a>
    <!-- Spasi Kecil -->
    <div class="ms-2"></div>
    <!-- Judul -->
    {{-- <h5 class="text-warning font-weight-bold mb-0">Dashboard Parkir Keluar</h5>     --}}
</div>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Dashboard</h1>
                <p class="text-muted m-0">Manajemen data parkir kendaraan Golden Hill</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#"><i class="bi bi-house"></i></a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form id="dateRangeForm" class="form-inline">
            <div class="form-group mr-3">
                <label class="mr-2">Filter:</label>
                <select name="range" class="form-control" onchange="this.form.submit()">
                    <option value="day" {{ request('range') == 'day' ? 'selected' : '' }}>Hari Ini</option>
                    <option value="week" {{ request('range') == 'week' ? 'selected' : '' }}>Minggu Ini</option>
                    <option value="month" {{ request('range') == 'month' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="year" {{ request('range') == 'year' ? 'selected' : '' }}>Tahun Ini</option>
                    <option value="custom" {{ request('range') == 'custom' ? 'selected' : '' }}>Custom</option>
                </select>
            </div>
            <div id="customDateInputs" style="{{ request('range') == 'custom' ? '' : 'display: none;' }}">
                <div class="form-group mr-3">
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="form-group mr-3">
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <button type="submit" class="btn btn-primary">Terapkan</button>
            </div>
        </form>
        
        <!-- Add export button -->
        <div class="mt-3">
            <a href="{{ route('dashboard.export-pdf', [
                'range' => request('range', 'day'),
                'start_date' => request('start_date'),
                'end_date' => request('end_date')
            ]) }}" 
            class="btn btn-danger">
                <i class="fas fa-file-pdf mr-2"></i>Export PDF
            </a>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Info Boxes -->
        <div class="row">
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="bi bi-car-front-fill"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Kendaraan Aktif</span>
                        <span class="info-box-number">{{ $stats['total_kendaraan_aktif'] }}</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="bi bi-box-arrow-in-right"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Masuk</span>
                        <span class="info-box-number">{{ $stats['total_masuk'] }}</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="bi bi-box-arrow-right"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Keluar</span>
                        <span class="info-box-number">{{ $stats['total_keluar'] }}</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-danger"><i class="bi bi-clock"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Rata-rata Durasi</span>
                        <span class="info-box-number">{{ $stats['rata_rata_durasi'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafik -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Statistik Kendaraan</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="statistikChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Jenis Kendaraan</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="jenisKendaraanChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ganti bagian Tabel Kendaraan Terbaru dengan ini -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header p-2">
                        <ul class="nav nav-pills" id="parkirTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="pill" href="#parkir-summary" role="tab">Ringkasan Parkir</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="pill" href="#parkir-history" role="tab">Riwayat Parkir</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <!-- Tab Ringkasan -->
                            <div class="tab-pane fade show active" id="parkir-summary" role="tabpanel">
                                <div class="row">
                                    <!-- Okupansi per Jenis -->
                                    <div class="col-md-4">
                                        <div class="info-box bg-gradient-info">
                                            <span class="info-box-icon"><i class="bi bi-car-front"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Mobil Hari Ini</span>
                                                <span class="info-box-number">{{ $stats['mobil_hari_ini'] ?? 0 }}</span>
                                                <div class="progress">
                                                    @php
                                                        $totalKendaraan = ($stats['total_kendaraan_hari_ini'] ?? 0);
                                                        $persentaseMobil = $totalKendaraan > 0 ? 
                                                            ($stats['mobil_hari_ini'] / $totalKendaraan) * 100 : 0;
                                                    @endphp
                                                    <div class="progress-bar" style="width: {{ $persentaseMobil }}%"></div>
                                                </div>
                                                <span class="progress-description">
                                                    {{ number_format($persentaseMobil, 1) }}% dari total kendaraan
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="info-box bg-gradient-success">
                                            <span class="info-box-icon"><i class="bi bi-bicycle"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Motor Hari Ini</span>
                                                <span class="info-box-number">{{ $stats['motor_hari_ini'] ?? 0 }}</span>
                                                <div class="progress">
                                                    @php
                                                        $persentaseMotor = $totalKendaraan > 0 ? 
                                                            ($stats['motor_hari_ini'] / $totalKendaraan) * 100 : 0;
                                                    @endphp
                                                    <div class="progress-bar" style="width: {{ $persentaseMotor }}%"></div>
                                                </div>
                                                <span class="progress-description">
                                                    {{ number_format($persentaseMotor, 1) }}% dari total kendaraan
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="info-box bg-gradient-warning">
                                            <span class="info-box-icon"><i class="bi bi-clock-history"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Rata-rata Durasi Hari Ini</span>
                                                <span class="info-box-number">
                                                    @php
                                                        $durasi = $stats['rata_rata_durasi_hari_ini'] ?? 0;
                                                        $jam = floor($durasi / 60);
                                                        $menit = $durasi % 60;
                                                    @endphp
                                                    {{ $durasi > 0 ? "$jam jam $menit menit" : '-' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="info-box bg-gradient-primary">
                                            <span class="info-box-icon"><i class="bi bi-bus-front"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Bus Hari Ini</span>
                                                <span class="info-box-number">{{ $stats['bus_hari_ini'] ?? 0 }}</span>
                                                <div class="progress">
                                                    @php
                                                        $persentaseBus = $totalKendaraan > 0 ? 
                                                            ($stats['bus_hari_ini'] / $totalKendaraan) * 100 : 0;
                                                    @endphp
                                                    <div class="progress-bar" style="width: {{ $persentaseBus }}%"></div>
                                                </div>
                                                <span class="progress-description">
                                                    {{ number_format($persentaseBus, 1) }}% dari total kendaraan
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab Riwayat -->
                            <div class="tab-pane fade" id="parkir-history" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>No Kartu</th>
                                                <th>Plat Nomor</th>
                                                <th>Jenis</th>
                                                <th>Masuk</th>
                                                <th>Keluar</th>
                                                <th>Durasi</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($parkirHistory as $parkir)
                                            <tr>
                                                <td>{{ $parkir->nomor_kartu }}</td>
                                                <td class="text-uppercase">{{ $parkir->plat_nomor }}</td>
                                                <td>
                                                    <span class="badge bg-{{ 
                                                        $parkir->jenis_kendaraan === 'Mobil' ? 'primary' : 
                                                        ($parkir->jenis_kendaraan === 'Bus' ? 'info' : 'success') 
                                                    }}">
                                                        {{ $parkir->jenis_kendaraan }}
                                                    </span>
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($parkir->waktu_masuk)->format('d/m H:i') }}</td>
                                                <td>{{ $parkir->waktu_keluar ? \Carbon\Carbon::parse($parkir->waktu_keluar)->format('d/m H:i') : '-' }}</td>
                                                <td>{{ $parkir->durasi }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $parkir->waktu_keluar ? 'success' : 'warning' }}">
                                                        {{ $parkir->waktu_keluar ? 'Selesai' : 'Aktif' }}
                                                    </span>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="7" class="text-center">Tidak ada data parkir</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Data Parkir -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Data Parkir Masuk & Keluar Terbaru</h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No Kartu</th>
                                    <th>Plat Nomor</th>
                                    <th>Jenis</th>
                                    <th>Waktu Masuk</th>
                                    <th>Waktu Keluar</th>
                                    <th>Durasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($parkirHistory as $parkir)
                                <tr>
                                    <td>{{ $parkir->nomor_kartu }}</td>
                                    <td class="text-uppercase">{{ $parkir->plat_nomor }}</td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $parkir->jenis_kendaraan === 'Mobil' ? 'primary' : 
                                            ($parkir->jenis_kendaraan === 'Bus' ? 'info' : 'success') 
                                        }}">
                                            {{ $parkir->jenis_kendaraan }}
                                        </span>
                                    </td>
                                    <td>{{ Carbon\Carbon::parse($parkir->waktu_masuk)->format('d/m/Y H:i:s') }}</td>
                                    <td>{{ Carbon\Carbon::parse($parkir->waktu_keluar)->format('d/m/Y H:i:s') }}</td>
                                    <td>{{ $parkir->durasi }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-3">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                            <p class="text-muted mt-2">Belum ada data parkir</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Handle custom date range visibility
    $('select[name="range"]').change(function() {
        if ($(this).val() === 'custom') {
            $('#customDateInputs').show();
        } else {
            $('#customDateInputs').hide();
            this.form.submit();
        }
    });

    // Data untuk grafik statistik
    const statistikData = {
        labels: @json($kendaraanMasukKeluar->pluck('tanggal')->map(function($date) {
            return \Carbon\Carbon::parse($date)->format('d/m/Y');
        })),
        datasets: [{
            label: 'Mobil',
            data: @json($kendaraanMasukKeluar->pluck('mobil')),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            fill: true,
            tension: 0.4
        },
        {
            label: 'Motor',
            data: @json($kendaraanMasukKeluar->pluck('motor')),
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            fill: true,
            tension: 0.4
        },
        {
            label: 'Bus',
            data: @json($kendaraanMasukKeluar->pluck('bus')),
            borderColor: 'rgb(54, 162, 235)',
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            fill: true,
            tension: 0.4
        }]
    };

    // Inisialisasi grafik statistik
    new Chart(document.getElementById('statistikChart'), {
        type: 'line',
        data: statistikData,
        options: {
            responsive: true,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        title: function(context) {
                            return 'Tanggal: ' + context[0].label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Data untuk pie chart
    const pieData = {
        labels: @json($jenisKendaraan->pluck('jenis_kendaraan')),
        datasets: [{
            data: @json($jenisKendaraan->pluck('total')),
            backgroundColor: [
                'rgb(75, 192, 192)', // Mobil
                'rgb(255, 99, 132)', // Motor
                'rgb(54, 162, 235)'  // Bus
            ]
        }]
    };

    // Inisialisasi pie chart
    new Chart(document.getElementById('jenisKendaraanChart'), {
        type: 'pie',
        data: pieData,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });

    // Handle form submit
    $('#dateRangeForm').on('submit', function(e) {
        e.preventDefault();
        window.location.href = '{{ route("dashboard") }}?' + $(this).serialize();
    });
});
</script>
@endpush