<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class TestYoloPlateDetection extends Command
{
    protected $signature = 'test:yolo-plate {image?}';
    protected $description = 'Test YOLO plate detection with a sample image';

    public function handle()
    {
        $this->info('Testing YOLO plate detection...');
        
        // Get image path
        $imagePath = $this->argument('image');
        
        if (!$imagePath) {
            // Check samples directory
            $samplesDir = base_path('python/samples');
            
            if (!file_exists($samplesDir)) {
                mkdir($samplesDir, 0755, true);
                $this->error('Samples directory not found. Created one at: ' . $samplesDir);
                $this->info('Add some images to this directory and try again.');
                return 1;
            }
            
            // Get list of images
            $images = glob($samplesDir . '/*.{jpg,jpeg,png}', GLOB_BRACE);
            
            if (empty($images)) {
                $this->error('No sample images found in: ' . $samplesDir);
                return 1;
            }
            
            $imagePath = $images[0];
            $this->info('Using sample image: ' . $imagePath);
        } else if (!file_exists($imagePath)) {
            $this->error('Image file not found: ' . $imagePath);
            return 1;
        }
        
        // Get Python script path
        $pythonScript = base_path('python/simple_detect.py');
        
        if (!file_exists($pythonScript)) {
            $this->error('Python script not found: ' . $pythonScript);
            return 1;
        }
        
        // Get Python path
        $pythonPath = $this->getPythonPath();
        $this->info('Using Python: ' . $pythonPath);
        
        // Execute Python script
        $this->info('Running detection...');
        $process = new Process([$pythonPath, $pythonScript, $imagePath]);
        $process->setTimeout(600);
        
        $this->info('Command: ' . $process->getCommandLine());
        
        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                $this->error($buffer);
            } else {
                $this->info($buffer);
            }
        });
        
        // Output results
        if ($process->isSuccessful()) {
            $output = $process->getOutput();
            $this->info('Detection complete!');
            $this->info($output);
            
            $result = json_decode($output, true);
            if ($result && isset($result['success']) && $result['success']) {
                $this->info('Found plate: ' . $result['plate_number']);
                $this->info('Confidence: ' . $result['confidence']);
            }
            
            return 0;
        } else {
            $this->error('Detection failed!');
            $this->error($process->getErrorOutput());
            return 1;
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
        
        // Default to 'python' and rely on PATH
        return 'python';
    }
}