#!/usr/bin/env python3
"""
Test the demo server on port 54094
"""
import requests

def test_demo_server():
    print('🧪 Testing demo server on port 54094...')
    
    try:
        # Test server status
        response = requests.get('http://localhost:54094/')
        if response.status_code == 200:
            data = response.json()
            print(f'✅ Server Status: {data.get("message", "Unknown")}')
            print(f'🤖 Mode: {data.get("mode", "Unknown")}')
            
            # Test chat
            chat_response = requests.post('http://localhost:54094/chat', 
                                        data={'message': 'What jobs am I qualified for?'})
            if chat_response.status_code == 200:
                chat_data = chat_response.json()
                if chat_data.get('success'):
                    print('💬 Chat Test: SUCCESS')
                    print(f'🤖 AI Response: {chat_data["ai_response"][:100]}...')
                    print('🎉 DEMO SERVER IS WORKING PERFECTLY!')
                    print('\n🚀 Next Steps:')
                    print('1. Keep the demo server running (don\'t close the command prompt)')
                    print('2. Go to your Streamlit app')
                    print('3. Try asking: "What jobs am I qualified for?"')
                    print('4. Enjoy unlimited AI responses!')
                    return True
                else:
                    print(f'❌ Chat failed: {chat_data.get("ai_response", "Unknown")}')
            else:
                print(f'❌ Chat HTTP error: {chat_response.status_code}')
        else:
            print(f'❌ Server error: {response.status_code}')
            
    except Exception as e:
        print(f'❌ Connection error: {e}')
        print('Make sure the demo server is running!')
        
    return False

if __name__ == "__main__":
    test_demo_server()
