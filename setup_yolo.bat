@echo off
echo Setting up YOLO + EasyOCR environment...

REM Check if Python is installed
python --version
if %ERRORLEVEL% NEQ 0 (
    echo Python not found. Please install Python 3.8 or 3.9
    exit /b 1
)

REM Create Python directory if it doesn't exist
if not exist python mkdir python
cd python

REM Create virtual environment
if not exist venv (
    echo Creating virtual environment...
    python -m venv venv
) else (
    echo Virtual environment already exists
)

REM Activate virtual environment
call venv\Scripts\activate

REM Install dependencies
echo Installing Python dependencies...
pip install torch torchvision opencv-python numpy easyocr pillow matplotlib

REM Clone YOLOv5 if not already cloned
if not exist yolov5 (
    echo Cloning YOLOv5 repository...
    git clone https://github.com/ultralytics/yolov5.git
    cd yolov5
    pip install -r requirements.txt
    cd ..
) else (
    echo YOLOv5 already cloned
)

REM Create samples directory
if not exist samples mkdir samples

REM Create temp directory in Laravel storage
if not exist ..\storage\app\temp mkdir ..\storage\app\temp

echo Setup complete!
echo.
echo You can now:
echo 1. Add sample images to the 'python/samples' directory
echo 2. Test the detection with: php artisan test:yolo-plate
echo 3. Start using the YOLO plate detection in your application