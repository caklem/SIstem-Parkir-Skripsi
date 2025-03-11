@extends('layouts.main')

@section('title')

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
    <h5 class="text-warning font-weight-bold mb-0">Data Parkir Masuk</h5>
</div>

<div class="container-fluid bg-light py-3">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <form action="{{ route('parkir.index') }}" method="GET" class="position-relative d-flex me-auto">
                    <div class="input-group">
                        <input type="text" id="search" name="search" class="form-control form-control-sm" placeholder="Cari nomor kartu atau jenis kendaraan..." value="{{ request('search') }}">
                    </div>
                </form>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-warning" id="btnTambah">Tambah</button>
                    <a href="{{ route('parkir.cetak-pdf') }}" class="btn btn-danger ml-2">Cetak PDF</a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @elseif(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div id="search-results">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTable">
                        <thead class="bg-warning text-white">
                            <tr>
                                <th>No Kartu</th>
                                <th>Nomor Plat</th>
                                <th>Jenis Kendaraan</th>
                                <th>Masuk</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @forelse($parkirs as $parkir)
                                <tr>
                                    <td>{{ $parkir->nomor_kartu }}</td>
                                    <td>{{ $parkir->plat_nomor }}</td>
                                    <td>{{ $parkir->jenis_kendaraan }}</td>
                                    <td>{{ $parkir->waktu_masuk }}</td>
                                    <td>
                                        <a href="#" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editParkirModal"
                                           data-id="{{ $parkir->id }}"
                                           data-plat-nomor="{{ $parkir->plat_nomor }}"
                                           data-jenis-kendaraan="{{ $parkir->jenis_kendaraan }}">
                                            Edit
                                        </a>
                                        <form action="{{ route('parkir.destroy', $parkir->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

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

        // Saat tombol Edit diklik
        $('a[data-target="#editParkirModal"]').on('click', function () {
            // Ambil data dari atribut data-*
            const id = $(this).data('id');
            const platNomor = $(this).data('plat-nomor');
            const jenisKendaraan = $(this).data('jenis-kendaraan');

            // Debug: Cetak data ke console untuk memastikan data terambil
            console.log('ID:', id);
            console.log('Plat Nomor:', platNomor);
            console.log('Jenis Kendaraan:', jenisKendaraan);

            // Isi form modal dengan data yang diambil
            $('#edit_plat_nomor').val(platNomor);
            $('#edit_jenis_kendaraan').val(jenisKendaraan);

            // Update action form dengan route yang sesuai
            $('#editParkirForm').attr('action', `/parkir/${id}`);
        });
    });
</script>

@endsection