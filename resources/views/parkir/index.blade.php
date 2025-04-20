@extends('layouts.main')

@section('title', 'Data Parkir Masuk')

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
                <h1 class="m-0 text-dark">Data Parkir Masuk</h1>
                <p class="text-muted m-0">Manajemen data parkir kendaraan Golden Hill</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#"><i class="nav-icon bi bi-box-arrow-in-right"></i></a></li>
                    <li class="breadcrumb-item active">Data Parkir Masuk</li>
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
                        <span class="info-box-number">{{ $parkirs->count() }}</span>
                        <div class="progress" style="height: 3px;">
                            <div class="progress-bar bg-warning" style="width: 100%"></div>
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
                        @php
                            $totalKendaraan = $parkirs->count();
                            $totalMotor = $parkirs->where('jenis_kendaraan', 'Sepeda Motor')->count();
                            $persenMotor = $totalKendaraan > 0 ? ($totalMotor / $totalKendaraan) * 100 : 0;
                        @endphp
                        <span class="info-box-number">{{ $totalMotor }}</span>
                        <div class="progress" style="height: 3px;">
                            <div class="progress-bar bg-success" style="width: {{ $persenMotor }}%"></div>
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
                        @php
                            $totalMobil = $parkirs->where('jenis_kendaraan', 'Mobil')->count();
                            $persenMobil = $totalKendaraan > 0 ? ($totalMobil / $totalKendaraan) * 100 : 0;
                        @endphp
                        <span class="info-box-number">{{ $totalMobil }}</span>
                        <div class="progress" style="height: 3px;">
                            <div class="progress-bar bg-primary" style="width: {{ $persenMobil }}%"></div>
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
                        @php
                            $totalBus = $parkirs->where('jenis_kendaraan', 'Bus')->count();
                            $persenBus = $totalKendaraan > 0 ? ($totalBus / $totalKendaraan) * 100 : 0;
                        @endphp
                        <span class="info-box-number">{{ $totalBus }}</span>
                        <div class="progress" style="height: 3px;">
                            <div class="progress-bar bg-danger" style="width: {{ $persenBus }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Table Card -->
        <div class="card card-outline card-warning shadow-sm">
            <div class="card-header">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center">
                        <h3 class="card-title fs-5 mb-0">
                            <i class="bi bi-table me-2"></i>
                            Data Kendaraan Parkir
                        </h3>
                    </div>
                    <!-- Menampilkan atau menyembunyikan tombol tambah data sesuai role -->
                    <div class="d-flex gap-2 flex-wrap">
                        @if(!Auth::user()->isAdmin())
                            <button type="button" class="btn btn-warning btn-sm btn-md-normal" id="btnTambah">
                                <i class="bi bi-plus-lg me-1"></i>
                                <span class="d-none d-md-inline">Tambah Data</span>
                                <span class="d-inline d-md-none">Tambah</span>
                            </button>
                        @endif
                        <a href="{{ route('parkir.cetak-pdf-masuk') }}" class="btn btn-danger btn-sm btn-md-normal">
                            <i class="bi bi-file-pdf me-1"></i>
                            <span class="d-none d-md-inline">Export PDF</span>
                            <span class="d-inline d-md-none">PDF</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            {{ session('success') }}
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Search Box -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <form action="{{ route('parkir.index') }}" method="GET" class="position-relative" id="searchForm">
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" 
                                       id="search" 
                                       name="search" 
                                       class="form-control" 
                                       placeholder="Cari nomor kartu atau jenis kendaraan..." 
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
                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="dataTable">
                        <thead class="table-light">
                            <tr>
                                <th>No Kartu</th>
                                <th>Nomor Plat</th>
                                <th>Jenis Kendaraan</th>
                                <th>Waktu Masuk</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($parkirs as $parkir)
                                <tr>
                                    <td>
                                        <span class="fw-medium">{{ $parkir->nomor_kartu }}</span>
                                    </td>
                                    <td class="text-uppercase">{{ $parkir->plat_nomor }}</td>
                                    <td>
                                        <span class="badge bg-{{ $parkir->jenis_kendaraan == 'Mobil' ? 'primary' : ($parkir->jenis_kendaraan == 'Sepeda Motor' ? 'success' : 'danger') }} rounded-pill">
                                            <i class="bi bi-{{ $parkir->jenis_kendaraan == 'Mobil' ? 'car-front' : ($parkir->jenis_kendaraan == 'Sepeda Motor' ? 'bicycle' : 'bus-front') }} me-1"></i>
                                            {{ $parkir->jenis_kendaraan }}
                                        </span>
                                    </td>
                                    <td>
                                        <i class="bi bi-clock me-1 text-muted"></i>
                                        {{ \Carbon\Carbon::parse($parkir->waktu_masuk)->format('d/m/Y H:i:s') }}
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" 
                                                    class="btn btn-warning btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editParkirModal"
                                                    data-id="{{ $parkir->id }}"
                                                    data-nomor-kartu="{{ $parkir->nomor_kartu }}"
                                                    data-plat-nomor="{{ $parkir->plat_nomor }}"
                                                    data-jenis-kendaraan="{{ $parkir->jenis_kendaraan }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <form action="{{ route('parkir.destroy', $parkir->id) }}" 
                                                  method="POST" 
                                                  class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-danger btn-sm btn-delete"
                                                        data-id="{{ $parkir->id }}"
                                                        onclick="return confirm('Yakin ingin menghapus?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bi bi-inbox display-6 text-muted"></i>
                                            <p class="text-muted mt-2">Tidak ada data</p>
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
</section>

<!-- Modal Tambah Data -->
<div class="modal fade" id="parkirModal" tabindex="-1" role="dialog" aria-labelledby="parkirModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="parkirModalLabel">Tambah Data Parkir</h5>
                <button type="button" class="close" id="closeModal" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('parkir.store') }}" method="POST" id="formParkir">
                @csrf
                <div class="modal-body">
                    <!-- QR Scanner Section -->
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

                    <!-- Form Fields -->
                    <div class="form-group mb-3">
                        <label for="nomor_kartu">Nomor Kartu</label>
                        <input type="text" 
                               name="nomor_kartu" 
                               id="nomor_kartu" 
                               class="form-control @error('nomor_kartu') is-invalid @enderror" 
                               value="{{ old('nomor_kartu') }}" 
                               required 
                               autocomplete="off" 
                               placeholder="Hasil scan QR code akan muncul di sini">
                        @error('nomor_kartu')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="plat_nomor">Nomor Plat</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-car-front"></i>
                            </span>
                            <input type="text" 
                                   name="plat_nomor" 
                                   id="plat_nomor" 
                                   class="form-control @error('plat_nomor') is-invalid @enderror" 
                                   value="{{ old('plat_nomor') }}" 
                                   required 
                                   placeholder="Masukkan nomor plat (contoh: B 1234 XYZ)"
                                   autocomplete="off">
                            <button type="button" class="btn btn-outline-secondary plate-edit-btn" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </div>
                        @error('plat_nomor')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                        <div class="plate-input-info">
                            <small>Nilai terdeteksi otomatis. Silakan edit jika diperlukan.</small>
                        </div>
                    </div>

                    <div class="form-group mt-3">
                        <label for="jenis_kendaraan">Jenis Kendaraan</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-car-front"></i>
                            </span>
                            <select name="jenis_kendaraan" 
                                    id="jenis_kendaraan" 
                                    class="form-control @error('jenis_kendaraan') is-invalid @enderror" 
                                    required>
                                <option value="" disabled selected>Pilih jenis kendaraan</option>
                                <option value="Sepeda Motor" {{ old('jenis_kendaraan') == 'Sepeda Motor' ? 'selected' : '' }}>Sepeda Motor</option>
                                <option value="Mobil" {{ old('jenis_kendaraan') == 'Mobil' ? 'selected' : '' }}>Mobil</option>
                                <option value="Bus" {{ old('jenis_kendaraan') == 'Bus' ? 'selected' : '' }}>Bus</option>
                            </select>
                        </div>
                        @error('jenis_kendaraan')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="text-center mt-3">
                        <p class="fw-medium text-muted">Atau gunakan fitur otomatis:</p>
                        <div class="d-flex justify-content-center gap-2">
                            <!-- Camera Capture UI -->
                            <div class="camera-container mb-3" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-info" id="camera-info" style="display: none;">Kamera aktif</span>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="switchCamera" title="Ganti Kamera">
                                        <i class="fas fa-sync"></i>
                                    </button>
                                </div>
                                <video id="camera-preview" style="width: 100%; max-height: 300px; object-fit: cover;" autoplay></video>
                                <canvas id="canvas-preview" style="display: none;"></canvas>
                                <div class="mt-2 d-flex justify-content-center gap-2">
                                    <button type="button" class="btn btn-primary btn-sm" id="takePicture">
                                        <i class="fas fa-camera"></i> Ambil Foto
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" id="cancelCamera">
                                        <i class="fas fa-times"></i> Tutup Kamera
                                    </button>
                                </div>
                                <div id="loading-indicator" style="display: none;" class="text-center mt-2">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Sedang mengenali plat nomor...</p>
                                </div>
                                <div class="captured-image mt-2" style="display: none;">
                                    <img id="captured-image" style="max-width: 100%; max-height: 200px;" />
                                </div>
                                <!-- Panduan visual untuk plat nomor -->
                                <div class="plate-guide-overlay">
                                    <div class="plate-frame"></div>
                                    <div class="plate-instruction">Posisikan plat nomor dalam kotak</div>
                                </div>
                            </div>
                            
                            <!-- Button to start camera -->
                            <button type="button" class="btn btn-info" id="startCamera">
                                <i class="fas fa-camera-retro"></i> Deteksi Plat Nomor
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-warning ms-2">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Data -->
<div class="modal fade" id="editParkirModal" tabindex="-1" role="dialog" aria-labelledby="editParkirModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editParkirModalLabel">Edit Data Parkir</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST" id="editParkirForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" name="nomor_kartu" id="edit_nomor_kartu">
                    <div class="form-group">
                        <label for="plat_nomor">Nomor Plat</label>
                        <input type="text" name="plat_nomor" id="edit_plat_nomor" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="jenis_kendaraan">Jenis Kendaraan</label>
                        <select name="jenis_kendaraan" id="edit_jenis_kendaraan" class="form-control" required>
                            <option value="Sepeda Motor">Sepeda Motor</option>
                            <option value="Mobil">Mobil</option>
                            <option value="Bus">Bus</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-warning">Perbarui</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<!-- Bootstrap JS (Opsional, jika diperlukan) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Add in head section -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

<!-- OCR Dependencies -->
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@4.0.3/dist/tesseract.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/browser-image-compression@2.0.0/dist/browser-image-compression.js"></script>

<script>
    $(document).ready(function () {
        // Menampilkan modal saat tombol Tambah diklik
        $('#btnTambah').click(function () {
            $('#parkirModal').modal('show');
        });

        // Menampilkan modal jika ada validasi error
        @if ($errors->any())
            $('#parkirModal').modal('show');
        @endif

        // Menutup modal saat tombol close diklik
        $('#closeModal, button[data-dismiss="modal"]').click(function() {
            $('#parkirModal').modal('hide');
        });

        // Function untuk scan QR code (placeholder)
        window.mulaiScanner = function() {
            alert('Fitur scan QR code belum tersedia');
        };

        // Function untuk OCR plat nomor (placeholder)
        window.mulaiOCR = function() {
            alert('Fitur scan Plat Nomor belum tersedia');
        };

        //function untuk OCR plat nomor
        window.mulaiOCR=function(){
            $('#startCamera').click();
        }

        // Form submission dengan Ajax untuk debugging
        $('#formParkir').submit(function(e) {
            console.log('Form submitted');
            console.log($(this).serialize());
            // Komentar baris di bawah ini jika ingin debugging form submission
            // e.preventDefault();
        });

        // Perbaikan handler untuk tombol edit
        $(document).on('click', 'button[data-bs-target="#editParkirModal"]', function () {
            const id = $(this).data('id');
            const platNomor = $(this).data('plat-nomor');
            const jenisKendaraan = $(this).data('jenis-kendaraan');
            const nomorKartu = $(this).data('nomor-kartu'); // Tambah ini

            // Set nilai ke form
            $('#edit_nomor_kartu').val(nomorKartu);
            $('#edit_plat_nomor').val(platNomor);
            $('#edit_jenis_kendaraan').val(jenisKendaraan);
            
            // Update action URL
            $('#editParkirForm').attr('action', `/parkir/${id}`);
            
            // Tampilkan modal
            $('#editParkirModal').modal('show');
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('#formParkir').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        location.reload();
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors || {};
                    let errorMessage = '';
                    
                    for (let field in errors) {
                        errorMessage += errors[field].join('\n') + '\n';
                    }
                    
                    alert(errorMessage || 'Terjadi kesalahan sistem');
                }
            });
        });
    });
</script>

<script>
    $('#editParkirForm').on('submit', function(e) {
        e.preventDefault();
        const id = $(this).attr('action').split('/').pop();
        
        $.ajax({
            url: `/parkir/${id}`,
            type: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#editParkirModal').modal('hide');
                    alert(response.message);
                    location.reload();
                }
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors || {};
                let errorMessage = '';
                
                for (let field in errors) {
                    errorMessage += errors[field].join('\n') + '\n';
                }
                
                alert(errorMessage || 'Terjadi kesalahan sistem');
            }
        });
    });
</script>

<script>
    $('.btn-delete').click(function(e) {
        e.preventDefault();
        
        if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            const id = $(this).data('id');
            
            $.ajax({
                url: `/parkir/${id}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        location.reload();
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors || {};
                    let errorMessage = '';
                    
                    for (let field in errors) {
                        errorMessage += errors[field].join('\n') + '\n';
                    }
                    
                    alert(errorMessage || 'Terjadi kesalahan sistem');
                }
            });
        }
    });
</script>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
let html5QrcodeScanner = null;

function stopScanner() {
    if (html5QrcodeScanner) {
        html5QrcodeScanner.clear();
        html5QrcodeScanner = null;
        $('#stopButton').hide();
        $('#startButton').show();
    }
}

$(document).ready(function() {
    // Start Scanner
    $('#startButton').click(function() {
        if (html5QrcodeScanner === null) {
            $('#stopButton').show();
            $(this).hide();
            
            html5QrcodeScanner = new Html5QrcodeScanner(
                "reader", 
                { 
                    fps: 10, 
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0
                }
            );
            
            html5QrcodeScanner.render((decodedText) => {
                // Cek status kartu melalui AJAX
                $.ajax({
                    url: '{{ route("parkir.check-card") }}',
                    type: 'POST',
                    data: {
                        nomor_kartu: decodedText,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.is_used) {
                            // Kartu sedang digunakan
                            Swal.fire({
                                icon: 'error',
                                title: 'Kartu Sedang Digunakan',
                                text: 'Kartu ini masih digunakan dan belum keluar parkir',
                                confirmButtonColor: '#dc3545'
                            });
                            stopScanner();
                        } else {
                            // Kartu valid dan bisa digunakan
                            $('#nomor_kartu').val(decodedText);
                            stopScanner();
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Nomor kartu berhasil di-scan',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan saat memverifikasi kartu',
                            confirmButtonColor: '#dc3545'
                        });
                        stopScanner();
                    }
    });

    // Stop Scanner
    $('#stopButton').click(function() {
        stopScanner();
    });

    // Reset scanner saat modal ditutup
    $('#parkirModal').on('hidden.bs.modal', function() {
        stopScanner();
    });

    // Validasi input manual
    $('#nomor_kartu').on('input', function() {
        let value = this.value;
        if (value.length > 0) {
            $.ajax({
                url: '{{ route("parkir.check-card") }}',
                type: 'POST',
                data: {
                    nomor_kartu: value,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.is_used) {
                        $('#nomor_kartu').addClass('is-invalid');
                        $('#nomor_kartu').next('.invalid-feedback').remove();
                        $('#nomor_kartu').after('<div class="invalid-feedback">Kartu ini masih digunakan dan belum keluar parkir</div>');
                    } else {
                        $('#nomor_kartu').removeClass('is-invalid');
                        $('#nomor_kartu').next('.invalid-feedback').remove();
                    }
                }
            });
        }
    });
});
</script>

<script>
// AI-based OCR System untuk Plat Nomor
$(document).ready(function() {
    console.log('Initializing AI-based OCR system...');
    
    // Global variables
    let videoStream = null;
    let ocrInProgress = false;
    let availableCameras = [];
    let selectedCameraId = null;
    
    // Event handlers
    $(document).on('click', '#startCamera', function() {
        console.log('Start camera button clicked');
        checkCamerasAndStart();
    });
    
    $(document).on('click', '#cancelCamera', function() {
        console.log('Cancel camera button clicked');
        stopCamera();
    });
    
    $(document).on('click', '#takePicture', function() {
        console.log('Take picture button clicked');
        takePicture();
    });
    
    $(document).on('click', '#switchCamera', function() {
        console.log('Switch camera button clicked');
        showCameraSelectionModal();
    });
    
    $('#parkirModal').on('hidden.bs.modal', function() {
        console.log('Modal closed, stopping camera');
        stopCamera();
    });
    
    // Check available cameras and start appropriate option
    function checkCamerasAndStart() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) {
            console.log('enumerateDevices() not supported, starting default camera');
            startCameraWithFallback();
            return;
        }
        
        navigator.mediaDevices.enumerateDevices()
            .then(devices => {
                availableCameras = devices.filter(device => device.kind === 'videoinput');
        // Periksa perangkat kamera yang tersedia
                console.log('Available cameras:', availableCameras);
                
                if (availableCameras.length === 0) {
                    // Tidak ada kamera yang tersedia
                    Swal.fire({
                        icon: 'error',
                        title: 'Tidak Ada Kamera',
                        text: 'Tidak ditemukan kamera pada perangkat Anda',
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }
                
                if (availableCameras.length === 1) {
                    // Hanya ada satu kamera, langsung gunakan
                    console.log('Only one camera found, using it directly');
                    selectedCameraId = availableCameras[0].deviceId;
                    startCameraWithFallback(selectedCameraId);
                } else {
                    // Ada beberapa kamera, tampilkan pilihan
                    console.log('Multiple cameras found, showing selection modal');
                    showCameraSelectionModal();
                }
            })
            .then(devices => {
                // Filter hanya perangkat video (kamera)
                availableCameras = devices.filter(device => device.kind === 'videoinput');
                console.log('Available cameras:', availableCameras);
                
                if (availableCameras.length === 0) {
                    // Tidak ada kamera yang tersedia
                    Swal.fire({
                        icon: 'error',
                        title: 'Tidak Ada Kamera',
                        text: 'Tidak ditemukan kamera pada perangkat Anda',
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }
                
                if (availableCameras.length === 1) {
                    // Hanya ada satu kamera, langsung gunakan
                    console.log('Only one camera found, using it directly');
                    selectedCameraId = availableCameras[0].deviceId;
                    startCameraWithFallback(selectedCameraId);
                } else {
                    // Ada beberapa kamera, tampilkan pilihan
                    console.log('Multiple cameras found, showing selection modal');
                    showCameraSelectionModal();
                }
            })
            .catch(err => {
                console.error('Error enumerating devices:', err);
                // Fallback ke kamera default jika gagal mendapatkan daftar
               // console.log('Falling back to default camera');
                startCameraWithFallback();
            });
    }
    
    // Fungsi untuk menampilkan modal pilihan kamera
    function showCameraSelectionModal() {
        // Jika tidak ada daftar kamera, dapatkan dulu
        if (availableCameras.length === 0) {
            navigator.mediaDevices.enumerateDevices()
                .then(devices => {
                    availableCameras = devices.filter(device => device.kind === 'videoinput');
                    showCameraOptions();
                })
                .catch(err => {
                    console.error('Failed to enumerate devices:', err);
                    startCameraWithFallback(); // Fallback ke kamera default
                });
        } else {
            showCameraOptions();
        }
        
        function showCameraOptions() {
            if (availableCameras.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Tidak Ada Kamera',
                    text: 'Tidak ditemukan kamera pada perangkat Anda',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }
            
            let cameraOptions = '';
            availableCameras.forEach((camera, index) => {
                const label = camera.label || `Kamera ${index + 1}`;
                const isSelected = camera.deviceId === selectedCameraId;
                cameraOptions += `
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="cameraSelect" 
                            id="camera${index}" value="${camera.deviceId}" ${isSelected || index === 0 ? 'checked' : ''}>
                        <label class="form-check-label" for="camera${index}">
                            ${label} ${index === 0 ? '(Default)' : ''}
                        </label>
                    </div>
                `;
            });
            
            // Tampilkan dialog pilihan kamera menggunakan SweetAlert2
            Swal.fire({
                title: 'Pilih Kamera',
                html: `
                    <div class="text-start">
                        ${cameraOptions}
                    </div>
                `,
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Gunakan Kamera',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Dapatkan ID kamera yang dipilih
                    selectedCameraId = document.querySelector('input[name="cameraSelect"]:checked').value;
                    console.log('Selected camera ID:', selectedCameraId);
                    
                    // Stop kamera yang aktif jika ada
                    if (videoStream) {
                        stopCamera();
                    }
                    
                    // Mulai kamera dengan ID terpilih
                    startCameraWithFallback(selectedCameraId);
                }
            });
        }
    }
    
    // Improved camera initialization with fallback options and camera selection
    function startCameraWithFallback(cameraId = null) {
        console.log('Starting camera with ID:', cameraId || 'default');
        const video = document.getElementById('camera-preview');
        
        if (!video) {
            console.error('Camera preview element not found!');
            return;
        }
        
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Browser Anda tidak mendukung akses kamera',
                confirmButtonColor: '#dc3545'
            });
            return;
        }
        
        // Ensure any existing stream is stopped
        if (videoStream) {
            stopCamera();
        }
        
        // Constraint dasar
        const baseConstraints = {
            audio: false,
            video: {}
        };
        
        // Tambahkan deviceId jika ada
        if (cameraId) {
            baseConstraints.video.deviceId = { exact: cameraId };
        } else {
            // Jika tidak ada ID kamera spesifik, preferensi ke kamera belakang untuk mobile
            if (isMobileDevice()) {
                baseConstraints.video.facingMode = { ideal: 'environment' };
            }
        }
        
        // First attempt - Try with high quality settings
        const highQualityConstraints = { 
            ...baseConstraints,
            video: {
                ...baseConstraints.video,
                width: { ideal: 1280 },
                height: { ideal: 720 }
            }
        };
        
        // Second attempt - Basic settings
        const basicConstraints = { ...baseConstraints };
        
        // Third attempt - Minimal settings
        const minimalConstraints = {
            ...baseConstraints,
            video: {
                ...baseConstraints.video,
                width: { ideal: 640 },
                height: { ideal: 480 }
            }
        };
        
        // Try with progressive fallbacks
        tryGetUserMedia(highQualityConstraints)
        .then(setupStream)
        .catch(err => {
            console.log('First camera attempt failed:', err);
            return tryGetUserMedia(basicConstraints);
        })
        .then(setupStream)
        .catch(err => {
            console.log('Second camera attempt failed:', err);
            return tryGetUserMedia(minimalConstraints);
        })
        .then(setupStream)
        .catch(err => {
            // All attempts failed
            console.error('All camera access attempts failed:', err);
            handleCameraError(err);
        });
        
        // Helper for getUserMedia
        function tryGetUserMedia(constraints) {
            return navigator.mediaDevices.getUserMedia(constraints);
        }
        
        // Setup video stream
        function setupStream(stream) {
            if (!stream) return Promise.reject('No stream available');
            
            console.log('Camera access granted, setting up video');
            videoStream = stream;
            video.srcObject = stream;
            
            // Dapatkan info kamera yang sedang digunakan
            const videoTrack = stream.getVideoTracks()[0];
            if (videoTrack) {
                const cameraLabel = videoTrack.label || 'Kamera aktif';
                $('#camera-info').text(cameraLabel).show();
                console.log('Using camera:', cameraLabel);
            }
            
            return new Promise((resolve, reject) => {
                video.onloadedmetadata = () => {
                    video.play()
                    .then(() => {
                        console.log('Video playing successfully');
                        $('.camera-container').show();
                        $('#startCamera').hide();
                        resolve();
                    })
                    .catch(err => {
                        console.error('Error playing video:', err);
                        reject(err);
                    });
                };
                
                video.onerror = (err) => {
                    console.error('Video error:', err);
                    reject(err);
                };
            });
        }
    }
    
    // Handle camera error with detailed feedback
    function handleCameraError(err) {
        console.error('Camera error:', err);
        
        if (err.name === 'NotReadableError' || err.name === 'TrackStartError') {
            Swal.fire({
                icon: 'error',
                title: 'Kamera Sedang Digunakan',
                html: `
                    <p>Kamera sedang digunakan aplikasi lain atau mengalami masalah.</p>
                    <p>Silakan coba:</p>
                    <ul class="text-start">
                        <li>Tutup aplikasi lain yang menggunakan kamera (Zoom, Meet, dll)</li>
                        <li>Refresh halaman dan coba lagi</li>
                        <li>Pilih kamera lain jika tersedia</li>
                        <li>Restart browser Anda</li>
                    </ul>
                `,
                confirmButtonColor: '#dc3545'
            });
        } else if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
            Swal.fire({
                icon: 'error',
                title: 'Izin Ditolak',
                text: 'Anda perlu memberikan izin akses kamera untuk menggunakan fitur ini',
                confirmButtonColor: '#dc3545'
            });
        } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
            Swal.fire({
                icon: 'error',
                title: 'Kamera Tidak Ditemukan',
                text: 'Tidak ada perangkat kamera yang terdeteksi pada perangkat Anda',
                confirmButtonColor: '#dc3545'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Tidak dapat mengakses kamera: ' + (err.message || 'Unknown error'),
                confirmButtonColor: '#dc3545'
            });
        }
    }
    
    // Function to stop camera - improved
    function stopCamera() {
        console.log('Stopping camera...');
        if (videoStream) {
            videoStream.getTracks().forEach(function(track) {
                track.stop();
            });
            videoStream = null;
            
            // Clear video source
            const video = document.getElementById('camera-preview');
            if (video) {
                video.srcObject = null;
            }
        }
        $('.camera-container').hide();
        $('#camera-info').hide();
        $('#startCamera').show();
        $('.captured-image').hide();
    }
    
    // Fungsi untuk mengambil gambar dan mendeteksi plat (Gunakan raw text)
    function takePicture() {
        if (ocrInProgress) return;
        
        console.log('Taking picture for AI processing...');
        const video = document.getElementById('camera-preview');
        const canvas = document.getElementById('canvas-preview');
        const capturedImage = document.getElementById('captured-image');
        
        if (!video || !canvas || !capturedImage) return;
        
        ocrInProgress = true;
        $('#loading-indicator').show();
        
        // Set canvas dimensions
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        // Draw video to canvas
        const context = canvas.getContext('2d');
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        // Display captured image
        capturedImage.src = canvas.toDataURL('image/jpeg', 0.9);
        $('.captured-image').show();
        
        // Get image data for API
        const imageData = canvas.toDataURL('image/jpeg', 0.9);
        
        // Console log image data length for debugging
        console.log("Image data length:", imageData.length);
        if (imageData.length < 1000) {
            console.error("Image data too small, might not be valid");
            $('#loading-indicator').hide();
            ocrInProgress = false;
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Gambar tidak valid. Silakan coba lagi.',
                confirmButtonColor: '#dc3545'
            });
            return;
        }
        
        // Process with Tesseract or API
        processWithOCR(imageData)
            .then(result => {
                // Isi form dengan hasil OCR
                fillPlateInputs(result);
                
                // Highlight input fields untuk menunjukkan pengguna bisa mengedit
                highlightInputsForEditing();
                
                // Fokus ke input region untuk memudahkan pengeditan
                $('.plate-region').focus();
            });
    }
    
    function highlightInputsForEditing() {
        $('.plate-region, .plate-number, .plate-suffix').addClass('highlight-for-edit');
        
        setTimeout(() => {
            $('.plate-region, .plate-number, .plate-suffix').removeClass('highlight-for-edit');
        }, 2000);
    }
    
    console.log('OCR system with camera selection initialized');
});

function improveOCRResult(text) {
    // Convert to uppercase and trim spaces
    let plate = text.toUpperCase().trim();
    
    // Bersihkan spasi berlebih (contoh: "B  2374  KH" → "B 2374 KH")
    plate = plate.replace(/\s+/g, ' ');
    
    // Koreksi kesalahan umum OCR
    const commonCorrections = {
        '0': 'O',  // angka 0 sering terdeteksi sebagai huruf O  
        'Q': 'O',  // Q sering terdeteksi sebagai O
        'I': '1',  // I sering terdeteksi sebagai 1 pada bagian angka
        'S': '5',  // huruf S kadang terdeteksi sebagai angka 5
        'Z': '2',  // Z kadang terdeteksi sebagai 2
        'G': '6',  // G kadang terdeteksi sebagai 6
        'D': '0',  // D kadang terdeteksi sebagai 0
        'U': 'V',  // U kadang terdeteksi sebagai V
    };
    
    // Deteksi jika ini adalah pola plat nomor (huruf-angka-huruf)
    const platePattern = /([A-Z]{1,2})\s*([0-9]{1,4})\s*([A-Z]{1,3})/;
    const match = plate.match(platePattern);
    
    if (match) {
        // Jika sesuai pola plat, koreksi setiap bagian dengan tepat
        let region = match[1];     // Huruf daerah (contoh: B, D, AB)
        let numbers = match[2];    // Bagian angka (contoh: 1234)
        let letters = match[3];    // Bagian huruf (contoh: ABC)
        
        // Pada bagian angka, ganti huruf yang mungkin salah baca
        numbers = numbers.replace(/O/g, '0');
        numbers = numbers.replace(/I/g, '1');
        numbers = numbers.replace(/S/g, '5');
        numbers = numbers.replace(/Z/g, '2');
        
        // Pada bagian huruf, ganti angka yang mungkin salah baca
        letters = letters.replace(/0/g, 'O');
        letters = letters.replace(/1/g, 'I');
        letters = letters.replace(/5/g, 'S');
        letters = letters.replace(/2/g, 'Z');
        
        // Buat hasil yang terkoreksi dengan format standar
        return `${region} ${numbers} ${letters}`;
    }
    
    // Jika tidak sesuai pola, kembalikan teks asli yang sudah dibersihkan
    return plate;
}
</script>

<script>
// Script untuk highlight dan saran
$(document).ready(function() {
    // Saat input berubah, periksa format
    $('#plat_nomor').on('input', function() {
        let value = $(this).val().toUpperCase();
        $(this).val(value);
        
        // Periksa pola standar plat nomor Indonesia
        const platePattern = /^([A-Z]{1,2})\s*([0-9]{1,4})\s*([A-Z]{1,3})$/;
        if (!platePattern.test(value)) {
            $(this).addClass('is-invalid');
            
            // Coba sarankan format yang benar
            const match = value.match(/([A-Z]{1,2})[^A-Z0-9]*([0-9]{1,4})[^A-Z0-9]*([A-Z]{1,3})/i);
            if (match) {
                const suggestion = `${match[1]} ${match[2]} ${match[3]}`.toUpperCase();
                $('#plate-suggestion-text').text(suggestion);
                $('#plate-suggestion').removeClass('d-none');
                
                $('#use-suggestion').off('click').on('click', function(e) {
                    e.preventDefault();
                    $('#plat_nomor').val(suggestion).removeClass('is-invalid');
                    $('#plate-suggestion').addClass('d-none');
                });
            } else {
                $('#plate-suggestion').addClass('d-none');
            }
        } else {
            $(this).removeClass('is-invalid');
            $('#plate-suggestion').addClass('d-none');
        }
    });
    
    // Tombol edit: fokuskan input dan seleksi isinya
    $('.plate-edit-btn').click(function() {
        $('#plat_nomor').focus().select();
    });
});
</script>

<script>
async function recognizeWithMultipleMethods(imageData) {
    // Metode 1: Tesseract with default settings
    const result1 = await Tesseract.recognize(
        imageData,
        'eng',
        { tessedit_char_whitelist: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 ' }
    );
    
    // Metode 2: Tesseract with contrast enhancement
    const enhancedCanvas = enhancePlateImage(canvas.cloneNode(true));
    const result2 = await Tesseract.recognize(
        enhancedCanvas.toDataURL('image/jpeg'),
        'eng',
        { tessedit_char_whitelist: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 ' }
    );
    
    // Metode 3: Server-side OCR using EasyOCR
    const apiResult = await $.ajax({
        url: '/api/detect-plate',
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            image: imageData
        }
    });
    
    console.log('Results from multiple methods:');
    console.log('Tesseract basic:', result1.data.text);
    console.log('Tesseract enhanced:', result2.data.text);
    console.log('EasyOCR:', apiResult.raw_text);
    
    // Voting mechanism - parsePlateNumber tries each result
    // and returns the most likely valid plate number
    return findBestPlateNumber([
        result1.data.text,
        result2.data.text,
        apiResult.raw_text
    ]);
}

function findBestPlateNumber(results) {
    // Filter valid-looking plates
    const platPattern = /([A-Z]{1,2})\s*(\d{1,4})\s*([A-Z]{1,3})/;
    
    // Find valid matches
    const validResults = results
        .map(text => text.trim().toUpperCase())
        .filter(text => platPattern.test(text));
    
    // Return first valid match or the first result
    return validResults.length > 0 ? validResults[0] : results[0].trim().toUpperCase();
}
</script>

<script>
// Tambahkan setelah fungsi enhancePlateImage
// dan sebelum console.log('OCR system with camera selection initialized')

// Fungsi untuk memproses gambar dengan OCR
// Perbaiki fungsi processWithOCR
async function processWithOCR(imageData) {
    return new Promise((resolve, reject) => {
        // Gunakan canvas untuk preprocessing
        const tempCanvas = document.createElement('canvas');
        const tempCtx = tempCanvas.getContext('2d');
        const img = new Image();
        img.onload = function() {
            // Atur ukuran canvas
            tempCanvas.width = img.width;
            tempCanvas.height = img.height;
            
            // Gambar ke canvas
            tempCtx.drawImage(img, 0, 0);
            
            // Terapkan peningkatan gambar
            const enhancedCanvas = window.enhancePlateImage(tempCanvas);
            
            // Process with Tesseract
            Tesseract.recognize(
                enhancedCanvas.toDataURL('image/jpeg'),
                'eng',
                { 
                    logger: m => console.log('OCR progress:', m.status || m),
                    tessedit_char_whitelist: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 ',
                    tessedit_pageseg_mode: '7',  // Treat image as single text line
                    preserve_interword_spaces: '0',
                    tessjs_create_hocr: '0',
                    tessjs_create_tsv: '0'
                }
            )
            .then(result => {
                const rawText = result.data.text.trim().toUpperCase();
                console.log('OCR raw result:', rawText);
                window.ocrInProgress = false; // Use window to ensure global scope
                $('#loading-indicator').hide();
                
                // Langsung isi field tunggal (bukan segmented)
                $('#plat_nomor').val(rawText);
                $('#loading-indicator').hide();
                ocrInProgress = false;
                
                // Highlight efek pada field
                $('#plat_nomor').addClass('highlight-for-edit');
                setTimeout(() => {
                    $('#plat_nomor').removeClass('highlight-for-edit');
                }, 2000);
                
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Plat nomor berhasil terdeteksi',
                    timer: 1500,
                    showConfirmButton: false
                });
                
                stopCamera();
                
                resolve(rawText);
            })
            .catch(error => {
                console.error('OCR error:', error);
                $('#loading-indicator').hide();
                window.ocrInProgress = false;
                
                Swal.fire({
                    icon: 'error',
                    title: 'OCR Error',
                    text: 'Gagal mengenali plat nomor. Silakan coba lagi.',
                    confirmButtonColor: '#dc3545'
                });
                
                reject(error);
            });
        };
        
        img.onerror = function(error) {
            console.error('Error loading image:', error);
            $('#loading-indicator').hide();
            window.ocrInProgress = false;
            reject(error);
        };
        
        img.src = imageData;
    });
}
</script>


<script>
$(document).ready(function() {
    // Update hidden input saat nilai berubah
    $('.plate-region, .plate-number, .plate-suffix').on('input', function() {
        updatePlatNomorInput();
    });
    
    // Format nilai saat diketik
    $('.plate-region, .plate-suffix').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });
    
    // Auto focus ke input berikutnya
    $('.plate-region').on('keyup', function() {
        if ($(this).val().length >= parseInt($(this).attr('maxlength'))) {
            $('.plate-number').focus();
        }
    });
    
    $('.plate-number').on('keyup', function() {
        if ($(this).val().length >= parseInt($(this).attr('maxlength'))) {
            $('.plate-suffix').focus();
        }
    });
});
</script>

@endsection

@push('styles')
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
</style>

<style>
#qr-reader-section {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-top: 10px;
}

#reader {
    width: 100%;
    max-width: 400px;
    margin: 0 auto;
}

#reader video {
    border-radius: 8px;
}

.input-group .btn {
    z-index: 0;
}
</style>

<style>
    /* Styling untuk kamera OCR */
    .camera-container {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        position: relative;
    }
    
    #camera-preview {
        border-radius: 8px;
        background-color: #000;
        width: 100%;
        max-height: 300px;
        object-fit: cover;
    }
    
    .captured-image img {
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        border: 2px solid #fff;
    }
    
    #loading-indicator {
        background-color: rgba(255,255,255,0.8);
        padding: 10px;
        border-radius: 8px;
    }

    .plate-guide-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 5;
    }

    .plate-frame {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        width: 80%;
        height: 30%;
        border: 3px dashed rgba(255, 255, 0, 0.8);
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0,0,0,0.2);
    }

    .plate-instruction {
        position: absolute;
        top: 15%;
        left: 0;
        right: 0;
        text-align: center;
        color: white;
        background: rgba(0,0,0,0.5);
        padding: 5px;
        font-weight: bold;
        border-radius: 5px;
        margin: 0 auto;
        width: fit-content;
    }
</style>

<style>
/* Styling untuk dropdown jenis kendaraan */
#jenis_kendaraan {
    height: calc(1.5em + 0.75rem + 2px);
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}

.input-group-text i {
    width: 16px;
    text-align: center;
}

/* Styling untuk highlight pada select */
select.highlight-for-edit {
    background-color: rgba(255, 193, 7, 0.2) !important;
    transition: background-color 0.5s ease;
}
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Sidebar toggle
        $('[data-lte-toggle="sidebar"]').on('click', function(e) {
            e.preventDefault();
            $('body').toggleClass('sidebar-collapse');
        });

        // Enhanced search functionality
        $('#search').on('input', function() {
            let searchText = $(this).val().toLowerCase();
            let noResults = true;
            
            $('#dataTable tbody tr').each(function() {
                let rowText = $(this).text().toLowerCase();
                if (rowText.includes(searchText)) {
                    $(this).show();
                    noResults = false;
                } else {
                    $(this).hide();
                }
            });

            // Show no results message
            if (noResults) {
                if ($('#noResults').length === 0) {
                    $('#dataTable tbody').append(`
                        <tr id="noResults">
                            <td colspan="5" class="text-center py-3">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="bi bi-search text-muted"></i>
                                    <p class="text-muted mt-2">Tidak ada data yang cocok</p>
                                </div>
                            </td>
                        </tr>
                    `);
                }
            } else {
                $('#noResults').remove();
            }
        });

        // Auto-hide alerts
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 3000);

        // Initialize tooltips
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el));
    });
</script>

<script>
    $(document).ready(function() {
        $('#formParkir').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        location.reload();
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors || {};
                    let errorMessage = '';
                    
                    for (let field in errors) {
                        errorMessage += errors[field].join('\n') + '\n';
                    }
                    
                    alert(errorMessage || 'Terjadi kesalahan sistem');
                }
            });
        });
    });
</script>

<script>
$(document).ready(function() {
    // Variabel untuk menyimpan timeout
    let searchTimeout;

    // Handle input pencarian dengan debounce
    $('#search').on('input', function() {
        clearTimeout(searchTimeout);
        
        searchTimeout = setTimeout(() => {
            $('#searchForm').submit();
        }, 500); // Tunggu 500ms setelah user selesai mengetik
    });

    // Handle clear search
    $('#clearSearch').on('click', function() {
        $('#search').val('');
        window.location.href = "{{ route('parkir.index') }}";
    });

    // Highlight hasil pencarian
    function highlightSearch() {
        let searchText = "{{ request('search') }}";
        if (searchText) {
            let searchRegex = new RegExp(searchText, 'gi');
            $('td').each(function() {
                let text = $(this).text();
                if (text.match(searchRegex)) {
                    $(this).html(text.replace(searchRegex, match => 
                        `<span class="highlight">${match}</span>`
                    ));
                }
            });
        }
    }

    // Panggil fungsi highlight saat halaman dimuat
    highlightSearch();
});
</script>

<script>
let html5QrcodeScanner = null;

function stopScanner() {
    if (html5QrcodeScanner) {
        html5QrcodeScanner.clear();
        html5QrcodeScanner = null;
        $('#stopButton').hide();
        $('#startButton').show();
    }
}

$(document).ready(function() {
    // Start Scanner
     $('#startButton').click(function() {
        if (html5QrcodeScanner === null) {
            $('#stopButton').show();
            $(this).hide();
            
            html5QrcodeScanner = new Html5QrcodeScanner(
                "reader", 
                { 
                    fps: 10, 
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0
                }
            );
            
            html5QrcodeScanner.render((decodedText) => {
                // Cek status kartu melalui AJAX
                $.ajax({
                    url: '/parkir/check-card',
                    type: 'POST',
                    data: {
                        nomor_kartu: decodedText,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.is_used) {
                            // Kartu sedang digunakan
                            Swal.fire({
                                icon: 'error',
                                title: 'Kartu Sedang Digunakan',
                                text: 'Kartu ini masih digunakan dan belum keluar parkir',
                                confirmButtonColor: '#dc3545'
                            });
                        } else {
                            // Kartu valid dan bisa digunakan
                            $('#nomor_kartu').val(decodedText);
                            stopScanner();
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Nomor kartu berhasil di-scan',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan saat memverifikasi kartu',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                });
            });
        }
    });

    // Stop Scanner
    $('#stopButton').click(function() {
        stopScanner();
    });

    // Reset scanner saat modal ditutup
    $('#parkirModal').on('hidden.bs.modal', function() {
        stopScanner();
    });

    // Validasi input manual
    $('#nomor_kartu').on('input', function() {
        let value = this.value;
        // Validasi saat input manual
        if (value.length > 0) {
            $.ajax({
                url: '/parkir/check-card',
                type: 'POST',
                data: {
                    nomor_kartu: value,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.is_used) {
                        $('#nomor_kartu').addClass('is-invalid');
                        $('#nomor_kartu').next('.invalid-feedback').remove();
                        $('#nomor_kartu').after('<div class="invalid-feedback">Kartu ini masih digunakan dan belum keluar parkir</div>');
                    } else {
                        $('#nomor_kartu').removeClass('is-invalid');
                        $('#nomor_kartu').next('.invalid-feedback').remove();
                    }
                }
            });
        }
    });
});
</script>

<!-- Tambahkan script ini -->
<script>
// OCR System Implementation - Perbaikan Error NotReadableError
$(document).ready(function() {
    console.log('Initializing OCR system with fallback options...');
    
    // Global variables
    // let videoStream = null;
    // let ocrInProgress = false;
    window.videoStream = null; // Use window to ensure global scope
    window.ocrInProgress = false; // Use window to ensure global scope
    
    // Event handlers
    $(document).on('click', '#startCamera', function() {
        console.log('Start camera button clicked');
        startCameraWithFallback();
    });
    
    $(document).on('click', '#cancelCamera', function() {
        console.log('Cancel camera button clicked');
        stopCamera();
    });
    
    $(document).on('click', '#takePicture', function() {
        console.log('Take picture button clicked');
        takePicture();
    });
    
    // Reset on modal close
    $('#parkirModal').on('hidden.bs.modal', function() {
        console.log('Modal closed, stopping camera');
        stopCamera();
    });
    
    // Improved camera initialization with fallback options
    function startCameraWithFallback() {
        console.log('Starting camera with fallback strategy...');
        const video = document.getElementById('camera-preview');
        
        if (!video) {
            console.error('Camera preview element not found!');
            return;
        }
        
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Browser Anda tidak mendukung akses kamera',
                confirmButtonColor: '#dc3545'
            });
            return;
        }
        
        // Ensure any existing stream is stopped
        if (videoStream) {
            stopCamera();
        }
        
        // First attempt - Try with ideal settings
        tryGetUserMedia({
            video: {
                facingMode: 'environment',
                width: { ideal: 1280 },
                height: { ideal: 720 }
            },
            audio: false
        })
        .then(setupStream)
        .catch(err => {
            console.log('First camera attempt failed:', err);
            
            // Second attempt - Try with basic settings
            return tryGetUserMedia({
                video: true,
                audio: false
            });
        })
        .then(setupStream)
        .catch(err => {
            console.log('Second camera attempt failed:', err);
            
            // Third attempt - Try with minimal settings
            return tryGetUserMedia({
                video: {
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                },
                audio: false
            });
        })
        .then(setupStream)
        .catch(err => {
            // All attempts failed
            console.error('All camera access attempts failed:', err);
            
            // Handle specific error types
            if (err.name === 'NotReadableError' || err.name === 'TrackStartError') {
                Swal.fire({
                    icon: 'error',
                    title: 'Kamera Sedang Digunakan',
                    html: `
                        <p>Kamera sedang digunakan aplikasi lain atau mengalami masalah.</p>
                        <p>Silakan coba:</p>
                        <ul class="text-start">
                            <li>Tutup aplikasi lain yang menggunakan kamera (Zoom, Meet, dll)</li>
                            <li>Refresh halaman dan coba lagi</li>
                            <li>Restart browser Anda</li>
                        </ul>
                    `,
                    confirmButtonColor: '#dc3545'
                });
            } else if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                Swal.fire({
                    icon: 'error',
                    title: 'Izin Ditolak',
                    text: 'Anda perlu memberikan izin akses kamera untuk menggunakan fitur ini',
                    confirmButtonColor: '#dc3545'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Tidak dapat mengakses kamera: ' + err.message,
                    confirmButtonColor: '#dc3545'
                });
            }
        });
        
        // Helper for getUserMedia
        function tryGetUserMedia(constraints) {
            return navigator.mediaDevices.getUserMedia(constraints);
        }
        
        // Setup video stream
        function setupStream(stream) {
            if (!stream) return Promise.reject('No stream available');
            
            console.log('Camera access granted, setting up video');
            videoStream = stream;
            video.srcObject = stream;
            
            return new Promise((resolve, reject) => {
                video.onloadedmetadata = () => {
                    video.play()
                    .then(() => {
                        console.log('Video playing successfully');
                        $('.camera-container').show();
                        $('#startCamera').hide();
                        resolve();
                    })
                    .catch(err => {
                        console.error('Error playing video:', err);
                        reject(err);
                    });
                };
                
                video.onerror = (err) => {
                    console.error('Video error:', err);
                    reject(err);
                };
            });
        }
    }
    
    // Function to stop camera - improved
    function stopCamera() {
        console.log('Stopping camera...');
        if (videoStream) {
            videoStream.getTracks().forEach(function(track) {
                track.stop();
            });
            videoStream = null;
            
            // Clear video source
            const video = document.getElementById('camera-preview');
            if (video) {
                video.srcObject = null;
            }
        }
        $('.camera-container').hide();
        $('#startCamera').show();
        $('.captured-image').hide();
    }
    
    // Take picture and process
    function takePicture() {
        if (ocrInProgress) return;
        
        console.log('Taking picture...');
        const video = document.getElementById('camera-preview');
        const canvas = document.getElementById('canvas-preview');
        const capturedImage = document.getElementById('captured-image');
        
        if (!video || !canvas || !capturedImage) {
            console.error('Required elements not found');
            return;
        }
        
        ocrInProgress = true;
        $('#loading-indicator').show();
        
        // Set canvas dimensions
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        // Draw video to canvas
        const context = canvas.getContext('2d');
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        // Display captured image
        capturedImage.src = canvas.toDataURL('image/jpeg', 0.9);
        $('.captured-image').show();
        
        // Preprocess the image for better OCR results
        const processedCanvas = enhancePlateImage(canvas);
        
        // Process with Tesseract
        Tesseract.recognize(
            processedCanvas.toDataURL('image/jpeg'),
            'eng',
            { 
                logger: m => console.log('OCR progress:', m.status || m),
                tessedit_char_whitelist: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 ',
                tessedit_pageseg_mode: '7',  // Treat image as single text line
                preserve_interword_spaces: '0',
                tessjs_create_hocr: '0',
                tessjs_create_tsv: '0'
            }
        )
        .then(result => {
            const rawText = result.data.text.trim();
            console.log('OCR raw result:', rawText);
            
            // Terapkan koreksi ringan
            const improvedText = improveOCRResult(rawText);
            console.log('Improved OCR result:', improvedText);
            
            // Langsung isi field tunggal (bukan segmented)
            $('#plat_nomor').val(improvedText);
            $('#loading-indicator').hide();
            ocrInProgress = false;
            
            // Highlight efek pada field
            $('#plat_nomor').addClass('highlight-for-edit');
            setTimeout(() => {
                $('#plat_nomor').removeClass('highlight-for-edit');
            }, 2000);
            
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Plat nomor berhasil terdeteksi',
                timer: 1500,
                showConfirmButton: false
            });
            
            stopCamera();
        })
        .catch(error => {
            console.error('OCR error:', error);
            $('#loading-indicator').hide();
            ocrInProgress = false;
            
            Swal.fire({
                icon: 'error',
                title: 'OCR Error',
                text: 'Gagal mengenali plat nomor. Silakan coba lagi.',
                confirmButtonColor: '#dc3545'
            });
        });
    }
    
    // Enhance image for better OCR results
    window.enhancePlateImage = function(canvas) {
        const ctx = canvas.getContext('2d');
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const data = imageData.data;
        
        // Tingkatkan kontras
        const contrast = 1.5; // Nilai tinggi = kontras lebih tinggi
        const factor = (259 * (contrast + 255)) / (255 * (259 - contrast));
        
        for (let i = 0; i < data.length; i += 4) {
            // Konversi ke grayscale
            const gray = 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
            
            // Tingkatkan kontras
            data[i] = factor * (gray - 128) + 128;     // R
            data[i + 1] = factor * (gray - 128) + 128; // G
            data[i + 2] = factor * (gray - 128) + 128; // B
            
            // Binarization (threshold)
            const threshold = 150;
            if (data[i] > threshold) {
                data[i] = data[i+1] = data[i+2] = 255; // Putih
            } else {
                data[i] = data[i+1] = data[i+2] = 0;   // Hitam
            }
        }
        
        ctx.putImageData(imageData, 0, 0);
        return canvas;
    };
    
    console.log('OCR system initialized with improved error handling');
});
</script>
@endpush

@push('styles')
<style>
    /* Highlight style untuk hasil pencarian */
    .highlight {
        background-color: #fff3cd;
        padding: 2px;
        border-radius: 3px;
    }

    /* Loading indicator style */
    .search-loading {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        display: none;
    }

    /* Clear button style */
    #clearSearch {
        border: none;
        background: transparent;
        padding: 0.375rem 0.75rem;
    }

    #clearSearch:hover {
        color: #dc3545;
    }

    /* Improve input group appearance */
    .input-group {
        border-radius: 0.375rem;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.04);
    }

    .input-group-text {
        border: none;
        background-color: #f8f9fa;
    }

    .form-control {
        border: none;
        box-shadow: none;
    }

    .form-control:focus {
        box-shadow: none;
        background-color: #fff;
    }

    .highlight-for-edit {
    background-color: rgba(255, 193, 7, 0.2) !important;
    transition: background-color 0.5s ease;
}
</style>
@endpush