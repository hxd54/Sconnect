#!/usr/bin/env python3
"""
Morning test for SmartPath AI
"""
import sys
import time
import requests

sys.path.append('backend')

def test_morning_ai():
    print("🌅 Good Morning! Testing SmartPath AI...")
    
    # First test Google AI directly
    try:
        import google.generativeai as genai
        from config import GOOGLE_API_KEY, GEMINI_MODEL
        
        genai.configure(api_key=GOOGLE_API_KEY)
        model = genai.GenerativeModel(GEMINI_MODEL)
        response = model.generate_content("Good morning! Is the AI working?")
        
        print("✅ Google AI Direct Test: SUCCESS")
        print(f"🤖 Response: {response.text.strip()[:80]}...")
        
    except Exception as e:
        print(f"❌ Google AI Direct Test: FAILED - {e}")
        return False
    
    # Test server on common ports
    print("\n🔍 Looking for running server...")
    ports_to_try = [49907, 8003, 8004, 8005, 8006]
    
    for port in ports_to_try:
        try:
            response = requests.get(f'http://localhost:{port}/', timeout=2)
            if response.status_code == 200:
                data = response.json()
                print(f"✅ Server found on port {port}!")
                print(f"📊 Status: {data.get('message', 'Unknown')}")
                
                # Test chat
                chat_response = requests.post(f'http://localhost:{port}/chat', 
                                            data={'message': 'Good morning! Test'})
                if chat_response.status_code == 200:
                    chat_data = chat_response.json()
                    if chat_data.get('success'):
                        print(f"💬 Chat Test: SUCCESS")
                        print(f"🤖 AI Response: {chat_data['ai_response'][:80]}...")
                        print(f"\n🎉 SMARTPATH AI IS FULLY WORKING!")
                        print(f"🔗 Your URL: http://localhost:{port}/")
                        return True
                    else:
                        print(f"❌ Chat failed: {chat_data.get('ai_response', 'Unknown')}")
                else:
                    print(f"❌ Chat HTTP error: {chat_response.status_code}")
        except Exception as e:
            continue
    
    print("❌ No working server found. Need to start server.")
    return False

if __name__ == "__main__":
    test_morning_ai()
