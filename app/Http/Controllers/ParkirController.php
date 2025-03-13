<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use App\Models\Parkir;
use App\Models\ParkirKeluar;
use App\Models\KartuParkir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;  // Add this line

class ParkirController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    public function index(Request $request)
    {
        try {
            $query = Parkir::query();
            
            // Jika ada parameter pencarian
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nomor_kartu', 'LIKE', "%{$search}%")
                      ->orWhere('plat_nomor', 'LIKE', "%{$search}%")
                      ->orWhere('jenis_kendaraan', 'LIKE', "%{$search}%");
                });
            }

            $parkirs = $query->orderBy('waktu_masuk', 'desc')->get();
            
            return view('parkir.index', compact('parkirs'));
            
        } catch (\Exception $e) {
            Log::error('Error in Parkir Index:', [
                'message' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Terjadi kesalahan sistem');
        }
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validate input
            $validator = Validator::make($request->all(), [
                'nomor_kartu' => 'required|string', // Hapus exists validation
                'plat_nomor' => 'required|string|unique:parkirs',
                'jenis_kendaraan' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Cek kartu parkir, jika tidak ada maka buat baru
            $kartu = KartuParkir::firstOrCreate(
                ['nomor_kartu' => $request->nomor_kartu],
                ['status' => false]
            );

            // Cek status kartu
            if ($kartu->status) {
                return response()->json([
                    'success' => false,
                    'errors' => ['nomor_kartu' => ['Kartu ini sedang digunakan.']]
                ], 422);
            }

            // Create parkir record
            Parkir::create([
                'nomor_kartu' => $request->nomor_kartu,
                'plat_nomor' => strtoupper($request->plat_nomor),
                'jenis_kendaraan' => $request->jenis_kendaraan,
                'waktu_masuk' => now()
            ]);

            // Update status kartu
            $kartu->update(['status' => true]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error Parkir Store:', [
                'message' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'errors' => ['system' => ['Terjadi kesalahan: ' . $e->getMessage()]]
            ], 500);
        }
    }

    public function dataKeluar()
    {
        $parkirs = Parkir::all();
        $parkirKeluar = ParkirKeluar::orderBy('waktu_keluar', 'desc')->get();
        return view('parkir.keluar', compact('parkirs', 'parkirKeluar'));
    }

    public function cariKendaraan(Request $request)
    {
        $keyword = trim($request->input('keyword'));
        $kendaraan = Parkir::where('nomor_kartu', $keyword)
                          ->orWhere('plat_nomor', $keyword)
                          ->first();

        if (!$kendaraan) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $kendaraan
        ]);
    }

    public function prosesKeluar(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validate request
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:parkirs,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find parkir record
            $parkir = Parkir::findOrFail($request->id);

            // Create parkir_keluar record
            ParkirKeluar::create([
                'nomor_kartu' => $parkir->nomor_kartu,
                'plat_nomor' => $parkir->plat_nomor,
                'jenis_kendaraan' => $parkir->jenis_kendaraan,
                'waktu_masuk' => $parkir->waktu_masuk,
                'waktu_keluar' => now()
            ]);

            // Update kartu status
            KartuParkir::where('nomor_kartu', $parkir->nomor_kartu)
                       ->update(['status' => false]);

            // Delete from parkir
            $parkir->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Kendaraan berhasil diproses keluar'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error Parkir Keluar:', [
                'message' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }

    public function editKeluar($id)
    {
        $parkirKeluar = ParkirKeluar::findOrFail($id);
        return response()->json($parkirKeluar);
    }

    public function updateKeluar(Request $request, $id)
    {
        try {
            $parkirKeluar = ParkirKeluar::findOrFail($id);
            $parkirKeluar->update([
                'nomor_kartu' => $request->nomor_kartu,
                'plat_nomor' => $request->plat_nomor,
                'jenis_kendaraan' => $request->jenis_kendaraan
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diupdate'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nomor_kartu' => 'required|string',
                'plat_nomor' => 'required|string',
                'jenis_kendaraan' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $parkir = Parkir::findOrFail($id);
            
            // Check if plat_nomor is changed and ensure it's unique
            if ($parkir->plat_nomor !== $request->plat_nomor) {
                $exists = Parkir::where('plat_nomor', $request->plat_nomor)
                    ->where('id', '!=', $id)
                    ->exists();
                
                if ($exists) {
                    return response()->json([
                        'success' => false,
                        'errors' => ['plat_nomor' => ['Plat nomor sudah digunakan.']]
                    ], 422);
                }
            }

            $parkir->update([
                'nomor_kartu' => $request->nomor_kartu,
                'plat_nomor' => strtoupper($request->plat_nomor),
                'jenis_kendaraan' => $request->jenis_kendaraan
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diupdate'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error Parkir Update:', [
                'message' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'errors' => ['system' => ['Terjadi kesalahan: ' . $e->getMessage()]]
            ], 500);
        }
    }

    public function cetakPdfMasuk()
    {
        $parkirs = Parkir::orderBy('waktu_masuk', 'desc')->get();
        $pdf = PDF::loadView('parkir.parkir_pdf', compact('parkirs'));
        return $pdf->download('laporan-parkir-masuk-'.now()->format('F-Y').'.pdf');
    }

    public function cetakPdfKeluar()
    {
        $parkirKeluar = ParkirKeluar::orderBy('waktu_keluar', 'desc')->get();
        $pdf = PDF::loadView('parkir.parkir_keluar_pdf', compact('parkirKeluar'));
        return $pdf->download('laporan-parkir-keluar-'.now()->format('F-Y').'.pdf');
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Cek apakah data parkir ada
            $parkir = Parkir::find($id);
            
            if (!$parkir) {
                return response()->json([
                    'success' => false,
                    'errors' => ['system' => ['Data parkir tidak ditemukan']]
                ], 404);
            }

            // Update status kartu menjadi tersedia
            if ($parkir->nomor_kartu) {
                KartuParkir::where('nomor_kartu', $parkir->nomor_kartu)
                          ->update(['status' => false]);
            }

            $parkir->delete();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error Parkir Delete:', [
                'message' => $e->getMessage(),
                'id' => $id
            ]);

            return response()->json([
                'success' => false,
                'errors' => ['system' => ['Terjadi kesalahan sistem']]
            ], 500);
        }
    }

    public function keluarIndex(Request $request)
    {
        try {
            $query = ParkirKeluar::query();
            
            // Jika ada parameter pencarian
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nomor_kartu', 'LIKE', "%{$search}%")
                      ->orWhere('plat_nomor', 'LIKE', "%{$search}%")
                      ->orWhere('jenis_kendaraan', 'LIKE', "%{$search}%");
                });
            }

            $parkirs = Parkir::orderBy('waktu_masuk', 'desc')->get();
            $parkirKeluar = $query->orderBy('waktu_keluar', 'desc')->get();

            // Format dates
            $parkirKeluar->transform(function ($item) {
                $item->waktu_masuk_formatted = Carbon::parse($item->waktu_masuk)->format('d/m/Y H:i:s');
                $item->waktu_keluar_formatted = Carbon::parse($item->waktu_keluar)->format('d/m/Y H:i:s');
                return $item;
            });

            return view('parkir.keluar', compact('parkirs', 'parkirKeluar'));

        } catch (\Exception $e) {
            Log::error('Error in Parkir Keluar Index:', [
                'message' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Terjadi kesalahan sistem');
        }
    }

    public function searchParkirKeluar(Request $request)
{
    try {
        $search = $request->input('search');
        $query = ParkirKeluar::query();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nomor_kartu', 'LIKE', "%{$search}%")
                  ->orWhere('plat_nomor', 'LIKE', "%{$search}%")
                  ->orWhere('jenis_kendaraan', 'LIKE', "%{$search}%");
            });
        }

        $results = $query->orderBy('waktu_keluar', 'desc')->get();

        // Format dates
        $results->transform(function ($item) {
            $item->waktu_masuk_formatted = Carbon::parse($item->waktu_masuk)->format('d/m/Y H:i:s');
            $item->waktu_keluar_formatted = Carbon::parse($item->waktu_keluar)->format('d/m/Y H:i:s');
            return $item;
        });

        return response()->json([
            'success' => true,
            'data' => $results
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ], 500);
    }
    }
}


