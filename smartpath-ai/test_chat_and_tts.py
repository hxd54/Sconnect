#!/usr/bin/env python3
"""
Test the new chat and text-to-speech features
"""
import requests
import time

BASE_URL = "http://localhost:8000"

def test_tts_info():
    """Test TTS engine information"""
    print("🔊 Testing TTS Engine Info...")
    try:
        response = requests.get(f"{BASE_URL}/tts/info")
        if response.status_code == 200:
            data = response.json()
            print(f"✅ TTS Available: {data['available']}")
            print(f"🔧 Engine Type: {data.get('engine_type', 'Unknown')}")
            print(f"🌍 Supported Languages: {data.get('supports_languages', [])}")
            return data['available']
        else:
            print(f"❌ TTS Info Error: {response.status_code}")
            return False
    except Exception as e:
        print(f"❌ TTS Info Error: {e}")
        return False

def test_text_to_speech():
    """Test text-to-speech functionality"""
    print("\n🗣️ Testing Text-to-Speech...")
    try:
        test_text = "Hello! Welcome to SmartPath AI. I can help you analyze your CV and find suitable jobs."
        
        response = requests.post(f"{BASE_URL}/speak", data={
            "text": test_text,
            "language": "en"
        })
        
        if response.status_code == 200:
            data = response.json()
            print(f"✅ Speech Request: {data['spoken']}")
            print(f"📝 Text: {data['text'][:50]}...")
            print(f"🌍 Language: {data['language']}")
            return True
        else:
            print(f"❌ Speech Error: {response.status_code}")
            return False
    except Exception as e:
        print(f"❌ Speech Error: {e}")
        return False

def test_chat_suggestions():
    """Test chat suggestions"""
    print("\n💡 Testing Chat Suggestions...")
    try:
        response = requests.get(f"{BASE_URL}/chat/suggestions")
        if response.status_code == 200:
            data = response.json()
            suggestions = data['suggested_questions']
            print(f"✅ Found {len(suggestions)} suggestions:")
            for i, suggestion in enumerate(suggestions[:3], 1):
                print(f"   {i}. {suggestion}")
            return suggestions
        else:
            print(f"❌ Suggestions Error: {response.status_code}")
            return []
    except Exception as e:
        print(f"❌ Suggestions Error: {e}")
        return []

def test_chat_functionality():
    """Test AI chat functionality"""
    print("\n🤖 Testing AI Chat...")
    
    test_questions = [
        "What jobs am I most qualified for?",
        "What skills should I develop?",
        "How can I improve my career prospects?"
    ]
    
    for question in test_questions:
        try:
            print(f"\n❓ Question: {question}")
            
            response = requests.post(f"{BASE_URL}/chat", data={
                "message": question,
                "speak_response": False,
                "language": "en"
            })
            
            if response.status_code == 200:
                data = response.json()
                print(f"✅ Success: {data['success']}")
                print(f"🤖 Response: {data['ai_response'][:100]}...")
                print(f"🎯 Context Used: {data.get('context_used', False)}")
            else:
                print(f"❌ Chat Error: {response.status_code}")
                
        except Exception as e:
            print(f"❌ Chat Error: {e}")
        
        time.sleep(1)  # Brief pause between requests

def test_context_setting():
    """Test setting context for chat"""
    print("\n🎯 Testing Context Setting...")
    try:
        # Set context with sample data
        response = requests.post(f"{BASE_URL}/context/set", data={
            "cv_text": "I am a Python developer with 3 years of experience in web development and data analysis.",
            "user_name": "John Doe"
        })
        
        if response.status_code == 200:
            data = response.json()
            print(f"✅ Context Set: {data['message']}")
            print(f"🧠 Skills Count: {data['skills_count']}")
            print(f"🎯 Job Matches: {data['job_matches_count']}")
            print(f"👤 User Name: {data.get('user_name', 'Not set')}")
            return True
        else:
            print(f"❌ Context Error: {response.status_code}")
            return False
    except Exception as e:
        print(f"❌ Context Error: {e}")
        return False

def test_chat_with_context():
    """Test chat with context set"""
    print("\n🎯 Testing Chat with Context...")
    try:
        response = requests.post(f"{BASE_URL}/chat", data={
            "message": "What jobs am I qualified for based on my CV?",
            "speak_response": False,
            "language": "en"
        })
        
        if response.status_code == 200:
            data = response.json()
            print(f"✅ Context-aware response:")
            print(f"🤖 {data['ai_response'][:200]}...")
            print(f"🎯 Used Context: {data.get('context_used', False)}")
            return True
        else:
            print(f"❌ Context Chat Error: {response.status_code}")
            return False
    except Exception as e:
        print(f"❌ Context Chat Error: {e}")
        return False

def main():
    """Run all tests"""
    print("🚀 SmartPath AI - Chat & TTS Feature Test")
    print("=" * 60)
    
    tests = [
        ("TTS Info", test_tts_info),
        ("Text-to-Speech", test_text_to_speech),
        ("Chat Suggestions", test_chat_suggestions),
        ("Chat Functionality", test_chat_functionality),
        ("Context Setting", test_context_setting),
        ("Chat with Context", test_chat_with_context)
    ]
    
    passed = 0
    total = len(tests)
    
    for test_name, test_func in tests:
        print(f"\n{'='*20} {test_name} {'='*20}")
        try:
            if test_func():
                passed += 1
                print(f"✅ {test_name} - PASSED")
            else:
                print(f"❌ {test_name} - FAILED")
        except Exception as e:
            print(f"❌ {test_name} - ERROR: {e}")
    
    print("\n" + "=" * 60)
    print(f"📊 Test Results: {passed}/{total} tests passed")
    
    if passed == total:
        print("🎉 All tests passed! Chat and TTS features are working!")
        print("\n💡 New Features Available:")
        print("🔊 Text-to-Speech - Speak any response")
        print("💬 AI Chat - Ask questions about your career")
        print("🎯 Context-Aware - Personalized responses based on CV")
        print("📱 Enhanced Frontend - Multi-page interface")
        print("\n🚀 Try the enhanced frontend at: http://localhost:8501")
    else:
        print("⚠️ Some tests failed. Check the error messages above.")

if __name__ == "__main__":
    main()
