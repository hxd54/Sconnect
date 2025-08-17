#!/usr/bin/env python3
"""
Test script to verify the backend functionality
"""
import sys
import os

# Add backend directory to path
sys.path.append(os.path.join(os.path.dirname(__file__), 'backend'))

def test_imports():
    """Test if all required modules can be imported"""
    try:
        from match_engine import match_jobs
        print("✅ match_engine imported successfully")
    except ImportError as e:
        print(f"❌ Failed to import match_engine: {e}")
        return False
    
    try:
        from course_recommender import recommend_courses
        print("✅ course_recommender imported successfully")
    except ImportError as e:
        print(f"❌ Failed to import course_recommender: {e}")
        return False
    
    try:
        from translator import translate_text
        print("✅ translator imported successfully")
    except ImportError as e:
        print(f"❌ Failed to import translator: {e}")
        return False
    
    try:
        from skill_detector import detect_skill_gaps, extract_skills
        print("✅ skill_detector imported successfully")
    except ImportError as e:
        print(f"❌ Failed to import skill_detector: {e}")
        return False
    
    return True

def test_functionality():
    """Test basic functionality"""
    try:
        # Test skill extraction
        from skill_detector import extract_skills
        test_cv = "I have experience with Python, SQL, and data analysis. I also know HTML and CSS."
        skills = extract_skills(test_cv)
        print(f"✅ Skill extraction works: {skills}")
        
        # Test job matching
        from match_engine import match_jobs
        jobs = match_jobs(test_cv)
        print(f"✅ Job matching works: Found {len(jobs)} matches")
        
        # Test skill gap detection
        from skill_detector import detect_skill_gaps
        gaps = detect_skill_gaps(test_cv, jobs)
        print(f"✅ Skill gap detection works: {len(gaps)} jobs analyzed")
        
        # Test course recommendation
        from course_recommender import recommend_courses
        courses = recommend_courses("Python")
        print(f"✅ Course recommendation works: Found {len(courses)} courses")
        
        # Test translation
        from translator import translate_text
        translated = translate_text("Data Analyst", "rw")
        print(f"✅ Translation works: {translated}")
        
        return True
    except Exception as e:
        print(f"❌ Functionality test failed: {e}")
        return False

if __name__ == "__main__":
    print("Testing SmartPath AI Backend...")
    print("=" * 50)
    
    if test_imports():
        print("\n" + "=" * 50)
        print("Testing functionality...")
        if test_functionality():
            print("\n🎉 All tests passed! Backend is working correctly.")
        else:
            print("\n❌ Some functionality tests failed.")
    else:
        print("\n❌ Import tests failed. Please install missing dependencies.")
