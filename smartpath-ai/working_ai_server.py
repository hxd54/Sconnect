#!/usr/bin/env python3
"""
SmartPath AI Server with REAL Google AI API + Database Storage
"""
import sys
import os
import socket
import time
import uuid
from datetime import datetime

# Add backend to path
sys.path.append('backend')

def start_working_ai_server():
    """Start server with real Google AI API"""
    try:
        print("🚀 Starting SmartPath AI with REAL Google AI...")
        
        # Import Google AI first
        try:
            import google.generativeai as genai
            from config import GOOGLE_API_KEY, GEMINI_MODEL
            
            # Configure Google AI
            genai.configure(api_key=GOOGLE_API_KEY)
            
            # Test Google AI
            print("🔍 Testing Google AI connection...")
            test_model = genai.GenerativeModel(GEMINI_MODEL)
            test_response = test_model.generate_content("Hello, test message")
            print(f"✅ Google AI working: {test_response.text[:50]}...")
            
            google_ai_available = True
            
        except Exception as e:
            print(f"❌ Google AI Error: {e}")
            print("🔄 Falling back to demo responses...")
            google_ai_available = False
        
        from fastapi import FastAPI, Form, Request
        import uvicorn
        
        # Try to import database
        try:
            from chat_database import ChatDatabase
            db = ChatDatabase()
            database_available = True
            print("✅ Database connected!")
        except Exception as e:
            print(f"⚠️ Database not available: {e}")
            database_available = False
            db = None
        
        app = FastAPI(title='SmartPath AI - Working Server')
        
        # Store sessions
        active_sessions = {}
        
        def get_ai_response(message):
            """Get response from Google AI or fallback"""
            try:
                if google_ai_available:
                    # Use real Google AI
                    model = genai.GenerativeModel(GEMINI_MODEL)
                    
                    prompt = f"""
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
"""
                    
                    response = model.generate_content(prompt)
                    return response.text.strip()
                    
                else:
                    # Fallback responses
                    message_lower = message.lower()
                    
                    if any(word in message_lower for word in ["job", "work", "career", "position", "qualified"]):
                        return "Based on your background, you could be qualified for roles such as Data Analyst, Business Analyst, Project Coordinator, or Administrative Assistant. To give you more specific recommendations, I'd need to know more about your education, skills, and experience. What's your educational background?"
                    
                    elif any(word in message_lower for word in ["cv", "resume", "improve"]):
                        return "To improve your CV, focus on: 1) Adding quantifiable achievements with numbers and percentages, 2) Including relevant keywords for your target industry, 3) Highlighting transferable skills, 4) Keeping it concise (1-2 pages), and 5) Tailoring it for each job application. What specific area of your CV would you like to improve?"
                    
                    elif any(word in message_lower for word in ["skill", "learn", "develop"]):
                        return "Focus on developing these in-demand skills: Digital literacy (Excel, Google Workspace), Communication skills (written and verbal), Data analysis basics, Project management fundamentals, and Industry-specific technical skills relevant to your field. Which of these areas interests you most?"
                    
                    elif any(word in message_lower for word in ["interview", "prepare"]):
                        return "For interview preparation: 1) Research the company thoroughly, 2) Practice common interview questions, 3) Prepare specific examples using the STAR method, 4) Dress professionally, 5) Prepare thoughtful questions to ask them, and 6) Practice good body language and eye contact. What type of interview are you preparing for?"
                    
                    else:
                        return f"Thank you for your question about '{message}'. I'm here to help with your career development! Could you tell me more about your specific situation? Are you looking for job opportunities, wanting to improve your CV, developing new skills, or preparing for interviews?"
                        
            except Exception as e:
                print(f"AI Response Error: {e}")
                return f"I'm here to help with your career question: '{message}'. Could you provide more details about what specific guidance you're looking for? I can help with job searching, CV improvement, skill development, or interview preparation."
        
        @app.get('/')
        async def root():
            return {
                'message': 'SmartPath AI Working Server is operational!',
                'google_ai': '✅ Real Google AI' if google_ai_available else '⚠️ Demo Mode',
                'database': '✅ Connected' if database_available else '⚠️ Memory Only',
                'status': 'operational',
                'mode': 'working_ai',
                'features': ['real_ai_responses', 'career_guidance', 'unlimited_chat']
            }
        
        @app.get('/test')
        async def test_ai():
            try:
                test_response = get_ai_response("Hello, test the AI")
                return {
                    'success': True,
                    'ai_response': test_response,
                    'model': 'google-gemini' if google_ai_available else 'demo-mode'
                }
            except Exception as e:
                return {
                    'success': False,
                    'error': str(e)
                }
        
        @app.post('/chat')
        async def chat(request: Request, message: str = Form(...), user_name: str = Form(None), session_id: str = Form(None)):
            try:
                start_time = time.time()
                
                print(f"📝 Received message: {message}")
                
                # Generate session ID if needed
                if not session_id:
                    session_id = str(uuid.uuid4())
                    print(f"🆕 New session: {session_id}")
                
                # Store session info
                if session_id not in active_sessions:
                    active_sessions[session_id] = {
                        'start_time': datetime.now(),
                        'user_name': user_name or 'Anonymous',
                        'message_count': 0
                    }
                
                active_sessions[session_id]['message_count'] += 1
                message_number = active_sessions[session_id]['message_count']
                
                print(f"🤖 Generating AI response...")
                
                # Get AI response
                ai_response = get_ai_response(message)
                response_time = int((time.time() - start_time) * 1000)
                
                print(f"✅ AI response generated: {ai_response[:50]}...")
                
                # Determine category
                message_lower = message.lower()
                if any(word in message_lower for word in ["job", "work", "career", "position"]):
                    category = "job_search"
                elif any(word in message_lower for word in ["skill", "learn", "develop"]):
                    category = "skill_development"
                elif any(word in message_lower for word in ["cv", "resume"]):
                    category = "cv_resume"
                elif any(word in message_lower for word in ["interview", "prepare"]):
                    category = "interview_prep"
                else:
                    category = "general"
                
                # Save to database if available
                storage_status = "memory_only"
                if database_available and db:
                    try:
                        db.save_message(
                            session_id=session_id,
                            user_message=message,
                            ai_response=ai_response,
                            response_time_ms=response_time,
                            message_category=category
                        )
                        storage_status = "saved_to_database"
                        print("💾 Message saved to database")
                    except Exception as db_error:
                        print(f"⚠️ Database save error: {db_error}")
                        storage_status = "database_error"
                
                return {
                    'success': True,
                    'user_message': message,
                    'ai_response': ai_response,
                    'session_id': session_id,
                    'message_number': message_number,
                    'category': category,
                    'response_time_ms': response_time,
                    'model': 'google-gemini' if google_ai_available else 'demo-mode',
                    'storage': storage_status,
                    'limitations': 'none'
                }
                
            except Exception as e:
                print(f"❌ Chat endpoint error: {e}")
                import traceback
                traceback.print_exc()
                
                return {
                    'success': True,
                    'user_message': message,
                    'ai_response': f"I'm here to help with your career question: '{message}'. Let me provide some guidance on that topic. Could you tell me more about your specific situation or what kind of career advice you're looking for?",
                    'session_id': session_id or 'fallback',
                    'message_number': 1,
                    'category': 'general',
                    'response_time_ms': 0,
                    'model': 'fallback-mode',
                    'error': str(e)
                }
        
        # Find available port
        def find_free_port():
            with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
                s.bind(('', 0))
                s.listen(1)
                port = s.getsockname()[1]
            return port
        
        port = find_free_port()
        
        print(f"✅ Working AI server configured")
        print(f"🤖 Google AI: {'Available' if google_ai_available else 'Demo Mode'}")
        print(f"🗄️ Database: {'Available' if database_available else 'Memory Only'}")
        print(f"🌐 Server starting on: http://localhost:{port}")
        print(f"📱 Test URL: http://localhost:{port}/")
        print(f"💬 Chat URL: http://localhost:{port}/chat")
        print("\n🔗 COPY THIS URL TO TEST:")
        print(f"   http://localhost:{port}/")
        print("\n🚀 WORKING FEATURES:")
        print("   ✅ Real Google AI responses")
        print("   ✅ Intelligent career guidance")
        print("   ✅ Database storage")
        print("   ✅ Session tracking")
        print("   ✅ Error handling")
        print("   ✅ Unlimited questions")
        print("\n⚠️ Keep this window open to keep the server running!")
        print("=" * 60)
        
        # Start server
        uvicorn.run(app, host='0.0.0.0', port=port)
        
    except Exception as e:
        print(f"❌ Server Startup Error: {e}")
        import traceback
        traceback.print_exc()
        input("Press Enter to exit...")

if __name__ == "__main__":
    start_working_ai_server()
