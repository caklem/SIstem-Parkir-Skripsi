<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class YoloPlateDetectionController extends Controller
{
    public function detect(Request $request)
    {
        // Tingkatkan batas waktu eksekusi PHP
        ini_set('max_execution_time', 300);
        
        try {
            // Validasi input
            $request->validate([
                'image' => 'required|string',
            ]);
            
            // Decode base64 image
            $image = $request->input('image');
            $image = str_replace('data:image/jpeg;base64,', '', $image);
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            
            // Buat direktori temp jika belum ada
            $tempPath = storage_path('app/temp');
            if (!file_exists($tempPath)) {
                mkdir($tempPath, 0755, true);
            }
            
            // Simpan gambar ke file sementara
            $fileName = 'plate_' . time() . '_' . Str::random(10) . '.jpg';
            $filePath = $tempPath . '/' . $fileName;
            file_put_contents($filePath, base64_decode($image));
            
            // Log informasi
            $filesize = filesize($filePath);
            Log::info('Image saved for plate detection', [
                'path' => $filePath, 
                'size' => $filesize
            ]);
            
            // Cek ukuran file - gambar terlalu kecil mungkin tidak valid
            if ($filesize < 10000) { // 10KB
                Log::warning('Image file too small, possibly invalid', ['size' => $filesize]);
                return response()->json([
                    'success' => false,
                    'message' => 'Gambar terlalu kecil atau tidak valid. Silakan ambil ulang dengan jelas.'
                ], 400);
            }
            
            // Path ke script Python
            $pythonScript = base_path('python/accurate_plate_detection.py');
            $pythonPath = $this->getPythonPath();
            
            Log::info('Running plate detection script', [
                'script' => $pythonScript,
                'python' => $pythonPath,
                'image' => $filePath
            ]);
            
            // Execute Python script dengan timeout yang cukup
            $process = new Process([
                $pythonPath,
                $pythonScript,
                $filePath
            ]);
            
            $process->setTimeout(180); // 3 menit timeout
            $process->run();
            
            // Hapus file gambar setelah diproses
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
            
            // Cek hasil proses
            if (!$process->isSuccessful()) {
                Log::error('Plate detection process failed', [
                    'error' => $process->getErrorOutput(),
                    'exitCode' => $process->getExitCode()
                ]);
                
                throw new ProcessFailedException($process);
            }
            
            // Ambil output
            $output = $process->getOutput();
            $result = json_decode($output, true);
            
            Log::info('Plate detection result', [
                'result' => $result
            ]);
            
            // Cek validitas output
            if (!$result || !isset($result['success'])) {
                Log::error('Invalid detection output', ['output' => $output]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid detection result format. Harap coba lagi.'
                ], 500);
            }
            
            // Jika deteksi gagal, teruskan pesan error
            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Tidak dapat mendeteksi plat nomor. Pastikan gambar jelas dan plat terlihat.'
                ], 400);
            }
            
            // Deteksi sukses, kembalikan hasil
            return response()->json([
                'success' => true,
                'plate_number' => $result['plate_number'],
                'confidence' => $result['confidence'],
                'method' => $result['method'] ?? 'detection',
                'processing_time' => $result['processing_time'] ?? null
            ]);
            
        } catch (ProcessFailedException $e) {
            Log::error('Process execution failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Proses deteksi gagal. Silakan coba lagi.'
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error in plate detection: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error processing image: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get Python executable path
     */
    private function getPythonPath()
    {
        // Check for virtual environment first
        $venvPath = base_path('python/venv/Scripts/python.exe');
        if (file_exists($venvPath)) {
            return $venvPath;
        }
        
        // Check for system Python
        $systemPaths = [
            'C:\\Python39\\python.exe',
            'C:\\Python310\\python.exe',
            'C:\\Python311\\python.exe',
            'C:\\Program Files\\Python39\\python.exe',
            'C:\\Program Files\\Python310\\python.exe',
            'C:\\Program Files\\Python311\\python.exe',
        ];
        
        foreach ($systemPaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        // Default to just 'python' and rely on PATH
        return 'python';
    }
}