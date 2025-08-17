#!/usr/bin/env python3
"""
Test the fixed backend with Google AI
"""
import requests
import time

def test_backend():
    print('🧪 Testing FIXED backend...')
    
    # Wait for server to start
    time.sleep(3)
    
    # Test system status
    try:
        response = requests.get('http://localhost:8000/')
        if response.status_code == 200:
            data = response.json()
            print(f'✅ System: {data["message"]}')
            print(f'🤖 Google AI Available: {data.get("google_ai_available", "Unknown")}')
            return data.get("google_ai_available", False)
        else:
            print(f'❌ System status error: {response.status_code}')
            return False
    except Exception as e:
        print(f'❌ Connection error: {e}')
        return False

def test_ai_endpoint():
    print('\n🤖 Testing AI endpoint...')
    
    try:
        response = requests.get('http://localhost:8000/test-ai')
        if response.status_code == 200:
            data = response.json()
            print(f'AI Test Success: {data["success"]}')
            if data['success']:
                print(f'✅ AI Response: {data["ai_response"]}')
                return True
            else:
                print(f'❌ AI Error: {data.get("error", "Unknown")}')
                return False
        else:
            print(f'❌ AI test error: {response.status_code}')
            return False
    except Exception as e:
        print(f'❌ AI test connection error: {e}')
        return False

def test_chat_endpoint():
    print('\n💬 Testing chat endpoint...')
    
    try:
        response = requests.post('http://localhost:8000/chat', data={
            'message': 'Hello, can you help me with my career?',
            'speak_response': False,
            'language': 'en'
        })
        
        if response.status_code == 200:
            data = response.json()
            print(f'Chat Success: {data["success"]}')
            if data['success']:
                print(f'✅ AI Response: {data["ai_response"][:100]}...')
                print(f'🔧 Method: {data.get("method", "unknown")}')
                return True
            else:
                print(f'❌ Chat Error: {data["ai_response"]}')
                return False
        else:
            print(f'❌ Chat endpoint error: {response.status_code}')
            return False
    except Exception as e:
        print(f'❌ Chat test error: {e}')
        return False

if __name__ == "__main__":
    print("🚀 Testing SmartPath AI Backend Fix")
    print("=" * 50)
    
    # Test backend status
    backend_ok = test_backend()
    
    # Test AI endpoint
    ai_ok = test_ai_endpoint()
    
    # Test chat endpoint
    chat_ok = test_chat_endpoint()
    
    print("\n" + "=" * 50)
    print("📊 TEST RESULTS")
    print("=" * 50)
    
    results = [
        ("Backend Status", "✅ PASS" if backend_ok else "❌ FAIL"),
        ("Google AI Test", "✅ PASS" if ai_ok else "❌ FAIL"),
        ("Chat Endpoint", "✅ PASS" if chat_ok else "❌ FAIL")
    ]
    
    for test_name, result in results:
        print(f"{test_name}: {result}")
    
    if all([backend_ok, ai_ok, chat_ok]):
        print("\n🎉 ALL TESTS PASSED! Chat is now working!")
        print("💬 You can now ask questions and get AI responses!")
    else:
        print("\n⚠️ Some tests failed. Check the errors above.")
