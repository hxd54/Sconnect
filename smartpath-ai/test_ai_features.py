#!/usr/bin/env python3
"""
Test script to demonstrate SmartPath AI v2.0 enhanced features with Google AI
"""
import requests
import json

BASE_URL = "http://localhost:8000"

def test_api_health():
    """Test if the API is running and shows AI features"""
    print("🔍 Testing API Health...")
    try:
        response = requests.get(f"{BASE_URL}/")
        if response.status_code == 200:
            data = response.json()
            print(f"✅ API Status: {data['message']}")
            print(f"🤖 Powered by: {data['powered_by']}")
            print(f"🎯 Features: {', '.join(data['features'])}")
            return True
        else:
            print(f"❌ API Error: {response.status_code}")
            return False
    except Exception as e:
        print(f"❌ Connection Error: {e}")
        return False

def test_skill_extraction():
    """Test AI-powered skill extraction"""
    print("\n🧠 Testing AI Skill Extraction...")
    test_text = "I am a software engineer with 5 years of experience in Python, JavaScript, React, and SQL. I have worked on machine learning projects using TensorFlow and have experience with AWS cloud services."
    
    try:
        response = requests.get(f"{BASE_URL}/skills/extract", params={"text": test_text})
        if response.status_code == 200:
            data = response.json()
            print(f"✅ Extracted {data['skill_count']} skills:")
            for skill in data['extracted_skills']:
                print(f"   • {skill}")
            print(f"🤖 AI-powered: {data['ai_powered']}")
            return True
        else:
            print(f"❌ Skill extraction failed: {response.status_code}")
            return False
    except Exception as e:
        print(f"❌ Error: {e}")
        return False

def test_translation():
    """Test AI-powered translation"""
    print("\n🌍 Testing AI Translation...")
    test_text = "Data Analyst"
    
    try:
        response = requests.post(f"{BASE_URL}/translate", params={
            "text": test_text,
            "target_lang": "rw"
        })
        if response.status_code == 200:
            data = response.json()
            print(f"✅ Translation successful:")
            print(f"   Original: {data['original_text']}")
            print(f"   Translated: {data['translated_text']}")
            print(f"   Source Language: {data['source_language']}")
            print(f"   Target Language: {data['target_language']}")
            print(f"🤖 AI-powered: {data['ai_powered']}")
            return True
        else:
            print(f"❌ Translation failed: {response.status_code}")
            return False
    except Exception as e:
        print(f"❌ Error: {e}")
        return False

def test_course_recommendations():
    """Test course recommendations"""
    print("\n📚 Testing Course Recommendations...")
    skill = "Python"
    
    try:
        response = requests.get(f"{BASE_URL}/recommend", params={"skill": skill})
        if response.status_code == 200:
            data = response.json()
            print(f"✅ Found {data['count']} courses for {data['skill']}:")
            for course in data['courses']:
                print(f"   • {course['course_name']}: {course['link']}")
            return True
        else:
            print(f"❌ Course recommendation failed: {response.status_code}")
            return False
    except Exception as e:
        print(f"❌ Error: {e}")
        return False

def main():
    """Run all tests"""
    print("🚀 SmartPath AI v2.0 - Enhanced Features Test")
    print("=" * 60)
    
    tests = [
        test_api_health,
        test_skill_extraction,
        test_translation,
        test_course_recommendations
    ]
    
    passed = 0
    total = len(tests)
    
    for test in tests:
        if test():
            passed += 1
    
    print("\n" + "=" * 60)
    print(f"📊 Test Results: {passed}/{total} tests passed")
    
    if passed == total:
        print("🎉 All tests passed! SmartPath AI v2.0 is working perfectly with Google AI!")
        print("\n💡 Try uploading a CV file to see the full AI-powered analysis in action!")
        print("📱 Frontend: http://localhost:8501")
        print("🔧 API Docs: http://localhost:8000/docs")
    else:
        print("⚠️  Some tests failed. Check the backend server and Google API configuration.")

if __name__ == "__main__":
    main()
