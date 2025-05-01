<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Log;

class DetectLicensePlate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $imagePath;
    protected $callbackUrl;

    public function __construct($imagePath, $callbackUrl)
    {
        $this->imagePath = $imagePath;
        $this->callbackUrl = $callbackUrl;
    }

    public function handle()
    {
        $pythonScript = base_path('python/detect_plate_yolo_easyocr.py');
        $pythonPath = base_path('python/venv/Scripts/python.exe');
        
        $process = new Process([$pythonPath, $pythonScript, $this->imagePath]);
        $process->setTimeout(600);
        $process->run();
        
        if ($process->isSuccessful()) {
            $output = $process->getOutput();
            $result = json_decode($output, true);
            
            // Call back to frontend with result
            // Implement webhook or save to database
        }
    }
}