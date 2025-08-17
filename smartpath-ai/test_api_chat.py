#!/usr/bin/env python3
"""
Test the chat API endpoint
"""
import requests
import time

def test_chat_api():
    """Test the chat API endpoint"""
    print('🧪 Testing chat API endpoint...')
    
    try:
        # Wait for server to be ready
        time.sleep(2)
        
        # Test chat endpoint
        response = requests.post('http://localhost:8000/chat', data={
            'message': 'What jobs am I qualified for?',
            'speak_response': False,
            'language': 'en'
        })
        
        print(f'Status Code: {response.status_code}')
        
        if response.status_code == 200:
            data = response.json()
            print(f'Success: {data["success"]}')
            print(f'Response: {data["ai_response"][:150]}...')
            print(f'Context Used: {data.get("context_used", False)}')
            print(f'TTS Available: {data.get("tts_available", False)}')
            return True
        else:
            print(f'Error Response: {response.text}')
            return False
            
    except Exception as e:
        print(f'❌ API test error: {e}')
        return False

def test_backend_status():
    """Test if backend is running"""
    try:
        response = requests.get('http://localhost:8000/')
        if response.status_code == 200:
            data = response.json()
            print(f'✅ Backend Status: {data["message"]}')
            return True
        else:
            print(f'❌ Backend Error: {response.status_code}')
            return False
    except Exception as e:
        print(f'❌ Backend connection error: {e}')
        return False

def test_tts_info():
    """Test TTS info endpoint"""
    try:
        response = requests.get('http://localhost:8000/tts/info')
        if response.status_code == 200:
            data = response.json()
            print(f'🔊 TTS Available: {data["available"]}')
            print(f'🔧 TTS Engine: {data.get("engine_type", "None")}')
            return True
        else:
            print(f'❌ TTS Info Error: {response.status_code}')
            return False
    except Exception as e:
        print(f'❌ TTS test error: {e}')
        return False

if __name__ == "__main__":
    print("🚀 Testing SmartPath AI Chat API")
    print("=" * 50)
    
    # Test backend status
    if not test_backend_status():
        print("❌ Backend not running. Please start it first.")
        exit(1)
    
    print("\n" + "-" * 30)
    
    # Test TTS
    test_tts_info()
    
    print("\n" + "-" * 30)
    
    # Test chat
    if test_chat_api():
        print("\n✅ Chat API is working!")
    else:
        print("\n❌ Chat API failed!")
