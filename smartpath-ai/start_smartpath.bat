@echo off
echo SmartPath AI Launcher
echo =====================

echo Installing dependencies...
python setup_and_run.py

echo.
echo Starting SmartPath AI...
python setup_and_run.py --start

echo.
echo SmartPath AI is now running!
echo Frontend: http://localhost:8501
echo Backend API: http://localhost:8000
echo.
echo Press any key to exit...
pause
