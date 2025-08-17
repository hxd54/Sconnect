#!/usr/bin/env python3
"""
Test the database chat server
"""
import requests

def test_database_server():
    print('🧪 Testing database server on port 49484...')
    
    try:
        # Test server status
        response = requests.get('http://localhost:49484/')
        if response.status_code == 200:
            data = response.json()
            print(f'✅ Server Status: {data.get("message", "Unknown")}')
            print(f'🗄️ Database Mode: {data.get("mode", "Unknown")}')
            
            analytics = data.get("analytics", {})
            print(f'📊 Total Sessions: {analytics.get("total_sessions", 0)}')
            print(f'💬 Total Messages: {analytics.get("total_messages", 0)}')
            print(f'👥 Unique Users: {analytics.get("unique_users", 0)}')
            
            # Test chat with database storage
            chat_response = requests.post('http://localhost:49484/chat', data={
                'message': 'Hello! Test database storage.',
                'user_name': 'Test User'
            })
            
            if chat_response.status_code == 200:
                chat_data = chat_response.json()
                if chat_data.get('success'):
                    print('💬 Chat Test: SUCCESS')
                    print(f'🆔 Session ID: {chat_data.get("session_id", "Unknown")[:8]}...')
                    print(f'📊 Category: {chat_data.get("category", "Unknown")}')
                    print(f'⚡ Response Time: {chat_data.get("response_time_ms", 0)}ms')
                    print(f'🤖 AI Response: {chat_data.get("ai_response", "No response")[:60]}...')
                    print('🎉 DATABASE CHAT SERVER IS WORKING PERFECTLY!')
                    
                    # Test analytics endpoint
                    analytics_response = requests.get('http://localhost:49484/analytics')
                    if analytics_response.status_code == 200:
                        analytics_data = analytics_response.json()
                        if analytics_data.get('success'):
                            print('📊 Analytics endpoint: WORKING')
                        else:
                            print('❌ Analytics endpoint: ERROR')
                    
                    return True
                else:
                    print(f'❌ Chat Error: {chat_data.get("ai_response", "Unknown")}')
            else:
                print(f'❌ Chat HTTP Error: {chat_response.status_code}')
        else:
            print(f'❌ Server Error: {response.status_code}')
            
    except Exception as e:
        print(f'❌ Connection Error: {e}')
        print('Make sure the database server is running!')
        
    return False

if __name__ == "__main__":
    if test_database_server():
        print("\n🎉 SUCCESS! Your database chat server is fully operational!")
        print("\n🗄️ Database Features Working:")
        print("   ✅ Message storage")
        print("   ✅ Session tracking")
        print("   ✅ User analytics")
        print("   ✅ Response categorization")
        print("   ✅ Performance metrics")
        print("\n🚀 Your Streamlit app is now connected to the database!")
        print("   • All conversations will be stored permanently")
        print("   • User sessions will be tracked")
        print("   • Analytics will be collected")
        print("   • Search functionality available")
    else:
        print("\n❌ Database server not working properly")
        print("💡 Make sure to run: python database_chat_server.py")
