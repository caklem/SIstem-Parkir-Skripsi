@extends('layouts.main')

@section('title', 'Parkir Keluar')

@section('content')
<!-- Simple Navbar -->
<div class="navbar bg-white py-2 px-4 border-bottom d-flex align-items-center">
    <!-- Tombol Sidebar -->
    <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
        <i class="bi bi-list"></i>
    </a>
    <!-- Spasi Kecil -->
    <div class="ms-2"></div>
</div>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Data Parkir Keluar</h1>
                <p class="text-muted m-0">Manajemen data kendaraan keluar</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="#"><i class="nav-icon bi bi-box-arrow-right"></i></a></li>
                    <li class="breadcrumb-item active">Data Parkir Keluar</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <!-- Stats Cards -->
        <div class="row">
            <div class="col-6 col-md-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-warning elevation-1">
                        <i class="bi bi-car-front"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Kendaraan</span>
                        <span class="info-box-number">{{ $parkirKeluar->count() }}</span>
                        <div class="progress" style="height: 3px;">
                            <div class="progress-bar bg-warning" style="width: {{ $parkirKeluar->count() }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-md-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-success elevation-1">
                        <i class="bi bi-bicycle"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Sepeda Motor</span>
                        <span class="info-box-number">{{ $parkirKeluar->where('jenis_kendaraan', 'Sepeda Motor')->count() }}</span>
                        <div class="progress" style="height: 3px;">
                            <div class="progress-bar bg-success" style="width: {{ ($parkirKeluar->where('jenis_kendaraan', 'Sepeda Motor')->count() / max(1, $parkirs->count())) * 100 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-md-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-primary elevation-1">
                        <i class="bi bi-car-front-fill"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Mobil</span>
                        <span class="info-box-number">{{ $parkirKeluar->where('jenis_kendaraan', 'Mobil')->count() }}</span>
                        <div class="progress" style="height: 3px;">
                            <div class="progress-bar bg-primary" style="width: {{ ($parkirKeluar->where('jenis_kendaraan', 'Mobil')->count() / max(1, $parkirs->count())) * 100 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-md-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-danger elevation-1">
                        <i class="bi bi-bus-front"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Bus</span>
                        <span class="info-box-number">{{ $parkirKeluar->where('jenis_kendaraan', 'Bus')->count() }}</span>
                        <div class="progress" style="height: 3px;">
                            <div class="progress-bar bg-danger" style="width: {{ ($parkirKeluar->where('jenis_kendaraan', 'Bus')->count() / max(1, $parkirs->count())) * 100 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            
        <!-- Search & Buttons -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="d-flex flex-wrap justify-content-between gap-2">
                    

                    <!-- Search Box -->
                    <div class="col-md-4">
                        <form action="{{ route('parkir.keluar') }}" method="GET" class="position-relative" id="searchForm">
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" 
                                       id="search" 
                                       name="search" 
                                       class="form-control" 
                                       placeholder="Cari nomor kartu, plat nomor atau jenis kendaraan.." 
                                       value="{{ request('search') }}"
                                       autocomplete="off">
                                    @if(request('search'))
                                        <button type="button" class="btn btn-outline-secondary" id="clearSearch">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-warning shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">Daftar Kendaraan Keluar</h3>
                    </div>
                    <div>
                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah"> 
                            <i class="bi bi-plus-lg me-1"></i>
                            <span class="d-none d-md-inline">Tambah/Scan Data Keluar</span>
                            <span class="d-inline d-md-none">Tambah</span>
                        </button>
                        
                        <a href="{{ route('parkir.cetak-pdf-keluar') }}" class="btn btn-danger btn-sm">
                            <i class="bi bi-file-pdf me-1"></i>
                            <span class="d-none d-md-inline">Export PDF</span>
                            <span class="d-inline d-md-none">PDF</span>
                        </a>
                    </div>

                    <div class="card-body p-0">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show m-3">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    {{ session('success') }}
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="dataTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>No Kartu</th>
                                        <th>Plat Nomor</th>
                                        <th>Jenis Kendaraan</th>
                                        <th>Waktu Masuk</th>
                                        <th>Waktu Keluar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($parkirKeluar as $item)
                                        <tr>
                                            <td>{{ $item->nomor_kartu }}</td>
                                            <td class="text-uppercase">{{ $item->plat_nomor }}</td>
                                            <td>
                                                <span class="badge bg-{{ $item->jenis_kendaraan == 'Mobil' ? 'primary' : ($item->jenis_kendaraan == 'Sepeda Motor' ? 'success' : 'danger') }} rounded-pill">
                                                    <i class="bi bi-{{ $item->jenis_kendaraan == 'Mobil' ? 'car-front' : ($item->jenis_kendaraan == 'Sepeda Motor' ? 'bicycle' : 'bus-front') }} me-1"></i>
                                                    {{ $item->jenis_kendaraan }}
                                                </span>
                                            </td>
                                            <td>{{ $item->waktu_masuk_formatted }}</td>
                                            <td>{{ $item->waktu_keluar_formatted }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Tambah Data -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Data Parkir Keluar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <!-- Tambahkan Nav Tabs -->
            <ul class="nav nav-tabs" id="tabKeluar" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pilih-tab" data-bs-toggle="tab" data-bs-target="#pilihKendaraan" type="button" role="tab" aria-controls="pilihKendaraan" aria-selected="true">Pilih Kendaraan</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="scan-tab" data-bs-toggle="tab" data-bs-target="#scanQR" type="button" role="tab" aria-controls="scanQR" aria-selected="false">Scan QR Code</button>
                </li>
            </ul>
            
            <form id="formParkirKeluar">
                @csrf
                <div class="modal-body">
                    <div class="tab-content" id="tabKeluarContent">
                        <!-- Tab Pilih Kendaraan (Custom Dropdown) -->
                        <div class="tab-pane fade show active" id="pilihKendaraan" role="tabpanel" aria-labelledby="pilih-tab">
                            <!-- Struktur HTML Dropdown yang Diperbaiki -->
                            <div class="form-group mb-3">
                                <label for="kendaraanDropdownBtn" class="form-label">Pilih Kendaraan</label>
                                <div class="dropdown-container">
                                    <button type="button" class="form-control text-start d-flex justify-content-between align-items-center" onclick="toggleKendaraanDropdown()" id="kendaraanDropdownBtn">
                                        <span id="selectedKendaraanText">Pilih Kendaraan dari Parkir Masuk</span>
                                        <i class="bi bi-chevron-down"></i>
                                    </button>
                                    <div id="kendaraanDropdownContent" class="dropdown-menu w-100">
                                        <div class="px-2 py-1">
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">
                                                    <i class="bi bi-search"></i>
                                                </span>
                                                <input type="text" class="form-control" placeholder="Cari kendaraan..." id="kendaraanSearchInput" onkeyup="filterKendaraan()">
                                            </div>
                                        </div>
                                        <div class="dropdown-divider"></div>
                                        <div id="kendaraanList" class="dropdown-items-container">
                                            @foreach($parkirs as $parkir)
                                                <a href="javascript:void(0)" class="dropdown-item" 
                                                   data-id="{{ $parkir->id }}" 
                                                   data-nomor-kartu="{{ $parkir->nomor_kartu }}"
                                                   data-plat-nomor="{{ $parkir->plat_nomor }}"
                                                   data-jenis-kendaraan="{{ $parkir->jenis_kendaraan }}"
                                                   data-waktu-masuk="{{ $parkir->waktu_masuk }}"
                                                   onclick="selectKendaraan(this)">
                                                    <span class="badge bg-{{ $parkir->jenis_kendaraan == 'Mobil' ? 'primary' : ($parkir->jenis_kendaraan == 'Sepeda Motor' ? 'success' : 'danger') }} me-2">
                                                        <i class="bi bi-{{ $parkir->jenis_kendaraan == 'Mobil' ? 'car-front' : ($parkir->jenis_kendaraan == 'Sepeda Motor' ? 'bicycle' : 'bus-front') }}"></i>
                                                    </span>
                                                    {{ $parkir->nomor_kartu }} - {{ strtoupper($parkir->plat_nomor) }} ({{ $parkir->jenis_kendaraan }})
                                                </a>
                                            @endforeach
                                            @if(count($parkirs) == 0)
                                                <span class="dropdown-item disabled text-muted">Tidak ada data kendaraan</span>
                                            @endif
                                        </div>
                                    </div>
                                    <input type="hidden" name="searchKendaraan" id="searchKendaraan" value="">
                                </div>
                            </div>
                        </div>

                        <!-- Tab Scan QR (New) -->
                        <div class="tab-pane fade" id="scanQR" role="tabpanel" aria-labelledby="scan-tab">
                            <div class="form-group mb-3">
                                <div id="reader" class="mb-2" style="width: 100%"></div>
                                <div class="d-flex justify-content-center gap-2 mb-3">
                                    <button type="button" class="btn btn-warning" id="startButton">
                                        <i class="fas fa-camera"></i> Mulai Scanner
                                    </button>
                                    <button type="button" class="btn btn-danger" id="stopButton" style="display: none;">
                                        <i class="fas fa-stop"></i> Stop Scanner
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hasil Pencarian (Digunakan oleh kedua tab) -->
                    <div id="hasilPencarian" class="mt-3 d-none">
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
                                <div>Detail Kendaraan</div>
                                <button type="button" class="btn-close btn-close-white" id="btnCloseDetail" aria-label="Close"></button>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="40%">Nomor Kartu</td>
                                        <td width="60%">: <span id="noKartu">-</span></td>
                                    </tr>
                                    <tr>
                                        <td>Plat Nomor</td>
                                        <td>: <span id="platNomor">-</span></td>
                                    </tr>
                                    <tr>
                                        <td>Jenis Kendaraan</td>
                                        <td>: <span id="jenisKendaraan">-</span></td>
                                    </tr>
                                    <tr>
                                        <td>Waktu Masuk</td>
                                        <td>: <span id="waktuMasuk">-</span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-warning" id="btnProsesKeluar">Proses Keluar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('styles')
<!-- Tambahkan CSS Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .info-box {
        min-height: 100px;
        background: #fff;
        width: 100%;
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        border-radius: 0.5rem;
        margin-bottom: 1rem;
        display: flex;
        transition: all 0.3s ease;
    }

    .info-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 3px 15px rgba(0,0,0,.1);
    }

    .info-box-icon {
        border-radius: 0.5rem 0 0 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 80px;
        font-size: 1.875rem;
    }

    .info-box-content {
        padding: 1rem;
        flex: 1;
    }

    .info-box-number {
        display: block;
        font-weight: 700;
        font-size: 1.5rem;
    }

    .badge {
        padding: 0.5em 0.75em;
    }

    .table > :not(caption) > * > * {
        padding: 1rem 0.75rem;
    }

    .btn-group > .btn {
        border-radius: 0.375rem !important;
    }

    /* Sidebar toggle button styles */
    [data-lte-toggle="sidebar"] {
        cursor: pointer;
        color: #6c757d;
        transition: color 0.3s ease;
    }

    [data-lte-toggle="sidebar"]:hover {
        color: #000;
    }

    [data-lte-toggle="sidebar"] i {
        font-size: 1.5rem;
    }

    .highlight {
        background-color: #fff3cd;
        padding: 2px;
        border-radius: 3px;
    }

    #clearSearch {
        border: none;
        background: transparent;
        padding: 0.375rem 0.75rem;
    }

    #clearSearch:hover {
        color: #dc3545;
    }

    /* Style QR scanner */
    #reader {
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
    }

    #reader video {
        border-radius: 8px;
    }

    /* Tambahkan atau update style untuk Select2 */
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
        padding-left: 12px;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
    
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #ffc107 !important;
        color: #212529 !important;
    }
    
    .select2-container--open {
        z-index: 9999; /* Memastikan dropdown tidak tertutup modal */
    }
    
    .select2-container {
        width: 100% !important;
    }

    /* Style untuk Custom Dropdown */
    .custom-dropdown {
        position: relative;
        display: block;
        width: 100%;
        margin-bottom: 1rem;
    }

    /* Dropdown Button */
    .custom-dropbtn {
        background-color: #f8f9fa;
        color: #333;
        padding: 8px 12px;
        font-size: 14px;
        border: 1px solid #ced4da;
        cursor: pointer;
        width: 100%;
        text-align: left;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 4px;
    }

    .custom-dropbtn:after {
        content: '\25BC';
        font-size: 10px;
        color: #6c757d;
    }

    /* The search field */
    #kendaraanSearchInput {
        box-sizing: border-box;
        font-size: 14px;
        padding: 8px 12px;
        border: none;
        border-bottom: 1px solid #ddd;
        width: 100%;
        background-color: #f6f6f6;
    }

    /* The search field when it gets focus/clicked on */
    #kendaraanSearchInput:focus {
        outline: none;
        background-color: #fff;
    }

    /* Dropdown Content */
    .custom-dropdown-content {
        display: none;
        position: absolute;
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        z-index: 9999;
        width: 100%;
        max-height: 300px;
        overflow-y: auto;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-top: 2px;
    }
    

    /* Links inside the dropdown */
    .custom-dropdown-content a {
        color: black;
        padding: 8px 12px;
        text-decoration: none;
        display: block;
        border-bottom: 1px solid #f0f0f0;
        font-size: 14px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Styling untuk badge di dalam dropdown */
    .custom-dropdown-content a .badge {
        margin-right: 8px;
        padding: 3px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        border-radius: 50%;
    }

    .custom-dropdown-content a .badge i {
        font-size: 12px;
    }

    /* Change color of dropdown links on hover */
    .custom-dropdown-content a:hover {
        background-color: #f8f9fa;
    }

    /* Show the dropdown menu */
    .custom-dropdown-content.show {
        display: block;
    }

    /* Modal footer buttons */
    .modal-footer {
        padding: 0.75rem;
        border-top: 1px solid #dee2e6;
    }

    /* Dropdown Container */
    .dropdown {
        position: relative;
        width: 100%;
    }

    /* Dropdown Button */
    .dropbtn {
        background-color: #fff;
        color: #444;
        padding: 10px 14px;
        font-size: 15px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        width: 100%;
        text-align: left;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        transition: border-color 0.2s;
    }
    .dropbtn:focus, .dropbtn:hover {
        border-color: #a1a1a1;
        background: #f4f6f9;
    }

    /* Dropdown Content */
    .dropdown-content {
        display: none;
        position: absolute;
        background-color: #fff;
        min-width: 100%;
        border: 1px solid #ced4da;
        border-radius: 0 0 4px 4px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        z-index: 9999;
        max-height: 260px;
        overflow-y: auto;
        margin-top: 2px;
    }
    .dropdown-content.show {
        display: block;
        animation: fadeIn .2s;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-8px);}
        to { opacity: 1; transform: translateY(0);}
    }

    /* Search Input */
    #kendaraanSearchInput {
        width: 100%;
        padding: 10px 14px 10px 38px;
        border: none;
        border-bottom: 1px solid #eee;
        font-size: 14px;
        background: #f4f6f9 url('data:image/svg+xml;utf8,<svg fill="gray" height="18" viewBox="0 0 24 24" width="18" xmlns="http://www.w3.org/2000/svg"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99c.41.41 1.09.41 1.5 0s.41-1.09 0-1.5l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>') no-repeat 12px center;
        border-radius: 0;
        outline: none;
        margin-bottom: 0;
        box-sizing: border-box;
    }
    #kendaraanSearchInput:focus {
        background-color: #fff;
    }

    /* Dropdown Item */
    .dropdown-content a {
        color: #444;
        padding: 10px 14px;
        text-decoration: none;
        display: flex;
        align-items: center;
        border-bottom: 1px solid #f4f4f4;
        font-size: 14px;
        transition: background 0.15s;
        cursor: pointer;
        gap: 8px;
    }
    .dropdown-content a:last-child {
        border-bottom: none;
    }
    .dropdown-content a:hover, .dropdown-content a.active {
        background-color: #f4f6f9;
        color: #222d32;
    }

    /* Badge Style */
    .dropdown-content .badge {
        min-width: 24px;
        height: 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        margin-right: 8px;
        border-radius: 50%;
    }

    /* CSS untuk Dropdown yang Diperbaiki */
    .dropdown-container {
        position: relative;
        width: 100%;
    }

    /* Dropdown Button */
    #kendaraanDropdownBtn {
        background-color: #fff;
        cursor: pointer;
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: nowrap;
        height: 38px;
    }

    /* Dropdown Menu */
    .dropdown-menu {
        display: none;
        position: absolute;
        background: #fff;
        border: 1px solid rgba(0,0,0,.15);
        border-radius: 0.25rem;
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,.175);
        z-index: 1000;
        max-height: 280px;
        overflow-y: auto;
        padding: 0;
        margin-top: 0.125rem;
    }

    .dropdown-menu.show {
        display: block;
        animation: fadeIn 0.2s;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Search Input */
    .dropdown-menu .input-group {
        margin-bottom: 0;
    }

    .dropdown-menu .input-group-text {
        border: none;
        background: transparent;
    }

    .dropdown-menu .form-control {
        border: none;
        box-shadow: none;
        padding: 0.375rem 0.75rem;
    }

    /* Dropdown Items Container */
    .dropdown-items-container {
        max-height: 210px;
        overflow-y: auto;
    }

    /* Dropdown Items */
    .dropdown-item {
        display: flex;
        align-items: center;
        padding: 0.5rem 1rem;
        clear: both;
        font-weight: 400;
        color: #212529;
        text-align: inherit;
        white-space: nowrap;
        background-color: transparent;
        border: 0;
    }

    .dropdown-item:hover, .dropdown-item:focus {
        color: #16181b;
        text-decoration: none;
        background-color: #f8f9fa;
    }

    .dropdown-item.active {
        color: #fff;
        text-decoration: none;
        background-color: #007bff;
    }

    .dropdown-item .badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        padding: 0;
        border-radius: 50%;
    }

    .dropdown-divider {
        height: 0;
        margin: 0.5rem 0;
        overflow: hidden;
        border-top: 1px solid #e9ecef;
    }

    /* Tambahkan di dalam section styles atau push styles yang sudah ada */
    .btn-close-white {
        opacity: 0.8;
        transition: opacity 0.15s;
    }

    .btn-close-white:hover {
        opacity: 1;
        transform: scale(1.1);
    }

    .card-header {
        padding: 0.75rem 1rem;
    }
</style>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Variabel untuk menyimpan timeout pencarian
    let searchTimeout;

    // Handler untuk tombol close pada detail kendaraan
$('#btnCloseDetail').on('click', function() {
    // Sembunyikan detail kendaraan
    $('#hasilPencarian').addClass('d-none');
    
    // Reset pilihan kendaraan
    window.selectedKendaraanId = null;
    $('#searchKendaraan').val('');
    $('#selectedKendaraanText').text('Pilih Kendaraan dari Parkir Masuk');
    
    // Reset tampilan tab pilih kendaraan jika sedang aktif
    if ($('#pilih-tab').hasClass('active')) {
        // Bersihkan input pencarian
        $('#kendaraanSearchInput').val('');
        filterKendaraan();
    }
});

    // Handle search input dengan debounce
    $('#search').on('input', function() {
        clearTimeout(searchTimeout);
        
        searchTimeout = setTimeout(() => {
            $('#searchForm').submit();
        }, 500); // Tunggu 500ms setelah user selesai mengetik
    });

    // Handle clear search
    $('#clearSearch').on('click', function() {
        $('#search').val('');
        window.location.href = "{{ route('parkir.keluar') }}";
    });

    // Highlight search results
    let searchTerm = "{{ request('search') }}";
    if (searchTerm) {
        $("#dataTable tbody td").each(function() {
            let text = $(this).text();
            if (text.toLowerCase().includes(searchTerm.toLowerCase())) {
                let regex = new RegExp(searchTerm, 'gi');
                $(this).html(text.replace(regex, match => `<mark class="highlight">${match}</mark>`));
            }
        });
    }

    // Fungsi untuk memastikan QR library dimuat dengan benar
    function checkQRLibrary() {
        return typeof Html5QrcodeScanner !== 'undefined';
    }

    // Fungsi untuk force release kamera
    async function forceReleaseCamera() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: true });
            const tracks = stream.getTracks();
            tracks.forEach(track => track.stop());
            console.log('Camera released successfully');
        } catch (e) {
            console.error('Error releasing camera:', e);
        }
    }

    let html5QrcodeScanner = null;
    window.selectedKendaraanId = null;
    
    // Function untuk stop scanner dengan lebih bersih
    function stopScanner() {
        if (html5QrcodeScanner) {
            try {
                html5QrcodeScanner.clear().then(() => {
                    console.log('Scanner cleared successfully');
                }).catch(error => {
                    console.error('Error clearing scanner:', error);
                }).finally(() => {
                    setTimeout(() => {
                        html5QrcodeScanner = null;
                    }, 300);
                });
            } catch (err) {
                console.error('Error stopping scanner:', err);
                html5QrcodeScanner = null;
            }
        }
        $('#stopButton').hide();
        $('#startButton').show();
    }

    // Fungsi untuk memproses kendaraan keluar
    function prosesKendaraanKeluar(id) {
        if (!id) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'ID kendaraan tidak valid',
                confirmButtonColor: '#dc3545'
            });
            return;
        }
        
        console.log('Memproses kendaraan keluar dengan ID:', id);
        
        $.ajax({
            url: '{{ route("parkir.proses-keluar") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: id
            },
            dataType: 'json',
            beforeSend: function() {
                // Tampilkan loading 
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang memproses kendaraan keluar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(response) {
                console.log('Respons sukses:', response);
                
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message || 'Kendaraan berhasil diproses keluar',
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        $('#modalTambah').modal('hide');
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.message || 'Gagal memproses kendaraan keluar',
                        confirmButtonColor: '#dc3545'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX:', xhr, status, error);
                
                let errorMessage = 'Terjadi kesalahan sistem';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage,
                    confirmButtonColor: '#dc3545'
                });
            }
        });
    }

    // Fungsi untuk inisialisasi scanner
    function initScanner() {
        if (!checkQRLibrary()) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Library QR Scanner tidak dimuat. Silakan refresh halaman.'
            });
            return;
        }

        $('#stopButton').show();
        $('#startButton').hide();
        
        try {
            console.log('Initializing scanner...');
            
            // Buat instance QR scanner baru
            html5QrcodeScanner = new Html5QrcodeScanner(
                "reader", 
                { 
                    fps: 10, 
                    qrbox: { width: 250, height: 250 },
                    rememberLastUsedCamera: false,
                    aspectRatio: 1.0
                }
            );
            
            console.log('Scanner initialized');
            
            // Render scanner dengan callback success dan error
            html5QrcodeScanner.render(onScanSuccess, onScanError);
        } catch (e) {
            console.error('Error creating scanner:', e);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Tidak dapat membuat scanner. Error: ' + e.message
            });
            $('#stopButton').hide();
            $('#startButton').show();
        }
    }

    // Handler untuk QR yang berhasil di-scan
    function onScanSuccess(decodedText) {
        console.log('QR Code detected:', decodedText);
        
        // Cari data kendaraan berdasarkan nomor kartu
        $.ajax({
            url: '{{ route("parkir.cari-kartu") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                nomor_kartu: decodedText
            },
            success: function(response) {
                console.log('Response from server:', response);
                
                if (response.success) {
                    const data = response.data;
                    
                    // Cek apakah kendaraan sudah keluar
                    if (data.waktu_keluar) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Kendaraan Sudah Keluar',
                            text: 'Kendaraan dengan nomor kartu ini sudah tercatat keluar',
                            confirmButtonColor: '#dc3545'
                        });
                        return;
                    }
                    
                    // Tampilkan data kendaraan
                    selectedKendaraanId = data.id;
                    $('#noKartu').text(data.nomor_kartu);
                    $('#platNomor').text(data.plat_nomor);
                    $('#jenisKendaraan').text(data.jenis_kendaraan);
                    $('#waktuMasuk').text(moment(data.waktu_masuk).format('DD/MM/YYYY HH:mm:ss'));
                    $('#hasilPencarian').removeClass('d-none');
                    
                    // Stop scanner
                    stopScanner();
                    
                    // Otomatis proses kendaraan keluar
                    Swal.fire({
                        icon: 'success',
                        title: 'Kendaraan Ditemukan',
                        text: 'Kendaraan akan diproses keluar secara otomatis',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        // Langsung proses keluar setelah scan
                        prosesKendaraanKeluar(data.id);
                    });
                } else {
                    // Kartu tidak ditemukan
                    Swal.fire({
                        icon: 'error',
                        title: 'Kartu Tidak Ditemukan',
                        text: response.message || 'Kartu tidak terdaftar atau tidak ada data parkir masuk',
                        confirmButtonColor: '#dc3545'
                    });
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan saat mencari data',
                    confirmButtonColor: '#dc3545'
                });
            }
        });
    }

    // Handler untuk error saat scan
    function onScanError(errorMessage) {
        console.error('QR Scanner error:', errorMessage);
    }

    // Start QR Scanner
    $('#startButton').click(async function() {
        // Force release camera terlebih dahulu
        await forceReleaseCamera();
        
        // Jeda sejenak sebelum menginisialisasi scanner
        setTimeout(initScanner, 300);
    });

    // Stop Scanner Button
    $('#stopButton').click(function() {
        stopScanner();
    });

    // Reset saat modal ditutup
    $('#modalTambah').on('hidden.bs.modal', async function() {
        console.log('Modal hidden');
        stopScanner();
        await forceReleaseCamera();
        window.selectedKendaraanId = null;
        
        // Reset custom dropdown
        $('#searchKendaraan').val('');
        $('#selectedKendaraanText').text('Pilih Kendaraan dari Parkir Masuk');
        $('#hasilPencarian').addClass('d-none');
        
        // Reset dropdown content
        var dropdowns = document.getElementsByClassName("custom-dropdown-content");
        for (var i = 0; i < dropdowns.length; i++) {
            dropdowns[i].classList.remove('show');
        }
        
        // Reset filter
        document.getElementById('kendaraanSearchInput').value = '';
        filterKendaraan();
    });

    // Handle tab change
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        console.log('Tab changed:', e.target.id);
        stopScanner();
    });

    // Toggle dropdown
    window.onclick = function(event) {
        if (!event.target.matches('.custom-dropbtn') && !event.target.closest('.custom-dropdown-content')) {
            var dropdowns = document.getElementsByClassName("custom-dropdown-content");
            for (var i = 0; i < dropdowns.length; i++) {
                var openDropdown = dropdowns[i];
                if (openDropdown.classList.contains('show')) {
                    openDropdown.classList.remove('show');
                }
            }
        }
    }
    
    // Handler untuk proses keluar (tombol manual)
    $('#btnProsesKeluar').on('click', function(e) {
        e.preventDefault();
        console.log('Tombol proses keluar diklik. ID Kendaraan:', window.selectedKendaraanId);

        if (!window.selectedKendaraanId) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Pilih kendaraan atau scan QR code terlebih dahulu!',
                confirmButtonColor: '#ffc107'
            });
            return;
        }

        Swal.fire({
            title: 'Konfirmasi',
            text: 'Apakah Anda yakin ingin memproses kendaraan keluar?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Proses!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                prosesKendaraanKeluar(window.selectedKendaraanId);
            }
        });
    });
});

// Toggle dropdown
function toggleKendaraanDropdown() {
    document.getElementById("kendaraanDropdownContent").classList.toggle("show");
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('#kendaraanDropdownBtn') && !event.target.closest('#kendaraanDropdownContent')) {
        var dropdowns = document.getElementsByClassName("dropdown-menu");
        for (var i = 0; i < dropdowns.length; i++) {
            dropdowns[i].classList.remove("show");
        }
    }
});

// Filter function
function filterKendaraan() {
    var input = document.getElementById("kendaraanSearchInput");
    var filter = input.value.toUpperCase();
    var items = document.getElementById("kendaraanList").getElementsByTagName("a");
    var noResults = true;
    
    for (var i = 0; i < items.length; i++) {
        var txtValue = items[i].textContent || items[i].innerText;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
            items[i].style.display = "";
            noResults = false;
        } else {
            items[i].style.display = "none";
        }
    }
    
    // Show "No results" message if needed
    var noResultsElem = document.getElementById("noResults");
    if (noResults) {
        if (!noResultsElem) {
            noResultsElem = document.createElement("span");
            noResultsElem.id = "noResults";
            noResultsElem.className = "dropdown-item disabled text-muted";
            noResultsElem.textContent = "Tidak ditemukan";
            document.getElementById("kendaraanList").appendChild(noResultsElem);
        }
    } else if (noResultsElem) {
        noResultsElem.remove();
    }
}

// Selection function
function selectKendaraan(element) {
    var id = element.getAttribute('data-id');
    var text = element.textContent.trim();
    
    // Update button text
    document.getElementById("selectedKendaraanText").textContent = id ? text : 'Pilih Kendaraan dari Parkir Masuk';
    
    // Update hidden input
    document.getElementById("searchKendaraan").value = id;
    
    // Close dropdown
    document.getElementById("kendaraanDropdownContent").classList.remove("show");
    
    // Update global variable
    window.selectedKendaraanId = id;
    
    // Update vehicle details
    if (id) {
        $('#noKartu').text(element.getAttribute('data-nomor-kartu'));
        $('#platNomor').text(element.getAttribute('data-plat-nomor'));
        $('#jenisKendaraan').text(element.getAttribute('data-jenis-kendaraan'));
        $('#waktuMasuk').text(moment(element.getAttribute('data-waktu-masuk')).format('DD/MM/YYYY HH:mm:ss'));
        $('#hasilPencarian').removeClass('d-none');
    } else {
        $('#hasilPencarian').addClass('d-none');
    }
}
</script>
@endpush