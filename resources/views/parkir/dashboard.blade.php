@extends('layouts.main')

@section('title', 'Dashboard Parkir')

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
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
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

<div class="card card-body mb-4">
    <div class="container-fluid">
        <form id="dateRangeForm" action="{{ route('dashboard') }}" method="GET">
            <div class="row">
                <div class="col-md-4 col-sm-12 mb-2">
                    <label class="mb-1">Filter Periode:</label>
                    <select name="range" class="form-control" id="rangeSelect">
                        <option value="day" {{ request('range') == 'day' ? 'selected' : '' }}>Hari Ini</option>
                        <option value="week" {{ request('range') == 'week' ? 'selected' : '' }}>Minggu Ini</option>
                        <option value="month" {{ request('range') == 'month' ? 'selected' : '' }}>Bulan Ini</option>
                        <option value="year" {{ request('range') == 'year' ? 'selected' : '' }}>Tahun Ini</option>
                        <option value="custom" {{ request('range') == 'custom' ? 'selected' : '' }}>Custom</option>
                    </select>
                </div>
                
                <div id="customDateInputs" class="row {{ request('range') == 'custom' ? '' : 'd-none' }}">
                    <div class="col-md-3 col-sm-6 mb-2">
                        <label class="mb-1">Tanggal Mulai:</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2">
                        <label class="mb-1">Tanggal Akhir:</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-2 col-sm-12">
                        <label class="d-md-block d-sm-none mb-1">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">Terapkan</button>
                    </div>
                </div>
            </div>
            
            <div class="mt-3">
                <a href="{{ route('dashboard.export-pdf', [
                    'range' => request('range', 'day'),
                    'start_date' => request('start_date'),
                    'end_date' => request('end_date')
                ]) }}" 
                class="btn btn-danger">
                    <i class="fas fa-file-pdf me-2"></i>Export PDF
                </a>
            </div>
        </form>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            @foreach ([
                [
                    'bg' => 'bg-info', 
                    'icon' => 'bi bi-car-front-fill', 
                    'text' => 'Kendaraan Aktif', 
                    'number' => $stats['total_kendaraan_aktif']
                ],
                [
                    'bg' => 'bg-success', 
                    'icon' => 'bi bi-box-arrow-in-right', 
                    'text' => 'Total Masuk', 
                    'number' => $stats['total_masuk']
                ],
                [
                    'bg' => 'bg-warning', 
                    'icon' => 'bi bi-box-arrow-right', 
                    'text' => 'Total Keluar', 
                    'number' => $stats['total_keluar']
                ],
                [
                    'bg' => 'bg-danger', 
                    'icon' => 'bi bi-clock', 
                    'text' => 'Rata-rata Durasi', 
                    'number' => $stats['rata_rata_durasi']
                ]
            ] as $box)
            <div class="col-12 col-sm-6 col-md-3 mb-3">
                <div class="info-box h-100">
                    <span class="info-box-icon {{ $box['bg'] }}"><i class="{{ $box['icon'] }}"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ $box['text'] }}</span>
                        <span class="info-box-number">{{ $box['number'] }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="row">
            <div class="col-lg-8 col-md-12 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="card-title">Statistik Kendaraan</h3>
                    </div>
                    <div class="card-body">
                        <div style="position: relative; height: 300px;">
                            <canvas id="statistikChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-12 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="card-title">Jenis Kendaraan</h3>
                    </div>
                    <div class="card-body">
                        <div style="position: relative; height: 300px;">
                            <canvas id="jenisKendaraanChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                            <div class="tab-pane fade show active" id="parkir-summary" role="tabpanel">
                                <div class="row">
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

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title">
                                Data Parkir Masuk & Keluar 
                                @if(request('range') == 'day')
                                    Hari Ini
                                @elseif(request('range') == 'week')
                                    Minggu Ini
                                @elseif(request('range') == 'month')
                                    Bulan Ini
                                @elseif(request('range') == 'year')
                                    Tahun Ini
                                @elseif(request('range') == 'custom')
                                    {{ \Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') }} - 
                                    {{ \Carbon\Carbon::parse(request('end_date'))->format('d/m/Y') }}
                                @endif
                            </h3>
                            <div class="card-tools">
                                <div class="input-group input-group-sm" style="width: 250px;">
                                    <input type="text" name="table_search" id="parkirSearch" class="form-control float-right" placeholder="Cari (plat nomor, kartu)">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-default">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover" id="parkirTable">
                            <thead>
                                <tr>
                                    <th>No Kartu</th>
                                    <th>Plat Nomor</th>
                                    <th>Jenis</th>
                                    <th>Waktu</th>
                                    <th>Status</th>
                                    <th>Durasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $groupedData = $parkirHistory->groupBy(function($item) {
                                        return \Carbon\Carbon::parse($item->waktu_masuk)->format('Y-m-d');
                                    });
                                @endphp

                                @if($groupedData->count() > 0)
                                    @foreach($groupedData as $date => $items)
                                        <tr class="bg-light">
                                            <td colspan="6" class="font-weight-bold text-primary">
                                                {{ \Carbon\Carbon::parse($date)->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                                            </td>
                                        </tr>
                                        
                                        @foreach($items as $parkir)
                                        <tr class="parkir-row">
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
                                            <td>{{ \Carbon\Carbon::parse($parkir->waktu_masuk)->format('H:i:s') }}</td>
                                            <td>
                                                <span class="badge bg-{{ $parkir->waktu_keluar ? 'success' : 'warning' }}">
                                                    {{ $parkir->waktu_keluar ? 'Keluar' : 'Aktif' }}
                                                </span>
                                            </td>
                                            <td>{{ $parkir->durasi }}</td>
                                        </tr>
                                        @endforeach
                                    @endforeach
                                @else
                                    <tr id="no-data-row">
                                        <td colspan="6" class="text-center py-3">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                                <p class="text-muted mt-2">Tidak ada data parkir untuk periode ini</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.85rem;
        }
        
        .badge {
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
        }
        
        .card-title {
            font-size: 1.1rem;
        }
        
        .card-tools {
            margin-top: 0.5rem;
            width: 100%;
        }
        
        .card-tools .input-group {
            width: 100% !important;
        }
        
        .nav-pills .nav-link {
            padding: 0.4rem 0.6rem;
            font-size: 0.85rem;
        }
        
        .info-box {
            margin-bottom: 0.5rem;
        }
        
        .info-box-content {
            padding: 5px 10px;
        }
        
        .info-box-text {
            white-space: normal;
        }
    }
    
    canvas {
        width: 100% !important;
        height: 100% !important;
        max-height: 300px;
    }
    
    .info-box-number {
        font-size: 1.4rem;
        font-weight: 600;
    }
    
    #statistikChart, #jenisKendaraanChart {
        margin: 0 auto;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    $('#rangeSelect').change(function() {
        if ($(this).val() === 'custom') {
            $('#customDateInputs').removeClass('d-none');
        } else {
            $('#customDateInputs').addClass('d-none');
            this.form.submit();
        }
    });

    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: window.innerWidth < 768 ? 'bottom' : 'top',
                labels: {
                    boxWidth: window.innerWidth < 768 ? 12 : 40,
                    font: {
                        size: window.innerWidth < 768 ? 10 : 12
                    }
                }
            },
            tooltip: {
                titleFont: {
                    size: window.innerWidth < 768 ? 10 : 12
                },
                bodyFont: {
                    size: window.innerWidth < 768 ? 10 : 12
                },
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
                    stepSize: 1,
                    font: {
                        size: window.innerWidth < 768 ? 8 : 12
                    }
                }
            },
            x: {
                ticks: {
                    font: {
                        size: window.innerWidth < 768 ? 8 : 12
                    },
                    maxRotation: window.innerWidth < 768 ? 45 : 0,
                    minRotation: window.innerWidth < 768 ? 45 : 0
                }
            }
        }
    };

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

    new Chart(document.getElementById('statistikChart'), {
        type: 'line',
        data: statistikData,
        options: chartOptions
    });

    const pieData = {
        labels: @json($jenisKendaraan->pluck('jenis_kendaraan')),
        datasets: [{
            data: @json($jenisKendaraan->pluck('total')),
            backgroundColor: [
                'rgb(75, 192, 192)',
                'rgb(255, 99, 132)',
                'rgb(54, 162, 235)'
            ]
        }]
    };

    new Chart(document.getElementById('jenisKendaraanChart'), {
        type: 'pie',
        data: pieData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: window.innerWidth < 768 ? 'bottom' : 'right',
                    labels: {
                        font: {
                            size: window.innerWidth < 768 ? 10 : 12
                        },
                        boxWidth: window.innerWidth < 768 ? 10 : 20
                    }
                }
            }
        }
    });

    $('#parkirSearch').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        let found = false;
        
        $('.parkir-row').show();
        $('.bg-light').show();
        
        $('.parkir-row').filter(function() {
            const rowText = $(this).text().toLowerCase();
            const isVisible = rowText.indexOf(value) > -1;
            $(this).toggle(isVisible);
            if (isVisible) found = true;
            
            if (!isVisible && $(this).prev().hasClass('bg-light')) {
                const groupHeader = $(this).prev();
                const allGroupHidden = $(this).nextUntil('.bg-light').addBack().filter(':visible').length === 0;
                if (allGroupHidden) {
                    groupHeader.hide();
                }
            }
        });
        
        if (value.length > 0) {
            if (!found) {
                $('#no-results').show();
                $('#no-data-row').hide();
            } else {
                $('#no-results').hide();
            }
        } else {
            $('#no-results').hide();
            if ($('.parkir-row').length === 0) {
                $('#no-data-row').show();
            }
        }
    });

    $('#dateRangeForm').on('submit', function(e) {
        e.preventDefault();
        window.location.href = '{{ route("dashboard") }}?' + $(this).serialize();
    });

    $(window).on('resize', function() {
        Chart.instances.forEach(chart => {
            if (chart.options.plugins && chart.options.plugins.legend) {
                if (chart.config.type === 'pie') {
                    chart.options.plugins.legend.position = window.innerWidth < 768 ? 'bottom' : 'right';
                } else {
                    chart.options.plugins.legend.position = window.innerWidth < 768 ? 'bottom' : 'top';
                }
                chart.update();
            }
        });
    });
});
</script>
@endpush