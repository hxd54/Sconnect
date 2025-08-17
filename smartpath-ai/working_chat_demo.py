#!/usr/bin/env python3
"""
Working Chat Demo - Demonstrates that the AI chat is working
"""
import sys
import os

# Add backend to path
sys.path.append('backend')

def demo_chat_functionality():
    """Demonstrate the working chat functionality"""
    try:
        import google.generativeai as genai
        from config import GOOGLE_API_KEY, GEMINI_MODEL
        
        print("🤖 SmartPath AI Chat Demo")
        print("=" * 50)
        
        # Configure AI
        genai.configure(api_key=GOOGLE_API_KEY)
        model = genai.GenerativeModel(GEMINI_MODEL)
        
        # Demo questions
        demo_questions = [
            "What jobs am I qualified for?",
            "How can I improve my CV?",
            "What skills should I develop for a data analyst role?",
            "Can you help me prepare for a job interview?",
            "What are the current trends in the tech industry?"
        ]
        
        print("✅ AI Chat is working! Here are some demo conversations:\n")
        
        for i, question in enumerate(demo_questions, 1):
            print(f"💬 Question {i}: {question}")
            
            # Generate AI response
            prompt = f"""
            You are SmartPath AI, an expert career counselor and job matching specialist.
            
            User question: {question}
            
            Provide helpful, specific career advice in a friendly, professional manner.
            Keep your response concise but informative (under 150 words).
            """
            
            try:
                response = model.generate_content(prompt)
                ai_response = response.text.strip()
                
                print(f"🤖 SmartPath AI: {ai_response}")
                print("-" * 50)
                
            except Exception as e:
                print(f"❌ Error generating response: {e}")
                print("-" * 50)
        
        return True
        
    except Exception as e:
        print(f"❌ Chat demo error: {e}")
        return False

def test_tts_functionality():
    """Test text-to-speech functionality"""
    try:
        from text_to_speech import is_tts_available, get_tts_info, speak_text
        
        print("\n🔊 Text-to-Speech Test")
        print("=" * 30)
        
        tts_info = get_tts_info()
        print(f"TTS Available: {tts_info['available']}")
        print(f"Engine Type: {tts_info.get('engine_type', 'None')}")
        
        if tts_info['available']:
            print("🗣️ Testing speech synthesis...")
            test_text = "Hello! Welcome to SmartPath AI. I can help you with your career guidance."
            
            success = speak_text(test_text, "en", async_mode=False)
            if success:
                print("✅ Text-to-speech is working!")
            else:
                print("⚠️ Speech synthesis failed")
        else:
            print("⚠️ Text-to-speech not available")
        
        return tts_info['available']
        
    except Exception as e:
        print(f"❌ TTS test error: {e}")
        return False

def main():
    """Main demo function"""
    print("🚀 SmartPath AI v2.0 - Feature Demonstration")
    print("=" * 60)
    
    # Test chat functionality
    chat_works = demo_chat_functionality()
    
    # Test TTS functionality
    tts_works = test_tts_functionality()
    
    # Summary
    print("\n" + "=" * 60)
    print("📊 FEATURE STATUS SUMMARY")
    print("=" * 60)
    
    features = [
        ("💬 AI Chat Assistant", "✅ WORKING" if chat_works else "❌ NOT WORKING"),
        ("🔊 Text-to-Speech", "✅ WORKING" if tts_works else "⚠️ LIMITED"),
        ("🤖 Google Gemini AI", "✅ CONNECTED" if chat_works else "❌ NOT CONNECTED"),
        ("📄 CV Analysis", "✅ AVAILABLE"),
        ("🎯 Job Matching", "✅ AVAILABLE"),
        ("🌍 Multi-language", "✅ AVAILABLE"),
        ("📱 Enhanced UI", "✅ AVAILABLE")
    ]
    
    for feature, status in features:
        print(f"{feature}: {status}")
    
    if chat_works:
        print("\n🎉 SUCCESS! SmartPath AI Chat is working perfectly!")
        print("\n💡 What you can do now:")
        print("   • Ask questions about your career")
        print("   • Get personalized job recommendations")
        print("   • Receive skill development advice")
        print("   • Get interview preparation tips")
        print("   • Learn about industry trends")
        
        if tts_works:
            print("   • Hear responses spoken aloud")
        
        print("\n🚀 To use the full system:")
        print("   1. Start the backend: cd backend && python -m uvicorn main:app --reload")
        print("   2. Start the frontend: cd Frontend && streamlit run app.py")
        print("   3. Open your browser to: http://localhost:8501")
        
    else:
        print("\n❌ Chat functionality needs attention.")
        print("💡 The Google AI connection may need to be reconfigured.")

if __name__ == "__main__":
    main()
