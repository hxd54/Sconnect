#!/usr/bin/env python3
"""
Working SmartPath AI Chat Server - Guaranteed to work
"""
import sys
import os

# Add backend to path
sys.path.append('backend')

def start_working_server():
    """Start the working server"""
    try:
        print("🚀 Starting SmartPath AI Chat Server...")
        
        # Import required modules
        import google.generativeai as genai
        from fastapi import FastAPI, Form
        import uvicorn
        
        # Configuration
        GOOGLE_API_KEY = 'AIzaSyD7gjE9qQFCOMth91BfI2zOXwUHu7DxP3A'
        GEMINI_MODEL = 'gemini-1.5-flash'
        
        # Test Google AI first
        print("🔍 Testing Google AI...")
        genai.configure(api_key=GOOGLE_API_KEY)
        test_model = genai.GenerativeModel(GEMINI_MODEL)
        test_response = test_model.generate_content("Hello")
        print(f"✅ Google AI working: {test_response.text[:30]}...")
        
        # Create FastAPI app
        app = FastAPI(title='SmartPath AI Chat - Working Version')
        
        @app.get('/')
        async def root():
            return {
                'message': 'SmartPath AI Chat Server is working!',
                'google_ai': '✅ Connected',
                'status': 'operational'
            }
        
        @app.get('/test')
        async def test_ai():
            try:
                model = genai.GenerativeModel(GEMINI_MODEL)
                response = model.generate_content('Say Hello! AI is working perfectly!')
                
                return {
                    'success': True,
                    'ai_response': response.text.strip(),
                    'model': GEMINI_MODEL
                }
            except Exception as e:
                return {
                    'success': False,
                    'error': str(e)
                }
        
        @app.post('/chat')
        async def chat(message: str = Form(...)):
            try:
                model = genai.GenerativeModel(GEMINI_MODEL)
                
                prompt = f'''
                You are SmartPath AI, an expert career counselor and job matching specialist.
                
                Your role:
                - Help users with career guidance and job search
                - Analyze CVs and provide insights
                - Recommend suitable jobs and career paths
                - Suggest skills to develop
                - Provide course recommendations
                - Answer questions about career development
                
                User question: {message}
                
                Provide helpful, specific career advice in a friendly, professional manner.
                Keep your response concise but informative (under 200 words).
                '''
                
                response = model.generate_content(prompt)
                
                return {
                    'success': True,
                    'user_message': message,
                    'ai_response': response.text.strip(),
                    'model': GEMINI_MODEL
                }
                
            except Exception as e:
                return {
                    'success': False,
                    'user_message': message,
                    'ai_response': f'Error: {str(e)}',
                    'error': str(e)
                }
        
        # Find available port
        import socket
        def find_free_port():
            with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
                s.bind(('', 0))
                s.listen(1)
                port = s.getsockname()[1]
            return port
        
        port = find_free_port()
        
        print(f"✅ Google AI configured and tested")
        print(f"🌐 Server starting on: http://localhost:{port}")
        print(f"📱 Test URL: http://localhost:{port}/")
        print(f"💬 Chat URL: http://localhost:{port}/chat")
        print("\n🔗 COPY THIS URL TO TEST:")
        print(f"   http://localhost:{port}/")
        print("\n⚠️ Keep this window open to keep the server running!")
        print("=" * 60)
        
        # Start server
        uvicorn.run(app, host='0.0.0.0', port=port)
        
    except ImportError as e:
        print(f"❌ Import Error: {e}")
        print("💡 Please install: pip install google-generativeai fastapi uvicorn python-multipart")
        input("Press Enter to exit...")
        
    except Exception as e:
        print(f"❌ Server Error: {e}")
        import traceback
        traceback.print_exc()
        input("Press Enter to exit...")

if __name__ == "__main__":
    start_working_server()
