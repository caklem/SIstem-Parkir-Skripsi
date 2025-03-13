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
    <!-- Judul -->
    {{-- <h5 class="text-warning font-weight-bold mb-0">Dashboard Parkir Keluar</h5>     --}}
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
                    <li class="breadcrumb-item"><a href="#"><i class="bi bi-house"></i></a></li>
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
                            <div class="progress-bar bg-success" style="width: {{ ($parkirKeluar->where('jenis_kendaraan', 'Sepeda Motor')->count() / $parkirs->count()) * 100 }}%"></div>
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
                            <div class="progress-bar bg-primary" style="width: {{ ($parkirKeluar->where('jenis_kendaraan', 'Mobil')->count() / $parkirs->count()) * 100 }}%"></div>
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
                            <div class="progress-bar bg-danger" style="width: {{ ($parkirKeluar->where('jenis_kendaraan', 'Bus')->count() / $parkirs->count()) * 100 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            
            <!-- Search & Buttons -->
            <div class="col-12">
                <div class="card card-outline card-warning shadow-sm">
                    <div class="card-header">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                            {{-- search --}}
                            <div class="row mb-3">
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

                            
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-warning btn-sm btn-md-normal" data-bs-toggle="modal" data-bs-target="#modalTambah"> 
                                <i class="bi bi-plus-lg me-1"></i>
                                <span class="d-none d-md-inline">Tambah Data</span>
                                <span class="d-inline d-md-none">Tambah</span>
                            </button>
                                <a href="{{ route('parkir.cetak-pdf-keluar') }}" class="btn btn-danger btn-sm btn-md-normal">
                                    <i class="bi bi-file-pdf me-1"></i>
                                    <span class="d-none d-md-inline">Export PDF</span>
                                    <span class="d-inline d-md-none">PDF</span>
                                </a>
                            </div>
                        </div>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<!-- Bootstrap JS (Opsional, jika diperlukan) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</section>

<!-- Modal Tambah Data -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Data Parkir Keluar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formParkirKeluar">
                @csrf
                <div class="modal-body">
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

                    <div id="hasilPencarian" class="d-none">
                        <div class="card">
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <td>Nomor Kartu</td>
                                        <td>: <span id="noKartu"></span></td>
                                    </tr>
                                    <tr>
                                        <td>Plat Nomor</td>
                                        <td>: <span id="platNomor"></span></td>
                                    </tr>
                                    <tr>
                                        <td>Jenis Kendaraan</td>
                                        <td>: <span id="jenisKendaraan"></span></td>
                                    </tr>
                                    <tr>
                                        <td>Waktu Masuk</td>
                                        <td>: <span id="waktuMasuk"></span></td>
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

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Data Parkir Keluar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEdit">
                @csrf
                @method('PUT')
                <input type="hidden" id="editId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nomor Kartu</label>
                        <input type="text" class="form-control" id="editNoKartu" name="nomor_kartu" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Plat Nomor</label>
                        <input type="text" class="form-control" id="editPlatNomor" name="plat_nomor" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis Kendaraan</label>
                        <select class="form-select" id="editJenisKendaraan" name="jenis_kendaraan" required>
                            <option value="Sepeda Motor">Sepeda Motor</option>
                            <option value="Mobil">Mobil</option>
                            <option value="Bus">Bus</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
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
    </style>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Remove any overlayScrollbars initialization here
    // It's now handled in main.blade.php
    
    // Your existing AJAX handlers
    $('#btnProsesKeluar').on('click', function(e) {
        e.preventDefault();
        // ...existing code...
    });
});
</script>
@endpush

@section('scripts')
    <script>
        $(document).ready(function() {
            function cariKendaraan() {
                const searchValue = $('#searchInput').val().trim();
                
                // Debug value yang akan dikirim
                console.log('Search Value:', searchValue);

                $.ajax({
                    url: '{{ route("parkir.cari") }}',
                    type: 'GET',
                    data: { search: searchValue }, // Pastikan parameter bernama 'search'
                    dataType: 'json',
                    beforeSend: function() {
                        $('#searchButton').prop('disabled', true);
                        $('#searchButton').html('<span class="spinner-border spinner-border-sm"></span>');
                    },
                    success: function(response) {
                        console.log('Response:', response); // Debug response
                        if (response.success && response.data) {
                            // ...rest of success handling
                        } else {
                            alert(response.message || 'Data tidak ditemukan');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr); // Debug error
                        alert('Terjadi kesalahan saat mencari data');
                    },
                    complete: function() {
                        $('#searchButton').prop('disabled', false);
                        $('#searchButton').html('<i class="bi bi-search me-1"></i>Cari');
                    }
                });
            }

            // Event handlers
            $('#searchInput').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    cariKendaraan();
                }
            });

            $('#searchButton').on('click', cariKendaraan);
        });
    </script>
    <script>
        let selectedKendaraanId = null;

        $('#btnCari').click(function() {
            const keyword = $('#keyword').val().trim();
            
            if (!keyword) {
                alert('Masukkan nomor kartu atau plat nomor');
                return;
            }

            $.ajax({
                url: '{{ route("parkir.cari") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    keyword: keyword
                },
                beforeSend: function() {
                    $('#btnCari').prop('disabled', true);
                },
                success: function(response) {
                    if (response.success) {
                        selectedKendaraanId = response.data.id;
                        $('#noKartu').text(response.data.nomor_kartu);
                        $('#platNomor').text(response.data.plat_nomor);
                        $('#jenisKendaraan').text(response.data.jenis_kendaraan);
                        $('#hasilPencarian').show();
                    }
                },
                error: function(xhr) {
                    alert(xhr.responseJSON?.message || 'Terjadi kesalahan');
                    $('#hasilPencarian').hide();
                },
                complete: function() {
                    $('#btnCari').prop('disabled', false);
                }
            });
        });

        $('#btnProsesKeluar').click(function() {
            if (!selectedKendaraanId) return;

            if (confirm('Proses kendaraan keluar?')) {
                $.ajax({
                    url: '{{ route("parkir.proses-keluar") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: selectedKendaraanId
                    },
                    success: function(response) {
                        alert('Kendaraan berhasil keluar');
                        location.reload();
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || 'Gagal memproses kendaraan');
                    }
                });
            }
        });

        // Pencarian kendaraan di modal tambah
        $('#btnSearch').click(function() {
            const keyword = $('#searchKendaraan').val().trim();
            
            if (!keyword) {
                alert('Masukkan nomor kartu atau plat nomor');
                return;
            }

            $.ajax({
                url: '{{ route("parkir.cari") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    keyword: keyword
                },
                success: function(response) {
                    if (response.success) {
                        selectedKendaraanId = response.data.id;
                        $('#noKartu').text(response.data.nomor_kartu);
                        $('#platNomor').text(response.data.plat_nomor);
                        $('#jenisKendaraan').text(response.data.jenis_kendaraan);
                        $('#waktuMasuk').text(moment(response.data.waktu_masuk).format('DD/MM/YYYY HH:mm:ss'));
                        $('#hasilPencarian').removeClass('d-none');
                    } else {
                        alert(response.message);
                        $('#hasilPencarian').addClass('d-none');
                    }
                }
            });
        });

        // Edit data
        function editData(id) {
            $.get(`/parkir/keluar/${id}/edit`, function(data) {
                $('#editId').val(data.id);
                $('#editNoKartu').val(data.nomor_kartu);
                $('#editPlatNomor').val(data.plat_nomor);
                $('#editJenisKendaraan').val(data.jenis_kendaraan);
            });
        }

        $('#formEdit').submit(function(e) {
            e.preventDefault();
            const id = $('#editId').val();

            $.ajax({
                url: `/parkir/keluar/${id}`,
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#modalEdit').modal('hide');
                    alert('Data berhasil diupdate');
                    location.reload();
                },
                error: function() {
                    alert('Gagal mengupdate data');
                }
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            let selectedKendaraanId = null;

            // Handler untuk pemilihan kendaraan
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

            // Handler untuk proses keluar
            $(document).on('click', '#btnProsesKeluar', function(e) {
                e.preventDefault();

                if (!selectedKendaraanId) {
                    alert('Pilih kendaraan terlebih dahulu!');
                    return;
                }

                if (confirm('Apakah Anda yakin ingin memproses kendaraan keluar?')) {
                    const $btn = $(this);

                    $.ajax({
                        url: '{{ route("parkir.proses-keluar") }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            id: selectedKendaraanId
                        },
                        beforeSend: function() {
                            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Memproses...');
                        },
                        success: function(response) {
                            if (response.success) {
                                alert('Kendaraan berhasil keluar');
                                $('#modalTambah').modal('hide');
                                window.location.reload();
                            } else {
                                alert(response.message || 'Gagal memproses kendaraan');
                            }
                        },
                        error: function(xhr) {
                            alert(xhr.responseJSON?.message || 'Gagal memproses kendaraan keluar');
                        },
                        complete: function() {
                            $btn.prop('disabled', false).html('Proses Keluar');
                        }
                    });
                }
            });

            // Reset form saat modal ditutup
            $('#modalTambah').on('hidden.bs.modal', function() {
                selectedKendaraanId = null;
                $('#searchKendaraan').val('').trigger('change');
                $('#hasilPencarian').addClass('d-none');
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            // Format tanggal menggunakan moment.js
            function formatDate(date) {
                return moment(date).format('DD/MM/YYYY HH:mm:ss');
            }

            $('#searchKendaraan').on('change', function() {
                const selected = $(this).find(':selected');
                if (selected.val()) {
                    selectedKendaraanId = selected.val();
                    $('#noKartu').text(selected.data('nomor-kartu'));
                    $('#platNomor').text(selected.data('plat-nomor'));
                    $('#jenisKendaraan').text(selected.data('jenis-kendaraan'));
                    // Format waktu masuk menggunakan moment.js
                    $('#waktuMasuk').text(formatDate(selected.data('waktu-masuk')));
                    $('#hasilPencarian').removeClass('d-none');
                } else {
                    $('#hasilPencarian').addClass('d-none');
                    selectedKendaraanId = null;
                }
            });
        });
    </script>
    @push('scripts')
<script>
$(document).ready(function() {
    let selectedKendaraanId = null;

    // Handler untuk pemilihan kendaraan
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

    // Handler untuk proses keluar
    $('#btnProsesKeluar').on('click', function(e) {
        e.preventDefault();

        if (!selectedKendaraanId) {
            alert('Pilih kendaraan terlebih dahulu!');
            return;
        }

        if (!confirm('Apakah Anda yakin ingin memproses kendaraan keluar?')) {
            return;
        }

        $.ajax({
            url: '{{ route("parkir.proses-keluar") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: selectedKendaraanId
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert(response.message || 'Gagal memproses kendaraan keluar');
                }
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors || {};
                let errorMessage = '';
                
                if (errors) {
                    for (let field in errors) {
                        errorMessage += errors[field].join('\n') + '\n';
                    }
                }
                
                alert(errorMessage || xhr.responseJSON?.message || 'Terjadi kesalahan sistem');
            }
        });
    });

    // Reset form saat modal ditutup
    $('#modalTambah').on('hidden.bs.modal', function() {
        selectedKendaraanId = null;
        $('#searchKendaraan').val('').trigger('change');
        $('#hasilPencarian').addClass('d-none');
    });
});
</script>
@endpush
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let searchTimeout;
    const tableBody = $('#dataTable tbody');
    
    function performSearch() {
        const searchValue = $('#searchInput').val().trim();
        const searchButton = $('#searchButton');
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        searchTimeout = setTimeout(() => {
            $.ajax({
                url: '{{ route("parkir.keluar.search") }}',
                type: 'GET',
                data: { search: searchValue },
                beforeSend: function() {
                    searchButton.prop('disabled', true)
                        .html('<span class="spinner-border spinner-border-sm"></span>');
                },
                success: function(response) {
                    if (response.success) {
                        // Clear existing table rows
                        tableBody.empty();
                        
                        if (response.data.length > 0) {
                            // Add new rows
                            response.data.forEach(function(item) {
                                const row = `
                                    <tr>
                                        <td>${item.nomor_kartu}</td>
                                        <td class="text-uppercase">${item.plat_nomor}</td>
                                        <td>
                                            <span class="badge bg-${item.jenis_kendaraan === 'Mobil' ? 'primary' : (item.jenis_kendaraan === 'Sepeda Motor' ? 'success' : 'danger')} rounded-pill">
                                                <i class="bi bi-${item.jenis_kendaraan === 'Mobil' ? 'car-front' : (item.jenis_kendaraan === 'Sepeda Motor' ? 'bicycle' : 'bus-front')} me-1"></i>
                                                ${item.jenis_kendaraan}
                                            </span>
                                        </td>
                                        <td>${item.waktu_masuk_formatted}</td>
                                        <td>${item.waktu_keluar_formatted}</td>
                                    </tr>
                                `;
                                tableBody.append(row);
                            });
                        } else {
                            // Show no data message
                            tableBody.html(`
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                            <p class="text-muted mt-2">Tidak ada data yang ditemukan</p>
                                        </div>
                                    </td>
                                </tr>
                            `);
                        }
                    }
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    alert('Terjadi kesalahan saat mencari data');
                },
                complete: function() {
                    searchButton.prop('disabled', false)
                        .html('<i class="bi bi-search me-1"></i>Cari');
                }
            });
        }, 500); // Delay 500ms after typing
    }

    // Event handlers
    $('#searchInput').on('input', performSearch);
    
    $('#searchInput').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            performSearch();
        }
    });

    $('#searchButton').on('click', performSearch);
});
</script>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
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

@push('styles')
<style>
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