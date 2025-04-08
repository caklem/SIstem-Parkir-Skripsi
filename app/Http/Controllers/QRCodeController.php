<?php

namespace App\Http\Controllers;

use App\Models\QRCode;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QRGenerator;

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
}
