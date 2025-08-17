#!/usr/bin/env python3
"""
Backend setup script for SmartPath AI
This script ensures all dependencies are installed in the backend environment
"""
import subprocess
import sys
import os
import importlib.util

def check_module_installed(module_name):
    """Check if a module is installed"""
    spec = importlib.util.find_spec(module_name)
    return spec is not None

def install_package(package):
    """Install a package using pip"""
    try:
        print(f"📦 Installing {package}...")
        subprocess.check_call([sys.executable, "-m", "pip", "install", package], 
                            stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
        return True
    except subprocess.CalledProcessError as e:
        print(f"❌ Failed to install {package}: {e}")
        return False

def install_from_requirements():
    """Install all packages from requirements.txt"""
    requirements_file = os.path.join(os.path.dirname(__file__), "requirements.txt")
    
    if not os.path.exists(requirements_file):
        print("❌ requirements.txt not found in backend directory")
        return False
    
    try:
        print("📋 Installing from requirements.txt...")
        subprocess.check_call([sys.executable, "-m", "pip", "install", "-r", requirements_file])
        print("✅ All requirements installed successfully!")
        return True
    except subprocess.CalledProcessError as e:
        print(f"❌ Failed to install from requirements.txt: {e}")
        return False

def verify_critical_modules():
    """Verify that critical modules are installed"""
    critical_modules = {
        'fastapi': 'FastAPI web framework',
        'uvicorn': 'ASGI server',
        'pandas': 'Data processing',
        'google.generativeai': 'Google AI',
        'sentence_transformers': 'AI embeddings',
        'fitz': 'PDF processing (PyMuPDF)',
        'streamlit': 'Frontend framework'
    }
    
    print("\n🔍 Verifying critical modules...")
    all_good = True
    
    for module, description in critical_modules.items():
        if check_module_installed(module):
            print(f"✅ {module} - {description}")
        else:
            print(f"❌ {module} - {description} (MISSING)")
            all_good = False
    
    return all_good

def setup_environment():
    """Set up the backend environment"""
    print("🚀 SmartPath AI Backend Setup")
    print("=" * 50)
    
    # Check if pip is available
    try:
        subprocess.check_call([sys.executable, "-m", "pip", "--version"], 
                            stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
        print("✅ pip is available")
    except subprocess.CalledProcessError:
        print("❌ pip is not available. Please install pip first.")
        return False
    
    # Install from requirements
    if not install_from_requirements():
        print("⚠️ Some packages may not have installed correctly")
    
    # Verify installation
    if verify_critical_modules():
        print("\n🎉 Backend setup completed successfully!")
        print("🔧 All critical modules are installed and ready")
        return True
    else:
        print("\n⚠️ Some critical modules are missing")
        print("💡 Try running: pip install -r backend/requirements.txt")
        return False

def test_backend_functionality():
    """Test if backend modules work correctly"""
    print("\n🧪 Testing backend functionality...")
    
    try:
        # Test imports
        from config import GOOGLE_API_KEY
        from match_engine import match_jobs
        from skill_detector import extract_skills
        from translator import translate_text
        from course_recommender import recommend_courses
        
        print("✅ All backend modules imported successfully")
        
        # Test basic functionality
        test_text = "I am a Python developer with SQL experience"
        skills = extract_skills(test_text)
        print(f"✅ Skill extraction works: {len(skills)} skills found")
        
        jobs = match_jobs(test_text)
        print(f"✅ Job matching works: {len(jobs)} matches found")
        
        translation = translate_text("Hello", "rw")
        print(f"✅ Translation works: {translation}")
        
        courses = recommend_courses("Python")
        print(f"✅ Course recommendation works: {len(courses)} courses found")
        
        print("🎉 All backend functionality tests passed!")
        return True
        
    except Exception as e:
        print(f"❌ Backend functionality test failed: {e}")
        return False

if __name__ == "__main__":
    # Change to backend directory
    backend_dir = os.path.dirname(os.path.abspath(__file__))
    os.chdir(backend_dir)
    
    if setup_environment():
        test_backend_functionality()
        print("\n" + "=" * 50)
        print("🚀 Backend is ready!")
        print("💡 To start the server: python -m uvicorn main:app --reload")
    else:
        print("\n" + "=" * 50)
        print("❌ Backend setup incomplete")
        print("💡 Please check the error messages above")
