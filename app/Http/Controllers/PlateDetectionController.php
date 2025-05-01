<?php
// filepath: c:\xampp3\htdocs\sistem-parkir\app\Http\Controllers\PlateDetectionController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class PlateDetectionController extends Controller
{
    /**
     * Detect license plate from image
     */
    public function detect(Request $request)
    {
        // Log request for debugging
        Log::info('Plate detection request received', [
            'has_image' => $request->has('image'),
            'image_size' => $request->has('image') ? strlen($request->input('image')) : 0
        ]);
        
        try {
            // Validasi input
            if (!$request->has('image') || empty($request->input('image'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gambar tidak ditemukan dalam request'
                ], 400);
            }
            
            // Decode base64 image
            $image = $request->input('image');
            $image = str_replace('data:image/jpeg;base64,', '', $image);
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            
            // Create temp directory if it doesn't exist
            $tempPath = storage_path('app/temp');
            if (!file_exists($tempPath)) {
                mkdir($tempPath, 0755, true);
            }
            
            // Save image to a temporary file
            $fileName = 'plate_' . time() . '_' . Str::random(10) . '.jpg';
            $filePath = $tempPath . '/' . $fileName;
            file_put_contents($filePath, base64_decode($image));
            
            // Log info
            $filesize = filesize($filePath);
            Log::info('Image saved for plate detection', [
                'path' => $filePath, 
                'size' => $filesize
            ]);
            
            // SIMPLIFIED: Return mock data for initial testing
            // return response()->json([
            //     'success' => true,
            //     'plate_number' => 'B 1234 XYZ',
            //     'confidence' => 0.95,
            //     'method' => 'test_response',
            //     'processing_time' => '0.1 seconds'
            // ]);
            
            // COMMENTED OUT FOR INITIAL TESTING - Uncomment after fixing connectivity
            
            // Path to Python script
            $pythonScript = base_path('python/plate_detection_opencv.py');
            $pythonPath = $this->getPythonPath();
            
            // Execute Python script
            $process = new Process([
                $pythonPath,
                $pythonScript,
                $filePath
            ]);
            
            $process->setTimeout(180);
            $process->run();
            
            // Check process result
            if (!$process->isSuccessful()) {
                Log::error('Plate detection process failed', [
                    'error' => $process->getErrorOutput(),
                    'exitCode' => $process->getExitCode()
                ]);
                
                throw new ProcessFailedException($process);
            }
            
            // Get output
            $output = $process->getOutput();
            $result = json_decode($output, true);
            
            // Return result
            return response()->json($result);
            
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
     * Show a debug image
     */
    public function showDebugImage($filename)
    {
        $path = storage_path('app/temp/' . $filename);
        
        if (!file_exists($path)) {
            abort(404, 'Image not found');
        }
        
        return response()->file($path);
    }
    
    /**
     * Get Python executable path
     */
    private function getPythonPath()
    {
        // Cek di virtual environment
        $venvPath = base_path('python/venv_ocr/Scripts/python.exe');
        if (file_exists($venvPath)) {
            return $venvPath;
        }
        
        // Cek beberapa lokasi instalasi Python umum di Windows
        $possiblePaths = [
            'C:\Python39\python.exe',
            'C:\Python310\python.exe',
            'C:\Python311\python.exe',
            'C:\Program Files\Python39\python.exe',
            'C:\Program Files\Python310\python.exe',
            'C:\Program Files\Python311\python.exe',
            'C:\Users\Admin\AppData\Local\Programs\Python\Python39\python.exe',
            'C:\Users\Admin\AppData\Local\Programs\Python\Python310\python.exe',
            'C:\Users\Admin\AppData\Local\Programs\Python\Python311\python.exe',
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        // Gunakan python dari PATH
        return 'python';
    }

    
}