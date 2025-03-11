@extends('layouts.main')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="alert alert-success text-center" role="alert">
                <strong>Notifikasi:</strong> Tambah Data Parkir Berhasil!
            </div>
        </div>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white text-center">
                    <h3 class="card-title mb-0">Tambah Data Parkir</h3>
                </div>
                <div class="card-body">
                    <p class="text-center font-weight-bold">Silakan masukkan data kendaraan</p>
                    <form action="{{ route('parkir.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="plat_nomor">Plat Nomor:</label>
                            <input type="text" name="plat_nomor" id="plat_nomor" class="form-control" placeholder="Masukkan plat nomor" required>
                        </div>
                        <div class="form-group">
                            <label for="jenis_kendaraan">Jenis Kendaraan:</label>
                            <input type="text" name="jenis_kendaraan" id="jenis_kendaraan" class="form-control" placeholder="Masukkan jenis kendaraan" required>
                        </div>
                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary w-50">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row justify-content-center mt-4">
        <div class="col-md-10">
            <div class="card shadow-sm" style="backdrop-filter: blur(5px);">
                <div class="card-header bg-secondary text-white text-center">
                    <h3 class="card-title mb-0">Data Parkir</h3>
                </div>
                <div class="card-body">
                    <input type="text" id="searchInput" class="form-control mb-3" placeholder="Cari plat nomor...">
                    <table class="table table-bordered table-hover" id="dataTable">
                        <thead class="thead-dark">
                            <tr>
                                <th>No</th>
                                <th>Plat Nomor</th>
                                <th>Jenis Kendaraan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data akan ditampilkan di sini -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="parkirModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah Data Parkir</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="text" name="plat_nomor" id="modal_plat_nomor" placeholder="Plat Nomor" class="form-control" required>
                    <br>
                    <input type="text" name="jenis_kendaraan" id="modal_jenis_kendaraan" placeholder="Jenis Kendaraan" class="form-control" required>
                    <br>
                    <button type="button" class="btn btn-secondary" onclick="mulaiScanner()">Scan</button>
                    <div id="reader" style="width: 300px; height: 300px; display:none;"></div>
                    <br>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function () {
        $("#searchInput").on("keyup", function () {
            var value = $(this).val().toLowerCase();
            $("#dataTable tbody tr").filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    });
</script>
@endsection
