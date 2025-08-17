#!/usr/bin/env python3
"""
Demo script for SmartPath AI v2.0 new features:
- Text-to-Speech functionality
- AI Chat Assistant
- Enhanced CV analysis with spoken responses
"""
import requests
import time
import json

BASE_URL = "http://localhost:8000"

def print_header(title):
    """Print a formatted header"""
    print("\n" + "=" * 60)
    print(f"🚀 {title}")
    print("=" * 60)

def demo_enhanced_cv_analysis():
    """Demo the enhanced CV analysis with TTS"""
    print_header("Enhanced CV Analysis with Text-to-Speech")
    
    # Check if we have a sample CV
    try:
        with open('sample_cv.txt', 'r', encoding='utf-8') as f:
            cv_content = f.read()
        
        print("📄 Using sample CV for demonstration...")
        print(f"CV Preview: {cv_content[:200]}...")
        
        # Upload CV for analysis
        files = {'file': ('sample_cv.txt', cv_content, 'text/plain')}
        params = {'lang': 'en', 'user_name': 'Demo User'}
        
        print("\n🤖 Analyzing CV with AI...")
        response = requests.post(f"{BASE_URL}/match", files=files, params=params)
        
        if response.status_code == 200:
            data = response.json()
            matches = data.get('matches', [])
            
            print(f"✅ Analysis Complete!")
            print(f"📊 Found {len(matches)} job matches")
            print(f"🧠 Extracted {data.get('skills_extracted', 0)} skills")
            print(f"🎯 Chat context set: {data.get('chat_context_set', False)}")
            
            if matches:
                top_match = matches[0]
                print(f"\n🏆 Top Match: {top_match['title']} ({top_match['score']}% compatibility)")
                
                # Test speaking the result
                summary = f"Analysis complete! Your top job match is {top_match['title']} with {top_match['score']}% compatibility. You have {len(top_match.get('have_skills', []))} relevant skills."
                
                print("\n🔊 Speaking analysis result...")
                speak_response = requests.post(f"{BASE_URL}/speak", data={
                    "text": summary,
                    "language": "en"
                })
                
                if speak_response.status_code == 200:
                    speak_data = speak_response.json()
                    if speak_data.get('spoken'):
                        print("✅ Analysis result spoken successfully!")
                    else:
                        print("⚠️ Speech synthesis not available")
                else:
                    print("❌ Failed to speak result")
            
            return True
        else:
            print(f"❌ CV analysis failed: {response.status_code}")
            return False
            
    except FileNotFoundError:
        print("❌ Sample CV not found. Please upload a CV through the web interface.")
        return False
    except Exception as e:
        print(f"❌ Error: {e}")
        return False

def demo_ai_chat():
    """Demo the AI chat functionality"""
    print_header("AI Chat Assistant Demo")
    
    # Get suggested questions
    try:
        suggestions_response = requests.get(f"{BASE_URL}/chat/suggestions")
        if suggestions_response.status_code == 200:
            suggestions = suggestions_response.json()['suggested_questions']
            print("💡 Available suggested questions:")
            for i, suggestion in enumerate(suggestions[:3], 1):
                print(f"   {i}. {suggestion}")
        
        # Demo conversation
        demo_questions = [
            "What jobs am I most qualified for?",
            "What skills should I develop to improve my career?",
            "How can I prepare for a Data Analyst interview?"
        ]
        
        print(f"\n🤖 Starting AI conversation...")
        
        for i, question in enumerate(demo_questions, 1):
            print(f"\n💬 Question {i}: {question}")
            
            chat_response = requests.post(f"{BASE_URL}/chat", data={
                "message": question,
                "speak_response": False,  # Don't speak in demo to avoid conflicts
                "language": "en"
            })
            
            if chat_response.status_code == 200:
                chat_data = chat_response.json()
                
                if chat_data['success']:
                    print(f"🤖 AI Response: {chat_data['ai_response'][:150]}...")
                    print(f"🎯 Used CV context: {chat_data.get('context_used', False)}")
                else:
                    print(f"⚠️ AI Response: {chat_data['response']}")
            else:
                print(f"❌ Chat failed: {chat_response.status_code}")
            
            time.sleep(1)  # Brief pause between questions
        
        return True
        
    except Exception as e:
        print(f"❌ Chat demo error: {e}")
        return False

def demo_text_to_speech():
    """Demo text-to-speech functionality"""
    print_header("Text-to-Speech Demo")
    
    # Check TTS availability
    try:
        tts_info_response = requests.get(f"{BASE_URL}/tts/info")
        if tts_info_response.status_code == 200:
            tts_info = tts_info_response.json()
            
            print(f"🔊 TTS Engine: {tts_info.get('engine_type', 'Unknown')}")
            print(f"✅ Available: {tts_info.get('available', False)}")
            print(f"🌍 Languages: {tts_info.get('supports_languages', [])}")
            
            if tts_info.get('available'):
                # Demo different types of speech
                demo_texts = [
                    "Welcome to SmartPath AI, your personal career coach!",
                    "You are highly qualified for the Data Analyst position with an 85% match score.",
                    "I recommend developing machine learning skills to enhance your profile."
                ]
                
                print(f"\n🗣️ Testing speech synthesis...")
                
                for i, text in enumerate(demo_texts, 1):
                    print(f"\n📝 Sample {i}: {text}")
                    
                    speak_response = requests.post(f"{BASE_URL}/speak", data={
                        "text": text,
                        "language": "en"
                    })
                    
                    if speak_response.status_code == 200:
                        speak_data = speak_response.json()
                        if speak_data.get('spoken'):
                            print(f"🔊 Speaking sample {i}...")
                            time.sleep(3)  # Wait for speech to complete
                        else:
                            print(f"⚠️ Failed to speak sample {i}")
                    else:
                        print(f"❌ Speech request failed for sample {i}")
                
                return True
            else:
                print("❌ Text-to-speech not available")
                return False
        else:
            print("❌ Cannot check TTS status")
            return False
            
    except Exception as e:
        print(f"❌ TTS demo error: {e}")
        return False

def demo_api_endpoints():
    """Demo the new API endpoints"""
    print_header("New API Endpoints Demo")
    
    endpoints = [
        ("GET /", "System status"),
        ("GET /tts/info", "TTS engine information"),
        ("GET /chat/suggestions", "Chat suggestions"),
        ("POST /chat", "AI chat conversation"),
        ("POST /speak", "Text-to-speech"),
        ("POST /context/set", "Set chat context")
    ]
    
    print("🔧 Available API endpoints:")
    for endpoint, description in endpoints:
        print(f"   • {endpoint} - {description}")
    
    # Test system status
    try:
        status_response = requests.get(f"{BASE_URL}/")
        if status_response.status_code == 200:
            status_data = status_response.json()
            print(f"\n✅ System Status: {status_data['message']}")
            print(f"🤖 Powered by: {status_data['powered_by']}")
            
            features = status_data.get('features', [])
            print(f"🎯 Features ({len(features)}):")
            for feature in features:
                print(f"   • {feature}")
            
            return True
        else:
            print(f"❌ System status check failed: {status_response.status_code}")
            return False
            
    except Exception as e:
        print(f"❌ API demo error: {e}")
        return False

def main():
    """Run the complete demo"""
    print("🚀 SmartPath AI v2.0 - New Features Demo")
    print("🎯 Demonstrating: Text-to-Speech, AI Chat, Enhanced CV Analysis")
    
    # Check if backend is running
    try:
        response = requests.get(f"{BASE_URL}/", timeout=5)
        if response.status_code != 200:
            print("❌ Backend not responding. Please start the backend server first.")
            print("💡 Run: cd backend && python -m uvicorn main:app --reload")
            return
    except requests.exceptions.RequestException:
        print("❌ Cannot connect to backend. Please start the backend server first.")
        print("💡 Run: cd backend && python -m uvicorn main:app --reload")
        return
    
    # Run demos
    demos = [
        ("API Endpoints", demo_api_endpoints),
        ("Text-to-Speech", demo_text_to_speech),
        ("Enhanced CV Analysis", demo_enhanced_cv_analysis),
        ("AI Chat Assistant", demo_ai_chat)
    ]
    
    results = []
    for demo_name, demo_func in demos:
        try:
            result = demo_func()
            results.append((demo_name, result))
        except Exception as e:
            print(f"❌ {demo_name} demo failed: {e}")
            results.append((demo_name, False))
    
    # Summary
    print_header("Demo Summary")
    
    passed = sum(1 for _, result in results if result)
    total = len(results)
    
    for demo_name, result in results:
        status = "✅ PASSED" if result else "❌ FAILED"
        print(f"{demo_name}: {status}")
    
    print(f"\n📊 Results: {passed}/{total} demos successful")
    
    if passed == total:
        print("\n🎉 All demos passed! SmartPath AI v2.0 features are working!")
        print("\n🌟 New Features Available:")
        print("   🔊 Text-to-Speech - Hear your analysis results")
        print("   💬 AI Chat - Ask questions about your career")
        print("   🎯 Context-Aware - Personalized responses")
        print("   📱 Enhanced UI - Multi-page interface")
        print("\n🚀 Try the enhanced frontend at: http://localhost:8501")
    else:
        print("\n⚠️ Some demos failed. Check the error messages above.")
        print("💡 Make sure all dependencies are installed and the backend is running.")

if __name__ == "__main__":
    main()
