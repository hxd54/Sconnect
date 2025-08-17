#!/usr/bin/env python3
"""
Setup and run script for SmartPath AI
This script will help install dependencies and start the application
"""
import subprocess
import sys
import os

def install_package(package):
    """Install a package using pip"""
    try:
        subprocess.check_call([sys.executable, "-m", "pip", "install", package])
        return True
    except subprocess.CalledProcessError:
        return False

def check_and_install_dependencies():
    """Check and install required dependencies"""
    required_packages = [
        "fastapi",
        "uvicorn",
        "pandas", 
        "streamlit",
        "sentence-transformers",
        "torch",
        "requests",
        "PyMuPDF"
    ]
    
    print("Checking and installing dependencies...")
    
    for package in required_packages:
        try:
            __import__(package.replace("-", "_"))
            print(f"✅ {package} is already installed")
        except ImportError:
            print(f"📦 Installing {package}...")
            if install_package(package):
                print(f"✅ {package} installed successfully")
            else:
                print(f"❌ Failed to install {package}")
                return False
    
    return True

def start_backend():
    """Start the FastAPI backend server"""
    print("\n🚀 Starting backend server...")
    try:
        os.chdir("backend")
        subprocess.Popen([sys.executable, "-m", "uvicorn", "main:app", "--reload", "--host", "0.0.0.0", "--port", "8000"])
        print("✅ Backend server started on http://localhost:8000")
        os.chdir("..")
        return True
    except Exception as e:
        print(f"❌ Failed to start backend: {e}")
        return False

def start_frontend():
    """Start the Streamlit frontend"""
    print("\n🎨 Starting frontend...")
    try:
        os.chdir("Frontend")
        subprocess.Popen([sys.executable, "-m", "streamlit", "run", "app.py"])
        print("✅ Frontend started on http://localhost:8501")
        os.chdir("..")
        return True
    except Exception as e:
        print(f"❌ Failed to start frontend: {e}")
        return False

def main():
    print("SmartPath AI Setup and Launch")
    print("=" * 50)
    
    # Check if pip is available
    try:
        subprocess.check_call([sys.executable, "-m", "pip", "--version"], 
                            stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
    except subprocess.CalledProcessError:
        print("❌ pip is not available. Please install pip first.")
        print("You can download get-pip.py from https://bootstrap.pypa.io/get-pip.py")
        print("Then run: python get-pip.py")
        return
    
    # Install dependencies
    if not check_and_install_dependencies():
        print("❌ Failed to install some dependencies. Please install them manually.")
        return
    
    print("\n" + "=" * 50)
    print("🎉 All dependencies installed successfully!")
    print("\nTo start the application:")
    print("1. Backend: cd backend && python -m uvicorn main:app --reload")
    print("2. Frontend: cd Frontend && python -m streamlit run app.py")
    print("\nOr run this script with --start to launch both automatically")
    
    if "--start" in sys.argv:
        start_backend()
        start_frontend()
        print("\n🎉 SmartPath AI is now running!")
        print("📱 Frontend: http://localhost:8501")
        print("🔧 Backend API: http://localhost:8000")

if __name__ == "__main__":
    main()
