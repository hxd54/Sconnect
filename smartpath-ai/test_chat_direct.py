#!/usr/bin/env python3
"""
Direct test of chat interface
"""
import sys
import os
sys.path.append('backend')

def test_chat():
    try:
        from chat_interface import chat_with_ai
        
        print('🤖 Testing chat interface directly...')
        response = chat_with_ai('What jobs am I qualified for?', False, 'en')
        
        print(f'Success: {response["success"]}')
        print(f'Response: {response["response"]}')
        
        if not response['success']:
            print(f'Error: {response.get("error", "Unknown")}')
        
        return response['success']
        
    except Exception as e:
        print(f'❌ Direct test error: {e}')
        return False

def test_google_ai_direct():
    try:
        print('🔍 Testing Google AI directly...')
        
        import google.generativeai as genai
        from config import GOOGLE_API_KEY, GEMINI_MODEL
        
        genai.configure(api_key=GOOGLE_API_KEY)
        model = genai.GenerativeModel(GEMINI_MODEL)
        
        response = model.generate_content("What is artificial intelligence?")
        print(f'✅ Google AI working: {response.text[:100]}...')
        
        return True
        
    except Exception as e:
        print(f'❌ Google AI test error: {e}')
        return False

if __name__ == "__main__":
    print("🧪 Testing Chat Interface Components")
    print("=" * 50)
    
    # Test Google AI directly
    ai_works = test_google_ai_direct()
    
    print("\n" + "-" * 50)
    
    # Test chat interface
    chat_works = test_chat()
    
    print("\n" + "=" * 50)
    if ai_works and chat_works:
        print("🎉 All tests passed!")
    else:
        print("❌ Some tests failed")
        if not ai_works:
            print("  - Google AI connection failed")
        if not chat_works:
            print("  - Chat interface failed")
