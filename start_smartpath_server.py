#!/usr/bin/env python3
"""
Quick Start Script for SmartPath AI Server
This script starts the SmartPath AI backend server on port 8000
"""

import os
import sys
import subprocess
import time

def check_python_version():
    """Check if Python version is compatible"""
    if sys.version_info < (3, 7):
        print("❌ Python 3.7 or higher is required")
        print(f"Current version: {sys.version}")
        return False
    print(f"✅ Python version: {sys.version}")
    return True

def install_dependencies():
    """Install required dependencies"""
    print("📦 Installing dependencies...")
    
    dependencies = [
        "fastapi",
        "uvicorn[standard]", 
        "pandas",
        "sentence-transformers",
        "torch",
        "requests",
        "PyMuPDF",
        "google-generativeai"
    ]
    
    for dep in dependencies:
        try:
            print(f"  Installing {dep}...")
            subprocess.check_call([
                sys.executable, "-m", "pip", "install", dep
            ], stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
            print(f"  ✅ {dep} installed")
        except subprocess.CalledProcessError:
            print(f"  ⚠️ Failed to install {dep}, but continuing...")
    
    print("✅ Dependencies installation completed")

def start_server():
    """Start the SmartPath AI server"""
    print("\n🚀 Starting SmartPath AI Backend Server...")
    print("📍 Server URL: http://localhost:8000")
    print("📋 API Documentation: http://localhost:8000/docs")
    print("🔄 Chat Endpoint: http://localhost:8000/chat")
    print("📄 CV Analysis: http://localhost:8000/analyze")
    print("🎯 Job Matching: http://localhost:8000/match")
    print("\n⚠️ Keep this window open to keep the server running!")
    print("=" * 60)
    
    try:
        # Change to smartpath-ai directory
        smartpath_dir = os.path.join(os.path.dirname(__file__), "smartpath-ai")
        if os.path.exists(smartpath_dir):
            os.chdir(smartpath_dir)
            print(f"📁 Changed to directory: {os.getcwd()}")
        
        # Start the server
        subprocess.run([
            sys.executable, "-m", "uvicorn", 
            "backend.main:app", 
            "--reload", 
            "--host", "0.0.0.0", 
            "--port", "8000"
        ])
        
    except KeyboardInterrupt:
        print("\n\n🛑 Server stopped by user")
    except Exception as e:
        print(f"\n❌ Server error: {e}")
        print("\nTroubleshooting:")
        print("1. Make sure you're in the correct directory")
        print("2. Check if backend/main.py exists")
        print("3. Verify all dependencies are installed")
        print("4. Try running: python -m uvicorn backend.main:app --port 8000")

def main():
    print("🤖 SmartPath AI Server Starter")
    print("=" * 40)
    
    if not check_python_version():
        input("Press Enter to exit...")
        return
    
    print("\n📍 Current directory:", os.getcwd())
    
    # Check if we're in the right directory
    if not os.path.exists("smartpath-ai"):
        print("⚠️ smartpath-ai directory not found")
        print("Make sure you're running this from the SConnect root directory")
        input("Press Enter to exit...")
        return
    
    try:
        install_dependencies()
        start_server()
    except Exception as e:
        print(f"❌ Error: {e}")
        input("Press Enter to exit...")

if __name__ == "__main__":
    main()
