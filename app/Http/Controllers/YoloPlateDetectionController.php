<?php
// filepath: c:\xampp3\htdocs\sistem-parkir\app\Http\Controllers\YoloPlateDetectionController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class YoloPlateDetectionController extends Controller
{
    public function detectPlate(Request $request)
    {
        Log::info('YOLO+EasyOCR plate detection request received');
        
        // Validasi input
        $validated = $request->validate([
            'image' => 'required|string' // Base64 encoded image
        ]);
        
        try {
            // Buat direktori untuk temporary file jika belum ada
            $tempDir = storage_path('app/public/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            
            // Decode base64 image
            $image = $this->decodeBase64Image($validated['image']);
            
            // Verifikasi ukuran
            if (strlen($image) < 100) {
                Log::error('Decoded image too small: ' . strlen($image) . ' bytes');
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid image data (too small)'
                ], 400);
            }
            
            // Simpan gambar untuk diproses
            $filename = 'plate_' . Str::random(10) . '.jpg';
            $path = $tempDir . '/' . $filename;
            file_put_contents($path, $image);
            
            // Verifikasi file gambar valid
            if (!file_exists($path) || filesize($path) < 100) {
                Log::error('Saved image invalid or too small: ' . filesize($path) . ' bytes');
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save valid image'
                ], 500);
            }
            
            Log::info('Image saved for processing: ' . $path);
            
            // Menggunakan path absolut ke Python dalam virtual environment
            $pythonPath = base_path('venv_yolo\Scripts\python.exe');
            $pythonScript = base_path('python/detect_plate_yolo_easyocr.py');
            
            // Verifikasi paths
            if (!file_exists($pythonPath)) {
                Log::error('Python not found at: ' . $pythonPath);
                return response()->json([
                    'success' => false,
                    'message' => 'Python interpreter not found'
                ], 500);
            }
            
            if (!file_exists($pythonScript)) {
                Log::error('Python script not found at: ' . $pythonScript);
                return response()->json([
                    'success' => false,
                    'message' => 'Python script not found'
                ], 500);
            }
            
            // Jalankan script dengan shell_exec
            $command = "\"$pythonPath\" \"$pythonScript\" \"$path\" 2>&1";
            
            Log::info('Executing command: ' . $command);
            $output = shell_exec($command);
            Log::info('Raw Python script output: ' . $output);
            
            // Periksa jika output kosong
            if (empty($output)) {
                Log::error('Python script produced no output');
                return response()->json([
                    'success' => false,
                    'message' => 'Python script execution failed (no output)'
                ], 500);
            }
            
            // Parse output sebagai JSON
            $result = json_decode($output, true);
            
            // Check if JSON parsing failed
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Failed to parse JSON: ' . json_last_error_msg());
                Log::error('Raw output: ' . $output);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to parse script output as JSON',
                    'raw_output' => $output
                ], 500);
            }
            
            // Hapus file temporary
            if (file_exists($path)) {
                unlink($path);
            }
            
            // Hapus file hasil preprocessing (debug images)
            $filePattern = str_replace('.jpg', '_*.jpg', $path);
            foreach (glob($filePattern) as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
            
            // Return hasil
            return response()->json($result);
            
        } catch (\Exception $e) {
            Log::error('YOLO plate detection error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    private function decodeBase64Image($base64String)
    {
        // Remove data URL header if present
        if (strpos($base64String, ';base64,') !== false) {
            list(, $base64String) = explode(';base64,', $base64String);
        }
        
        return base64_decode($base64String);
    }
}   