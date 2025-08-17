#!/usr/bin/env python3
"""
Verify that all backend modules are properly organized and working
"""
import os
import sys
import importlib.util

def check_module_exists(module_name, description=""):
    """Check if a module exists and can be imported"""
    try:
        spec = importlib.util.find_spec(module_name)
        if spec is not None:
            print(f"✅ {module_name} - {description}")
            return True
        else:
            print(f"❌ {module_name} - {description} (NOT FOUND)")
            return False
    except Exception as e:
        print(f"❌ {module_name} - {description} (ERROR: {e})")
        return False

def verify_backend_files():
    """Verify that all backend files exist"""
    backend_dir = os.path.dirname(os.path.abspath(__file__))
    required_files = {
        'main.py': 'FastAPI application',
        'config.py': 'Configuration settings',
        'match_engine.py': 'Job matching engine',
        'skill_detector.py': 'Skill extraction and analysis',
        'translator.py': 'Translation services',
        'course_recommender.py': 'Course recommendations',
        'jobs_dataset.csv': 'Jobs database',
        'courses_dataset.csv': 'Courses database'
    }
    
    print("📁 Checking backend files...")
    all_files_exist = True
    
    for filename, description in required_files.items():
        filepath = os.path.join(backend_dir, filename)
        if os.path.exists(filepath):
            print(f"✅ {filename} - {description}")
        else:
            print(f"❌ {filename} - {description} (MISSING)")
            all_files_exist = False
    
    return all_files_exist

def verify_python_modules():
    """Verify that required Python modules are installed"""
    print("\n🐍 Checking Python modules...")
    
    modules = {
        'fastapi': 'Web framework',
        'uvicorn': 'ASGI server',
        'pandas': 'Data processing',
        'google.generativeai': 'Google AI',
        'sentence_transformers': 'AI embeddings',
        'fitz': 'PDF processing',
        'streamlit': 'Frontend framework',
        'requests': 'HTTP client',
        'torch': 'PyTorch ML framework'
    }
    
    all_modules_ok = True
    for module, description in modules.items():
        if not check_module_exists(module, description):
            all_modules_ok = False
    
    return all_modules_ok

def test_backend_imports():
    """Test importing backend modules"""
    print("\n🔧 Testing backend module imports...")
    
    # Add backend directory to path
    backend_dir = os.path.dirname(os.path.abspath(__file__))
    if backend_dir not in sys.path:
        sys.path.insert(0, backend_dir)
    
    modules_to_test = [
        ('config', 'Configuration module'),
        ('match_engine', 'Job matching module'),
        ('skill_detector', 'Skill detection module'),
        ('translator', 'Translation module'),
        ('course_recommender', 'Course recommendation module')
    ]
    
    all_imports_ok = True
    for module_name, description in modules_to_test:
        try:
            module = importlib.import_module(module_name)
            print(f"✅ {module_name} - {description}")
        except Exception as e:
            print(f"❌ {module_name} - {description} (ERROR: {e})")
            all_imports_ok = False
    
    return all_imports_ok

def test_functionality():
    """Test basic functionality of backend modules"""
    print("\n🧪 Testing functionality...")
    
    try:
        # Add backend to path
        backend_dir = os.path.dirname(os.path.abspath(__file__))
        if backend_dir not in sys.path:
            sys.path.insert(0, backend_dir)
        
        # Test imports
        from config import GOOGLE_API_KEY
        from match_engine import match_jobs
        from skill_detector import extract_skills
        from translator import translate_text
        from course_recommender import recommend_courses
        
        print("✅ All modules imported successfully")
        
        # Test basic functionality
        test_cv = "I am a Python developer with experience in web development and data analysis."
        
        # Test skill extraction
        skills = extract_skills(test_cv)
        print(f"✅ Skill extraction: Found {len(skills)} skills")
        
        # Test job matching
        jobs = match_jobs(test_cv)
        print(f"✅ Job matching: Found {len(jobs)} job matches")
        
        # Test translation
        translated = translate_text("Hello", "rw")
        print(f"✅ Translation: 'Hello' -> '{translated}'")
        
        # Test course recommendation
        courses = recommend_courses("Python")
        print(f"✅ Course recommendation: Found {len(courses)} courses")
        
        print("🎉 All functionality tests passed!")
        return True
        
    except Exception as e:
        print(f"❌ Functionality test failed: {e}")
        return False

def main():
    """Main verification function"""
    print("🔍 SmartPath AI Backend Module Verification")
    print("=" * 60)
    
    # Check files
    files_ok = verify_backend_files()
    
    # Check Python modules
    modules_ok = verify_python_modules()
    
    # Test imports
    imports_ok = test_backend_imports()
    
    # Test functionality
    functionality_ok = test_functionality()
    
    print("\n" + "=" * 60)
    print("📊 Verification Summary:")
    print(f"📁 Backend files: {'✅ OK' if files_ok else '❌ Issues'}")
    print(f"🐍 Python modules: {'✅ OK' if modules_ok else '❌ Issues'}")
    print(f"🔧 Module imports: {'✅ OK' if imports_ok else '❌ Issues'}")
    print(f"🧪 Functionality: {'✅ OK' if functionality_ok else '❌ Issues'}")
    
    if all([files_ok, modules_ok, imports_ok, functionality_ok]):
        print("\n🎉 All backend modules are properly organized and working!")
        print("🚀 Ready to start the server: python -m uvicorn main:app --reload")
        return True
    else:
        print("\n⚠️ Some issues found. Please check the details above.")
        return False

if __name__ == "__main__":
    main()
