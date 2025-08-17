#!/usr/bin/env python3
"""
Final test of the fixed chat functionality
"""
import requests

def test_chat():
    print('🧪 Testing FIXED chat functionality...')
    
    # Test chat with a simple question
    response = requests.post('http://localhost:8000/chat', data={
        'message': 'Hello, can you help me with my career?',
        'speak_response': False,
        'language': 'en'
    })
    
    print(f'Status: {response.status_code}')
    if response.status_code == 200:
        data = response.json()
        print(f'Success: {data["success"]}')
        if data['success']:
            print(f'AI Response: {data["ai_response"][:150]}...')
            print('🎉 CHAT IS WORKING!')
            return True
        else:
            print(f'Error: {data["ai_response"]}')
            return False
    else:
        print(f'HTTP Error: {response.text}')
        return False

if __name__ == "__main__":
    if test_chat():
        print("\n✅ SUCCESS! The chat functionality is now working!")
        print("🚀 You can now ask questions and get AI responses!")
        print("💬 Try asking: 'What jobs am I qualified for?'")
        print("🔊 Text-to-speech is also available!")
    else:
        print("\n❌ Chat still not working. Check the error above.")
