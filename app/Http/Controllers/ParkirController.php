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

    //Login
    public function __construct()
    {
        $this->middleware('auth');
    }

    // dashboard    
    public function dashboard(Request $request)
    {
        $range = $request->input('range', 'day');
        $startDate = null;
        $endDate = null;

        switch ($range) {
            case 'day':
                $startDate = now()->startOfDay();
                $endDate = now()->endOfDay();
                break;
            case 'week':
                $startDate = now()->startOfWeek();
                $endDate = now()->endOfWeek();
                break;
            case 'month':
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
                break;
            case 'year':
                $startDate = now()->startOfYear();
                $endDate = now()->endOfYear();
                break;
            case 'custom':
                $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : now()->subDays(30)->startOfDay();
                $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : now()->endOfDay();
                break;
        }

        // Get statistics data based on date range
        $kendaraanMasukKeluar = ParkirKeluar::selectRaw('
            DATE(waktu_masuk) as tanggal,
            COUNT(*) as total,
            SUM(CASE WHEN jenis_kendaraan = "Mobil" THEN 1 ELSE 0 END) as mobil,
            SUM(CASE WHEN jenis_kendaraan = "Sepeda Motor" THEN 1 ELSE 0 END) as motor,
            SUM(CASE WHEN jenis_kendaraan = "Bus" THEN 1 ELSE 0 END) as bus
        ')
        ->whereBetween('waktu_masuk', [$startDate, $endDate])
        ->groupBy('tanggal')
        ->orderBy('tanggal')
        ->get();

        // Basic stats
        $stats = [
            'total_kendaraan_aktif' => Parkir::count(),
            'total_masuk' => ParkirKeluar::count(),
            'total_keluar' => ParkirKeluar::count(),
            'rata_rata_durasi' => $this->hitungRataRataDurasi()
        ];

        // Stats for today
        $today = Carbon::today();
        $stats['mobil_hari_ini'] = ParkirKeluar::whereDate('waktu_masuk', $today)
            ->where('jenis_kendaraan', 'Mobil')
            ->count();
            
        $stats['motor_hari_ini'] = ParkirKeluar::whereDate('waktu_masuk', $today)
            ->where('jenis_kendaraan', 'Sepeda Motor')
            ->count();

        $stats['bus_hari_ini'] = ParkirKeluar::whereDate('waktu_masuk', $today)
            ->where('jenis_kendaraan', 'Bus')
            ->count();
            
        $stats['total_kendaraan_hari_ini'] = $stats['mobil_hari_ini'] + 
            $stats['motor_hari_ini'] + 
            $stats['bus_hari_ini'];

        // Data for pie chart
        $jenisKendaraan = ParkirKeluar::selectRaw('
            jenis_kendaraan, 
            COUNT(*) as total
        ')
        ->groupBy('jenis_kendaraan')
        ->get();

        // Recent parking history
        $parkirHistory = ParkirKeluar::orderBy('waktu_keluar', 'desc')
            ->take(10)
            ->get()
            ->map(function($item) {
                $masuk = Carbon::parse($item->waktu_masuk);
                $keluar = Carbon::parse($item->waktu_keluar);
                $item->durasi = $masuk->diffForHumans($keluar, true);
                return $item;
            });

        return view('parkir.dashboard', compact(
            'stats',
            'kendaraanMasukKeluar',
            'jenisKendaraan',
            'parkirHistory'
        ));

    }

    private function getKendaraanMasukData($startDate, $endDate)
    {
        return Parkir::selectRaw('DATE(waktu_masuk) as tanggal, COUNT(*) as total')
            ->whereBetween('waktu_masuk', [$startDate, $endDate])
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();
    }

    private function getKendaraanKeluarData($startDate, $endDate)
    {
        return ParkirKeluar::selectRaw('DATE(waktu_keluar) as tanggal, COUNT(*) as total')
            ->whereBetween('waktu_keluar', [$startDate, $endDate])
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();
    }

    private function getJenisKendaraanData()
    {
        return Parkir::selectRaw('jenis_kendaraan, COUNT(*) as total')
            ->groupBy('jenis_kendaraan')
            ->get();
    }

    private function getParkirTerbaru()
    {
        return Parkir::latest('waktu_masuk')
            ->take(5)
            ->get();
    }

    private function hitungRataRataDurasi($date = null)
    {
        $query = ParkirKeluar::selectRaw('AVG(TIMESTAMPDIFF(MINUTE, waktu_masuk, waktu_keluar)) as durasi');
        
        if ($date) {
            $query->whereDate('waktu_keluar', $date);
        }
        
        $rataRata = $query->first();
        $durasi = round($rataRata->durasi ?? 0);
        
        // Convert ke format jam:menit
        $jam = floor($durasi / 60);
        $menit = $durasi % 60;
        
        return $durasi > 0 ? "$jam jam $menit menit" : '-';
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
        try {
            $parkirs = Parkir::orderBy('waktu_masuk', 'desc')->get();
            $pdf = Pdf::loadView('parkir.parkir_pdf', compact('parkirs'));
            return $pdf->download('laporan-parkir-masuk-'.now()->format('F-Y').'.pdf');
        } catch (\Exception $e) {
            Log::error('Error in Cetak PDF Masuk:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Terjadi kesalahan saat mencetak PDF: ' . $e->getMessage());
        }
    }

    public function cetakPdfKeluar()
    {
        try {
            $parkirKeluar = ParkirKeluar::orderBy('waktu_keluar', 'desc')->get();
            $pdf = Pdf::loadView('parkir.parkir_keluar_pdf', compact('parkirKeluar'));
            return $pdf->download('laporan-parkir-keluar-'.now()->format('F-Y').'.pdf');
        } catch (\Exception $e) {
            Log::error('Error in Cetak PDF Keluar:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Terjadi kesalahan saat mencetak PDF: ' . $e->getMessage());
        }
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

    public function exportPDF(Request $request)
    {
        try {
            $range = $request->input('range', 'day');
            $startDate = null;
            $endDate = null;

            switch ($range) {
                case 'day':
                    $startDate = now()->startOfDay();
                    $endDate = now()->endOfDay();
                    $periode = 'Hari Ini (' . now()->format('d/m/Y') . ')';
                    break;
                case 'week':
                    $startDate = now()->startOfWeek();
                    $endDate = now()->endOfWeek();
                    $periode = 'Minggu Ini (' . $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y') . ')';
                    break;
                case 'month':
                    $startDate = now()->startOfMonth();
                    $endDate = now()->endOfMonth();
                    $periode = 'Bulan ' . now()->format('F Y');
                    break;
                case 'year':
                    $startDate = now()->startOfYear();
                    $endDate = now()->endOfYear();
                    $periode = 'Tahun ' . now()->format('Y');
                    break;
                case 'custom':
                    $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : now()->subDays(30)->startOfDay();
                    $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : now()->endOfDay();
                    $periode = $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y');
                    break;
            }

            // Get data
            $stats = $this->getStats($startDate, $endDate);
            $parkirHistory = ParkirKeluar::whereBetween('waktu_masuk', [$startDate, $endDate])
                                        ->orderBy('waktu_masuk', 'desc')
                                        ->get();

            $pdf = Pdf::loadView('parkir.dashboard-pdf', [
                'stats' => $stats,
                'parkirHistory' => $parkirHistory,
                'periode' => $periode
            ]);

            return $pdf->download('laporan-dashboard-' . now()->format('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            Log::error('Error in Export PDF:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Terjadi kesalahan saat export PDF: ' . $e->getMessage());
        }
    }

    public function exportDashboardPDF(Request $request)
    {
        try {
            $range = $request->input('range', 'day');
            $startDate = null;
            $endDate = null;

            // Set date range
            switch ($range) {
                case 'day':
                    $startDate = now()->startOfDay();
                    $endDate = now()->endOfDay();
                    $periode = 'Hari Ini (' . now()->format('d/m/Y') . ')';
                    break;
                case 'week':
                    $startDate = now()->startOfWeek();
                    $endDate = now()->endOfWeek();
                    $periode = 'Minggu Ini (' . $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y') . ')';
                    break;
                case 'month':
                    $startDate = now()->startOfMonth();
                    $endDate = now()->endOfMonth();
                    $periode = 'Bulan ' . now()->format('F Y');
                    break;
                case 'year':
                    $startDate = now()->startOfYear();
                    $endDate = now()->endOfYear();
                    $periode = 'Tahun ' . now()->format('Y');
                    break;
                case 'custom':
                    $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : now()->subDays(30)->startOfDay();
                    $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : now()->endOfDay();
                    $periode = $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y');
                    break;
            }

            // Get data
            $stats = [
                'total_kendaraan_aktif' => Parkir::count(),
                'total_masuk' => ParkirKeluar::whereBetween('waktu_masuk', [$startDate, $endDate])->count(),
                'total_keluar' => ParkirKeluar::whereBetween('waktu_keluar', [$startDate, $endDate])->count(),
                'rata_rata_durasi' => $this->hitungRataRataDurasi(),
                'mobil_hari_ini' => ParkirKeluar::whereDate('waktu_masuk', today())->where('jenis_kendaraan', 'Mobil')->count(),
                'motor_hari_ini' => ParkirKeluar::whereDate('waktu_masuk', today())->where('jenis_kendaraan', 'Sepeda Motor')->count(),
                'bus_hari_ini' => ParkirKeluar::whereDate('waktu_masuk', today())->where('jenis_kendaraan', 'Bus')->count(),
            ];

            $parkirHistory = ParkirKeluar::whereBetween('waktu_masuk', [$startDate, $endDate])
                ->orderBy('waktu_masuk', 'desc')
                ->take(20)
                ->get()
                ->map(function($item) {
                    $masuk = Carbon::parse($item->waktu_masuk);
                    $keluar = Carbon::parse($item->waktu_keluar);
                    $item->durasi = $masuk->diffForHumans($keluar, true);
                    return $item;
                });

            $pdf = PDF::loadView('parkir.dashboard-pdf', [
                'stats' => $stats,
                'parkirHistory' => $parkirHistory,
                'periode' => $periode
            ]);

            return $pdf->download('laporan-dashboard-' . now()->format('Y-m-d') . '.pdf');

        } catch (\Exception $e) {
            Log::error('Error generating PDF: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat mengexport PDF: ' . $e->getMessage());
        }
    }

    public function checkCard(Request $request)
    {
        try {
            $request->validate([
                'nomor_kartu' => 'required|string'
            ]);

            // Cek apakah kartu sedang digunakan di parkir_masuk
            $kartuDipakai = Parkir::where('nomor_kartu', $request->nomor_kartu)
                                 ->whereNull('waktu_keluar')
                                 ->first();

            if ($kartuDipakai) {
                return response()->json([
                    'success' => true,
                    'is_used' => true,
                    'message' => 'Kartu sedang digunakan'
                ]);
            }

            return response()->json([
                'success' => true,
                'is_used' => false,
                'message' => 'Kartu tersedia'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}


