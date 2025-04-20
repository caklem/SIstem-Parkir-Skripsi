# Sistem OCR Plat Nomor dengan AI

Panduan untuk mengimplementasikan dan menggunakan sistem pengenalan plat nomor berbasis AI.

## Persiapan Lingkungan

### Prasyarat
- PHP 7.4+ dengan Laravel
- Python 3.7+
- Tesseract OCR
- TensorFlow 2.x

### Instalasi Tesseract OCR

#### Windows:
1. Unduh installer dari [https://github.com/UB-Mannheim/tesseract/wiki](https://github.com/UB-Mannheim/tesseract/wiki)
2. Jalankan installer dan centang "Add to PATH"
3. Pilih bahasa Inggris dan Indonesia (jika tersedia)

#### Linux:
```bash
sudo apt update
sudo apt install tesseract-ocr tesseract-ocr-eng tesseract-ocr-ind