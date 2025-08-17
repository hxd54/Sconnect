#!/usr/bin/env python3
"""
Test the simple chat server
"""
import requests
import time

def test_simple_server():
    print('🧪 Testing simple chat server...')
    
    time.sleep(3)  # Wait for server to start
    
    # Test root
    try:
        response = requests.get('http://localhost:8002/')
        if response.status_code == 200:
            data = response.json()
            print(f'✅ Server: {data["message"]}')
            print(f'🤖 Google AI: {data["google_ai"]}')
        else:
            print(f'❌ Server error: {response.status_code}')
            return False
    except Exception as e:
        print(f'❌ Connection error: {e}')
        return False
    
    # Test AI
    try:
        response = requests.get('http://localhost:8002/test')
        if response.status_code == 200:
            data = response.json()
            if data['success']:
                print(f'✅ AI Test: {data["ai_response"]}')
            else:
                print(f'❌ AI Error: {data["error"]}')
                return False
        else:
            print(f'❌ AI test error: {response.status_code}')
            return False
    except Exception as e:
        print(f'❌ AI test error: {e}')
        return False
    
    # Test chat
    try:
        response = requests.post('http://localhost:8002/chat', data={
            'message': 'What jobs am I qualified for?'
        })
        if response.status_code == 200:
            data = response.json()
            if data['success']:
                print(f'✅ Chat: {data["ai_response"][:100]}...')
                print('🎉 CHAT IS WORKING!')
                return True
            else:
                print(f'❌ Chat Error: {data["ai_response"]}')
                return False
        else:
            print(f'❌ Chat error: {response.status_code}')
            return False
    except Exception as e:
        print(f'❌ Chat error: {e}')
        return False

if __name__ == "__main__":
    if test_simple_server():
        print("\n🎉 SUCCESS! The simple chat server is working!")
        print("💬 You can now use the chat functionality!")
        print("🌐 Server running at: http://localhost:8002")
        print("\n💡 To test manually:")
        print("   • GET http://localhost:8002/ - Server status")
        print("   • GET http://localhost:8002/test - AI test")
        print("   • POST http://localhost:8002/chat - Chat with AI")
    else:
        print("\n❌ Simple server test failed.")
