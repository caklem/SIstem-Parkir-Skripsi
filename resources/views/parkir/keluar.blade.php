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
                                       placeholder="Cari nomor kartu, plat nomor..." 
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
                                            <td>{{ $item->plat_nomor }}</td>
                                            <td>{{ $item->jenis_kendaraan }}</td>
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
                        <!-- Tab Pilih Kendaraan (Existing) -->
                        <div class="tab-pane fade show active" id="pilihKendaraan" role="tabpanel" aria-labelledby="pilih-tab">
                            <div class="form-group mb-3">
                                <label for="searchKendaraan">Pilih Kendaraan</label>
                                <select name="searchKendaraan" id="searchKendaraan" class="form-control" required>
                                    <option value="" selected disabled>Pilih Kendaraan dari Parkir Masuk</option>
                                    @foreach($parkirs as $parkir)
                                        <option value="{{ $parkir->id }}"
                                            data-nomor-kartu="{{ $parkir->nomor_kartu }}"
                                            data-plat-nomor="{{ $parkir->plat_nomor }}"
                                            data-jenis-kendaraan="{{ $parkir->jenis_kendaraan }}"
                                            data-waktu-masuk="{{ $parkir->waktu_masuk }}">
                                            {{ $parkir->nomor_kartu }} - {{ $parkir->plat_nomor }}
                                        </option>
                                    @endforeach
                                </select>
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
                            <div class="card-header bg-warning text-white">
                                Detail Kendaraan
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
</style>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

<script>
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

$(document).ready(function() {
    let html5QrcodeScanner = null;
    let selectedKendaraanId = null;
    
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
        $.ajax({
            url: '{{ route("parkir.proses-keluar") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: id
            },
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
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Kendaraan berhasil diproses keluar',
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
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan sistem',
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
        selectedKendaraanId = null;
        $('#searchKendaraan').val('').trigger('change');
        $('#hasilPencarian').addClass('d-none');
    });

    // Handle tab change
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        console.log('Tab changed:', e.target.id);
        stopScanner();
    });

    // Existing code for select dropdown
    $('#searchKendaraan').on('change', function() {
        const selected = $(this).find(':selected');
        if (selected.val()) {
            selectedKendaraanId = selected.val();
            $('#noKartu').text(selected.data('nomor-kartu'));
            $('#platNomor').text(selected.data('plat-nomor'));
            $('#jenisKendaraan').text(selected.data('jenis-kendaraan'));
            $('#waktuMasuk').text(moment(selected.data('waktu-masuk')).format('DD/MM/YYYY HH:mm:ss'));
            $('#hasilPencarian').removeClass('d-none');
        } else {
            selectedKendaraanId = null;
            $('#hasilPencarian').addClass('d-none');
        }
    });

    // Handler untuk proses keluar (tombol manual) - hanya untuk tab "Pilih Kendaraan"
    $('#btnProsesKeluar').on('click', function(e) {
        e.preventDefault();

        if (!selectedKendaraanId) {
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
                prosesKendaraanKeluar(selectedKendaraanId);
            }
        });
    });

    // Handle search input
    $('#search').on('input', function() {
        clearTimeout(this.delay);
        this.delay = setTimeout(function(){
            $('#searchForm').submit();
        }, 500);
    });

    // Handle clear search
    $('#clearSearch').on('click', function() {
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
});
</script>
@endpush