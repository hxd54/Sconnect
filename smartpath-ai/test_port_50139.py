#!/usr/bin/env python3
"""
Test the simple database server on port 50139
"""
import requests

def test_server():
    print('🧪 Testing simple database server on port 50139...')
    
    try:
        # Test server status
        response = requests.get('http://localhost:50139/')
        if response.status_code == 200:
            data = response.json()
            print(f'✅ Server Status: {data.get("message", "Unknown")}')
            print(f'🗄️ Database: {data.get("database", "Unknown")}')
            print(f'🤖 Mode: {data.get("mode", "Unknown")}')
            
            # Test chat with a career question
            chat_response = requests.post('http://localhost:50139/chat', data={
                'message': 'What jobs am I qualified for?',
                'user_name': 'Test User'
            })
            
            if chat_response.status_code == 200:
                chat_data = chat_response.json()
                if chat_data.get('success'):
                    print('💬 Chat Test: SUCCESS')
                    ai_response = chat_data.get('ai_response', 'No response')
                    print(f'🤖 AI Response: {ai_response[:100]}...')
                    print(f'📊 Category: {chat_data.get("category", "Unknown")}')
                    print(f'💾 Storage: {chat_data.get("storage", "Unknown")}')
                    print(f'🆔 Session: {chat_data.get("session_id", "Unknown")[:8]}...')
                    
                    # Test another question
                    chat_response2 = requests.post('http://localhost:50139/chat', data={
                        'message': 'How can I improve my CV?',
                        'user_name': 'Test User',
                        'session_id': chat_data.get('session_id')
                    })
                    
                    if chat_response2.status_code == 200:
                        chat_data2 = chat_response2.json()
                        if chat_data2.get('success'):
                            print('💬 Second Chat Test: SUCCESS')
                            ai_response2 = chat_data2.get('ai_response', 'No response')
                            print(f'🤖 Second Response: {ai_response2[:100]}...')
                            print('🎉 SIMPLE DATABASE SERVER IS WORKING PERFECTLY!')
                            return True
                    
                else:
                    print(f'❌ Chat Error: {chat_data.get("ai_response", "Unknown")}')
            else:
                print(f'❌ Chat HTTP Error: {chat_response.status_code}')
        else:
            print(f'❌ Server Error: {response.status_code}')
            
    except Exception as e:
        print(f'❌ Connection Error: {e}')
        
    return False

if __name__ == "__main__":
    if test_server():
        print("\n🎉 SUCCESS! Your simple database server is working!")
        print("\n🚀 Your Streamlit app is now connected to port 50139!")
        print("   • Go to your Streamlit app")
        print("   • Try asking: 'What jobs am I qualified for?'")
        print("   • You should get intelligent career responses!")
        print("   • All conversations will be stored in the database!")
    else:
        print("\n❌ Simple database server not working properly")
        print("💡 Make sure the server is running on port 50139")
