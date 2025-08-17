#!/usr/bin/env python3
"""
Start the chat server on any available port
"""
import socket
import sys
import os

# Add backend to path
sys.path.append('backend')
os.chdir('backend')

def find_free_port():
    """Find a free port"""
    with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
        s.bind(('', 0))
        s.listen(1)
        port = s.getsockname()[1]
    return port

def start_server():
    """Start the server on a free port"""
    try:
        import google.generativeai as genai
        from fastapi import FastAPI, Form
        import uvicorn
        
        # Configuration
        GOOGLE_API_KEY = 'AIzaSyD7gjE9qQFCOMth91BfI2zOXwUHu7DxP3A'
        GEMINI_MODEL = 'gemini-1.5-flash'
        
        # Configure Google AI
        genai.configure(api_key=GOOGLE_API_KEY)
        
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
        
        # Find a free port
        port = find_free_port()
        
        print('🚀 Starting SmartPath AI Chat Server...')
        print('✅ Google AI configured')
        print(f'🌐 Server will be available at: http://localhost:{port}')
        print(f'📱 Test URL: http://localhost:{port}/')
        print(f'💬 Chat URL: http://localhost:{port}/chat')
        print('\n🔗 Copy this URL to test:')
        print(f'   http://localhost:{port}/')
        print('\n⚠️ Keep this window open to keep the server running!')
        
        uvicorn.run(app, host='0.0.0.0', port=port)
        
    except Exception as e:
        print(f'❌ Error starting server: {e}')
        input('Press Enter to exit...')

if __name__ == "__main__":
    start_server()
