<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QRCodeController extends Controller
{
    public function scanQR(Request $request)
    {
        try {
            $nomorKartu = $request->nomor_kartu;
            
            // Validasi nomor kartu
            if(empty($nomorKartu)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nomor kartu tidak valid'
                ]);
            }

            return response()->json([
                'success' => true,
                'nomor_kartu' => $nomorKartu
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    // Untuk testing - generate QR
    public function generateQR($nomorKartu)
    {
        return view('qrcode.generate', compact('nomorKartu'));
    }
}
