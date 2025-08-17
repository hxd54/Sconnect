#!/usr/bin/env python3
"""
Test the chat server on port 8003
"""
import requests
import time

def test_port_8003():
    print('🧪 Testing chat server on port 8003...')
    
    time.sleep(3)  # Wait for server to start
    
    # Test server status
    try:
        response = requests.get('http://localhost:8003/')
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
    
    # Test chat
    try:
        response = requests.post('http://localhost:8003/chat', data={
            'message': 'Hello, can you help me with my career?'
        })
        if response.status_code == 200:
            data = response.json()
            if data['success']:
                print(f'✅ Chat working: {data["ai_response"][:80]}...')
                print('🎉 CHAT SERVER IS WORKING ON PORT 8003!')
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
    if test_port_8003():
        print("\n🎉 SUCCESS! Your chat server is working on port 8003!")
        print("\n🌐 Test URLs:")
        print("   • http://localhost:8003/ - Server status")
        print("   • http://localhost:8003/test - AI test")
        print("   • POST http://localhost:8003/chat - Chat with AI")
        print("\n📱 Open the test page: test_chat_page.html")
    else:
        print("\n❌ Server not working on port 8003")
        print("💡 Make sure to run: python simple_chat_server.py")
