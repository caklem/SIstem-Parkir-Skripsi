<!-- Area Hasil Pencarian -->
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
                                        <a href="{{ route('parkir.edit', $parkir->id) }}" class="btn btn-warning btn-sm">Edit</a>
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