<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Parkir;

class PlateOCRController extends Controller
{
    /**
     * Validasi hasil OCR plat nomor
     */
    public function validatePlate(Request $request)
    {
        $plateNumber = $request->input('plate_number');
        
        // Format plat nomor
        $formattedPlate = $this->formatPlateNumber($plateNumber);
        
        // Validasi format plat nomor Indonesia
        $isValid = preg_match('/^[A-Z]{1,2}\s*\d{1,4}\s*[A-Z]{1,3}$/', str_replace(' ', '', $formattedPlate));
        
        // Cek apakah plat sudah ada di parkiran
        $existingEntry = Parkir::where('plat_nomor', $formattedPlate)
                              ->whereNull('waktu_keluar')
                              ->first();
        
        return response()->json([
            'success' => true,
            'is_valid' => $isValid,
            'is_already_parked' => $existingEntry ? true : false,
            'formatted_plate' => $formattedPlate
        ]);
    }
    
    /**
     * Format plat nomor sesuai standar
     */
    private function formatPlateNumber($plateNumber)
    {
        // Hapus spasi dan konversi ke uppercase
        $plate = preg_replace('/\s+/', '', strtoupper($plateNumber));
        
        // Format ke standar plat nomor Indonesia
        if (preg_match('/^([A-Z]{1,2})(\d{1,4})([A-Z]{1,3})$/', $plate, $matches)) {
            return $matches[1] . ' ' . $matches[2] . ' ' . $matches[3];
        }
        
        return $plateNumber;
    }
}