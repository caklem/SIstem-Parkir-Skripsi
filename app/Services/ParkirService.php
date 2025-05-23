<?php

namespace App\Services;

use App\Models\Parkir;
use App\Models\ParkirKeluar;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ParkirService
{
    /**
     * Mencatat kendaraan masuk.
     *
     * @param array $data
     * @return array
     */
    public function recordKendaraanMasuk(array $data): array
    {
        try {
            DB::beginTransaction();

            // Validasi nomor kartu
            if ($this->isKartuInUse($data['nomor_kartu'])) {
                return [
                    'success' => false,
                    'message' => 'Nomor kartu sudah digunakan dan belum keluar'
                ];
            }

            // Validasi plat nomor
            if ($this->isPlatNomorInUse($data['plat_nomor'])) {
                return [
                    'success' => false,
                    'message' => 'Kendaraan dengan plat nomor tersebut sudah ada di dalam area parkir'
                ];
            }

            // Standarisasi plat nomor
            $data['plat_nomor'] = $this->standardizePlatNomor($data['plat_nomor']);

            // Catat kendaraan masuk
            $parkir = Parkir::create([
                'nomor_kartu' => $data['nomor_kartu'],
                'plat_nomor' => $data['plat_nomor'],
                'jenis_kendaraan' => $data['jenis_kendaraan'],
                'waktu_masuk' => Carbon::now(),
            ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Kendaraan berhasil dicatat masuk',
                'data' => $parkir
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error recording kendaraan masuk: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Memproses kendaraan keluar.
     *
     * @param int $id
     * @param string $verifiedBy
     * @param string $verificationMethod
     * @return array
     */
    public function processKendaraanKeluar(int $id, string $verifiedBy, string $verificationMethod): array
    {
        try {
            DB::beginTransaction();

            // Cari data kendaraan
            $parkir = Parkir::find($id);
            
            if (!$parkir) {
                return [
                    'success' => false,
                    'message' => 'Data parkir tidak ditemukan'
                ];
            }

            // Periksa apakah kendaraan sudah keluar
            if (ParkirKeluar::where('id_parkir', $id)->exists()) {
                return [
                    'success' => false,
                    'message' => 'Kendaraan ini sudah tercatat keluar sebelumnya'
                ];
            }

            // Catat kendaraan keluar
            $parkirKeluar = ParkirKeluar::create([
                'id_parkir' => $id,
                'nomor_kartu' => $parkir->nomor_kartu,
                'plat_nomor' => $parkir->plat_nomor,
                'jenis_kendaraan' => $parkir->jenis_kendaraan,
                'waktu_masuk' => $parkir->waktu_masuk,
                'waktu_keluar' => Carbon::now(),
                'verified_by' => $verifiedBy,
                'verification_method' => $verificationMethod
            ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Kendaraan berhasil diproses keluar',
                'data' => $parkirKeluar
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing kendaraan keluar: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Mencari data kendaraan berdasarkan nomor kartu.
     *
     * @param string $nomorKartu
     * @return array
     */
    public function findByNomorKartu(string $nomorKartu): array
    {
        try {
            $parkir = Parkir::where('nomor_kartu', $nomorKartu)
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('parkir_keluar')
                        ->whereRaw('parkir_keluar.id_parkir = parkir.id');
                })
                ->first();
                
            if (!$parkir) {
                return [
                    'success' => false,
                    'message' => 'Kartu tidak ditemukan atau kendaraan sudah keluar'
                ];
            }
            
            return [
                'success' => true,
                'data' => $parkir
            ];
        } catch (\Exception $e) {
            Log::error('Error finding kendaraan by nomor kartu: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Memeriksa apakah nomor kartu sedang digunakan.
     *
     * @param string $nomorKartu
     * @return bool
     */
    private function isKartuInUse(string $nomorKartu): bool
    {
        return Parkir::where('nomor_kartu', $nomorKartu)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('parkir_keluar')
                    ->whereRaw('parkir_keluar.id_parkir = parkir.id');
            })
            ->exists();
    }

    /**
     * Memeriksa apakah plat nomor sedang digunakan.
     *
     * @param string $platNomor
     * @return bool
     */
    private function isPlatNomorInUse(string $platNomor): bool
    {
        $platNomor = $this->standardizePlatNomor($platNomor);
        
        return Parkir::where('plat_nomor', $platNomor)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('parkir_keluar')
                    ->whereRaw('parkir_keluar.id_parkir = parkir.id');
            })
            ->exists();
    }

    /**
     * Standardisasi format plat nomor.
     *
     * @param string $platNomor
     * @return string
     */
    public function standardizePlatNomor(string $platNomor): string
    {
        // Hapus spasi berlebih dan ubah ke uppercase
        $platNomor = trim(strtoupper($platNomor));
        
        // Hapus karakter khusus
        $platNomor = preg_replace('/[^A-Z0-9\s]/', '', $platNomor);
        
        return $platNomor;
    }
    
    /**
     * Mendapatkan statistik parkir untuk dashboard.
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getDashboardStats(string $startDate, string $endDate): array
    {
        try {
            $stats = [
                'total_kendaraan' => Parkir::whereBetween('waktu_masuk', [$startDate, $endDate])->count(),
                'kendaraan_masuk' => Parkir::whereBetween('waktu_masuk', [$startDate, $endDate])->count(),
                'kendaraan_keluar' => ParkirKeluar::whereBetween('waktu_keluar', [$startDate, $endDate])->count(),
                'mobil' => Parkir::where('jenis_kendaraan', 'Mobil')->whereBetween('waktu_masuk', [$startDate, $endDate])->count(),
                'motor' => Parkir::where('jenis_kendaraan', 'Motor')->whereBetween('waktu_masuk', [$startDate, $endDate])->count(),
                'bus' => Parkir::where('jenis_kendaraan', 'Bus')->whereBetween('waktu_masuk', [$startDate, $endDate])->count(),
            ];
            
            // Kendaraan di dalam
            $stats['kendaraan_di_dalam'] = $stats['kendaraan_masuk'] - $stats['kendaraan_keluar'];
            
            return $stats;
        } catch (\Exception $e) {
            Log::error('Error calculating dashboard stats: ' . $e->getMessage());
            
            return [
                'total_kendaraan' => 0,
                'kendaraan_masuk' => 0,
                'kendaraan_keluar' => 0,
                'kendaraan_di_dalam' => 0,
                'mobil' => 0,
                'motor' => 0,
                'bus' => 0,
            ];
        }
    }
}