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
    {{-- <h5 class="text-warning font-weight-bold m-0">Data Parkir Masuk</h5> --}}
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
                    <li class="breadcrumb-item"><a href="#"><i class="bi bi-house"></i></a></li>
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
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-warning btn-sm btn-md-normal" id="btnTambah">
                            <i class="bi bi-plus-lg me-1"></i>
                            <span class="d-none d-md-inline">Tambah Data</span>
                            <span class="d-inline d-md-none">Tambah</span>
                        </button>
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
                    <div class="form-group">
                        <label for="nomor_kartu">Nomor Kartu</label>
                        <input type="number" name="nomor_kartu" id="nomor_kartu" class="form-control @error('nomor_kartu') is-invalid @enderror" 
                            value="{{ old('nomor_kartu') }}" required autocomplete="off" autofocus placeholder="Masukkan nomor kartu (1-100)" min="1" max="100">
                        @error('nomor_kartu')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="plat_nomor">Nomor Plat</label>
                        <input type="text" name="plat_nomor" id="plat_nomor" class="form-control @error('plat_nomor') is-invalid @enderror" 
                            value="{{ old('plat_nomor') }}" required autocomplete="off" placeholder="Masukkan nomor plat kendaraan">
                        @error('plat_nomor')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="jenis_kendaraan">Jenis Kendaraan</label>
                        <select name="jenis_kendaraan" id="jenis_kendaraan" class="form-control @error('jenis_kendaraan') is-invalid @enderror" required>
                            <option value="" disabled selected>Pilih Jenis Kendaraan</option>
                            <option value="Sepeda Motor" {{ old('jenis_kendaraan') == 'Sepeda Motor' ? 'selected' : '' }}>Sepeda Motor</option>
                            <option value="Mobil" {{ old('jenis_kendaraan') == 'Mobil' ? 'selected' : '' }}>Mobil</option>
                            <option value="Bus" {{ old('jenis_kendaraan') == 'Bus' ? 'selected' : '' }}>Bus</option>
                        </select>
                        @error('jenis_kendaraan')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="text-center mt-3">
                        <p class="fw-medium text-muted">Atau gunakan fitur otomatis:</p>
                        <div class="d-flex justify-content-center gap-2">
                            <button type="button" class="btn btn-outline-warning" onclick="mulaiScanner()">
                                <i class="fas fa-qrcode"></i> Scan QR Code
                            </button>
                            <button type="button" class="btn btn-outline-warning" onclick="mulaiOCR()">
                                <i class="fas fa-camera"></i> Foto Plat
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
</style>
@endpush