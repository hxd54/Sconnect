@echo off
echo ========================================
echo Starting SmartPath AI for SConnect
echo ========================================
echo.

cd /d "C:\xampp\htdocs\Sconnect\smartpath-ai"

echo Current directory: %CD%
echo.

echo Checking if Python is available...
python --version
if %errorlevel% neq 0 (
    echo ERROR: Python is not installed or not in PATH
    echo Please install Python from https://python.org
    pause
    exit /b 1
)

echo.
echo Installing/checking dependencies...
python -m pip install fastapi uvicorn pandas streamlit sentence-transformers torch requests PyMuPDF google-generativeai

echo.
echo Starting SmartPath AI Backend Server...
echo Server will run on: http://localhost:8000
echo.

python -m uvicorn backend.main:app --reload --host 0.0.0.0 --port 8000

echo.
echo SmartPath AI server stopped.
pause
