#!/usr/bin/env python3
"""
Final comprehensive test of the fixed SmartPath AI backend
"""
import requests
import time

def test_server_status():
    """Test basic server status"""
    print("🔍 Testing server status...")
    try:
        response = requests.get('http://localhost:8000/', timeout=10)
        if response.status_code == 200:
            data = response.json()
            print(f"✅ Server: {data['message']}")
            print(f"🤖 Google AI Available: {data.get('google_ai_available', 'Unknown')}")
            return data.get('google_ai_available', False)
        else:
            print(f"❌ Server error: {response.status_code}")
            return False
    except Exception as e:
        print(f"❌ Connection error: {e}")
        return False

def test_ai_endpoint():
    """Test the AI test endpoint"""
    print("\n🤖 Testing AI endpoint...")
    try:
        response = requests.get('http://localhost:8000/test-ai', timeout=15)
        if response.status_code == 200:
            data = response.json()
            print(f"AI Test Success: {data['success']}")
            if data['success']:
                print(f"✅ AI Response: {data['ai_response']}")
                return True
            else:
                print(f"❌ AI Error: {data.get('error', 'Unknown')}")
                return False
        else:
            print(f"❌ AI endpoint error: {response.status_code}")
            return False
    except Exception as e:
        print(f"❌ AI endpoint error: {e}")
        return False

def test_chat_endpoint():
    """Test the chat endpoint"""
    print("\n💬 Testing chat endpoint...")
    try:
        response = requests.post('http://localhost:8000/chat', 
                               data={'message': 'Hello, can you help me with my career?'},
                               timeout=20)
        
        if response.status_code == 200:
            data = response.json()
            print(f"Chat Success: {data['success']}")
            if data['success']:
                print(f"✅ AI Response: {data['ai_response'][:100]}...")
                print(f"🔧 Method: {data.get('method', 'unknown')}")
                return True
            else:
                print(f"❌ Chat Error: {data['ai_response']}")
                return False
        else:
            print(f"❌ Chat endpoint error: {response.status_code}")
            return False
    except Exception as e:
        print(f"❌ Chat endpoint error: {e}")
        return False

def test_multiple_chat_questions():
    """Test multiple chat questions"""
    print("\n🗣️ Testing multiple chat questions...")
    
    questions = [
        "What jobs am I qualified for?",
        "How can I improve my CV?",
        "What skills should I develop?"
    ]
    
    success_count = 0
    
    for i, question in enumerate(questions, 1):
        print(f"\n💬 Question {i}: {question}")
        try:
            response = requests.post('http://localhost:8000/chat', 
                                   data={'message': question},
                                   timeout=15)
            
            if response.status_code == 200:
                data = response.json()
                if data['success']:
                    print(f"✅ Response: {data['ai_response'][:80]}...")
                    success_count += 1
                else:
                    print(f"❌ Error: {data['ai_response'][:80]}...")
            else:
                print(f"❌ HTTP Error: {response.status_code}")
                
        except Exception as e:
            print(f"❌ Request Error: {e}")
    
    return success_count == len(questions)

def main():
    """Run comprehensive tests"""
    print("🚀 SmartPath AI - Final Comprehensive Test")
    print("=" * 60)
    
    # Wait for server to be ready
    print("⏳ Waiting for server to start...")
    time.sleep(5)
    
    # Run tests
    tests = [
        ("Server Status", test_server_status),
        ("AI Endpoint", test_ai_endpoint),
        ("Chat Endpoint", test_chat_endpoint),
        ("Multiple Questions", test_multiple_chat_questions)
    ]
    
    results = []
    
    for test_name, test_func in tests:
        print(f"\n{'='*20} {test_name} {'='*20}")
        try:
            result = test_func()
            results.append((test_name, result))
            print(f"Result: {'✅ PASS' if result else '❌ FAIL'}")
        except Exception as e:
            print(f"❌ Test Error: {e}")
            results.append((test_name, False))
    
    # Final summary
    print("\n" + "=" * 60)
    print("📊 FINAL TEST RESULTS")
    print("=" * 60)
    
    passed = 0
    total = len(results)
    
    for test_name, result in results:
        status = "✅ PASS" if result else "❌ FAIL"
        print(f"{test_name}: {status}")
        if result:
            passed += 1
    
    print(f"\nOverall: {passed}/{total} tests passed")
    
    if passed == total:
        print("\n🎉 ALL TESTS PASSED!")
        print("✅ SmartPath AI chat is working perfectly!")
        print("💬 You can now ask questions and get AI responses!")
        print("\n🌐 Server running at: http://localhost:8000")
        print("📡 Available endpoints:")
        print("   • GET  / - Server status")
        print("   • GET  /test-ai - Test AI connection")
        print("   • POST /chat - Chat with AI")
        print("\n💡 Example usage:")
        print("   curl -X POST http://localhost:8000/chat -d 'message=What jobs am I qualified for?'")
        
    elif passed > 0:
        print(f"\n⚠️ Partial success: {passed}/{total} tests passed")
        print("Some functionality is working. Check the failed tests above.")
        
    else:
        print("\n❌ All tests failed.")
        print("Please check the server logs and configuration.")

if __name__ == "__main__":
    main()
