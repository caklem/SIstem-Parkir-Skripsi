<?php

namespace App\Http\Controllers;

use App\Models\QRCode;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QRGenerator;
use Illuminate\Support\Facades\Log;

class QRCodeController extends Controller
{
    public function scanQR(Request $request)
    {
        try {
            $request->validate([
                'nomor_kartu' => 'required|string|max:50'
            ]);

            $nomorKartu = trim($request->nomor_kartu);
            
            // Validasi format nomor kartu
            if (!preg_match('/^[A-Za-z0-9]+$/', $nomorKartu)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format nomor kartu tidak valid'
                ], 422);
            }

            // Cek keberadaan kartu di database
            $qrcode = QRCode::where('nomor_kartu', $nomorKartu)->first();
            
            if (!$qrcode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kartu tidak terdaftar'
                ], 404);
            }

            //jika kartu valid
            return response()->json([
                'success' => true,
                'nomor_kartu' => $nomorKartu,
                'scan_time' => now()->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        try {
            $qrcodes = QRCode::orderBy('created_at', 'desc')->paginate(10);
            return view('qrcode.list', compact('qrcodes'));
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function showGenerateForm()
    {
        return view('qrcode.generate');
    }

    public function generateQR(Request $request)
    {
        try {
            $request->validate([
                'nomorKartu' => 'required|string|regex:/^[A-Za-z0-9]+$/'
            ]);

            $nomorKartu = trim($request->nomorKartu);

            QRCode::create([
                'nomor_kartu' => $nomorKartu,
                'generated_at' => now()
            ]);

            $qrcode = QRGenerator::size(200)
                                ->margin(1)
                                ->errorCorrection('H')
                                ->generate($nomorKartu);

            return view('qrcode.print-card', compact('qrcode', 'nomorKartu'));

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal generate QR Code: ' . $e->getMessage());
        }
    }

    public function print($nomorKartu)
    {
        try {
            $qrcode = QRGenerator::size(200)
                                ->margin(1)
                                ->errorCorrection('H')
                                ->generate($nomorKartu);

            return view('qrcode.print-card', compact('qrcode', 'nomorKartu'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mencetak QR Code');
        }
    }

    public function delete($id)
    {
        try {
            $qrcode = QRCode::findOrFail($id);
            $qrcode->delete();
            return redirect()->route('qrcode.list')->with('success', 'QR Code berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus QR Code');
        }
    }

    // /**
    //  * Download QR Code sebagai file gambar
    //  *
    //  * @param string $nomorKartu
    //  * @return \Illuminate\Http\Response
    //  */
    // public function downloadQrCode($nomorKartu)
    // {
    //     // Cek apakah QR code tersedia di database
    //     $qrData = Qrcode::where('nomor_kartu', $nomorKartu)->first();
    //     if (!$qrData) {
    //         return redirect()->route('qrcode.list')->with('error', 'QR Code tidak ditemukan');
    //     }

    //     try {
    //         // Generate QR Code dengan setting yang optimal untuk download
    //         $qrcode = QrCode::format('png')
    //             ->size(500) // Ukuran lebih besar untuk kualitas lebih baik
    //             ->errorCorrection('H') // High error correction untuk ketahanan
    //             ->margin(10)
    //             ->backgroundColor(255, 255, 255)
    //             ->color(0, 0, 0)
    //             ->generate($nomorKartu);
                
    //         // Set header untuk download
    //         $headers = [
    //             'Content-Type' => 'image/png',
    //             'Content-Disposition' => 'attachment; filename="qrcode-' . $nomorKartu . '.png"',
    //             'Cache-Control' => 'no-cache, no-store, must-revalidate',
    //             'Pragma' => 'no-cache',
    //             'Expires' => '0'
    //         ];

    //         // Log aktivitas download
    //         Log::info('QR Code diunduh', ['nomor_kartu' => $nomorKartu, 'user' => auth()->id() ?? 'guest']);
            
    //         return response($qrcode, 200, $headers);
            
    //     } catch (\Exception $e) {
    //         Log::error('Error downloading QR Code', ['message' => $e->getMessage(), 'nomor_kartu' => $nomorKartu]);
    //         return redirect()->route('qrcode.list')->with('error', 'Terjadi kesalahan saat mengunduh QR Code');
    //     }
    // }
}
