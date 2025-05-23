<?php

namespace App\Services;

class ValidationService
{
    /**
     * Validasi format plat nomor Indonesia.
     *
     * @param string $platNomor
     * @return bool
     */
    public function isValidPlatNomor(string $platNomor): bool
    {
        // Bersihkan plat nomor
        $platNomor = trim(strtoupper($platNomor));
        $platNomor = preg_replace('/[^A-Z0-9\s]/', '', $platNomor);
        
        // Pola plat nomor Indonesia: 1-2 huruf, diikuti 1-4 angka, diikuti 0-3 huruf
        // Contoh: B 1234 CD, AB 123 XYZ, B 1 A
        $pattern = '/^[A-Z]{1,2}\s?[0-9]{1,4}\s?[A-Z]{0,3}$/';
        
        return (bool) preg_match($pattern, $platNomor);
    }
    
    /**
     * Validasi format nomor kartu.
     *
     * @param string $nomorKartu
     * @return bool
     */
    public function isValidNomorKartu(string $nomorKartu): bool
    {
        // Format kartu parkir: A-XXXXXX (huruf diikuti 6 digit angka)
        $pattern = '/^[A-Z]-[0-9]{6}$/i';
        
        return (bool) preg_match($pattern, $nomorKartu);
    }
    
    /**
     * Validasi jenis kendaraan.
     *
     * @param string $jenisKendaraan
     * @return bool
     */
    public function isValidJenisKendaraan(string $jenisKendaraan): bool
    {
        $validTypes = ['Mobil', 'Motor', 'Bus'];
        
        return in_array($jenisKendaraan, $validTypes);
    }
}