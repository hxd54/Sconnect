#!/usr/bin/env python3
"""
Quick fix for chat functionality - standalone test
"""
import sys
import os

# Add backend to path
sys.path.append('backend')

def test_google_ai_import():
    """Test Google AI import directly"""
    try:
        import google.generativeai as genai
        from config import GOOGLE_API_KEY, GEMINI_MODEL
        
        print("✅ Google AI imported successfully")
        
        # Configure and test
        genai.configure(api_key=GOOGLE_API_KEY)
        model = genai.GenerativeModel(GEMINI_MODEL)
        
        # Test generation
        response = model.generate_content("Hello, how can I help with career guidance?")
        print(f"✅ AI Response: {response.text[:100]}...")
        
        return True
        
    except Exception as e:
        print(f"❌ Google AI Error: {e}")
        return False

def create_simple_chat_endpoint():
    """Create a simple working chat endpoint"""
    from fastapi import FastAPI, Form
    import uvicorn
    
    app = FastAPI(title="SmartPath AI Chat Fix")
    
    @app.post("/chat")
    async def chat_fixed(message: str = Form(...)):
        try:
            import google.generativeai as genai
            from config import GOOGLE_API_KEY, GEMINI_MODEL
            
            genai.configure(api_key=GOOGLE_API_KEY)
            model = genai.GenerativeModel(GEMINI_MODEL)
            
            # Generate response
            response = model.generate_content(f"""
            You are SmartPath AI, a career counselor. 
            User question: {message}
            
            Provide helpful career advice in a friendly, professional manner.
            """)
            
            return {
                "success": True,
                "ai_response": response.text,
                "user_message": message
            }
            
        except Exception as e:
            return {
                "success": False,
                "ai_response": f"Error: {str(e)}",
                "user_message": message
            }
    
    @app.get("/")
    async def root():
        return {"message": "SmartPath AI Chat Fix is running!"}
    
    print("🚀 Starting fixed chat server on port 8001...")
    uvicorn.run(app, host="0.0.0.0", port=8001)

if __name__ == "__main__":
    print("🔧 SmartPath AI Chat Fix")
    print("=" * 50)
    
    # Test Google AI import
    if test_google_ai_import():
        print("\n✅ Google AI is working!")
        
        # Ask user if they want to start the fixed server
        print("\n🚀 Would you like to start the fixed chat server? (y/n)")
        choice = input().lower().strip()
        
        if choice == 'y':
            create_simple_chat_endpoint()
        else:
            print("💡 You can test the chat by running this script and choosing 'y'")
    else:
        print("\n❌ Google AI is not working. Please check the installation.")
