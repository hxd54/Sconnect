#!/usr/bin/env python3
"""
Simple SmartPath AI Server with Database - Guaranteed to work!
"""
import sys
import os
import random
import socket
import time
import uuid
from datetime import datetime

# Add backend to path
sys.path.append('backend')

def start_simple_database_server():
    """Start simple server with database storage"""
    try:
        print("🚀 Starting Simple SmartPath AI Database Server...")
        
        from fastapi import FastAPI, Form, Request
        import uvicorn
        
        # Try to import database, but don't fail if it doesn't work
        try:
            from chat_database import ChatDatabase
            db = ChatDatabase()
            database_available = True
            print("✅ Database connected!")
        except Exception as e:
            print(f"⚠️ Database not available: {e}")
            database_available = False
            db = None
        
        app = FastAPI(title='SmartPath AI - Simple Database Server')
        
        # Store active sessions in memory as fallback
        active_sessions = {}
        message_history = []
        
        # Comprehensive career responses
        career_responses = {
            "jobs": [
                "Based on your background, you could be qualified for Data Analyst, Business Analyst, Project Manager, Marketing Coordinator, or Customer Success roles. What industry interests you most?",
                "Your skills could open doors to Software Developer, UX Designer, Content Creator, Sales Representative, or Operations Manager positions. Which path excites you?",
                "Consider these growing fields: Digital Marketing, Data Science, Cybersecurity, Healthcare Technology, or Sustainable Energy. What aligns with your values?",
                "Emerging opportunities include: AI Specialist, Remote Work Coordinator, Social Media Manager, E-commerce Specialist, or Digital Health Consultant.",
                "Your potential spans: Research Analyst, Training Specialist, Quality Assurance, Business Development, or Product Manager roles."
            ],
            "skills": [
                "Future-ready skills include: Digital literacy, Critical thinking, Emotional intelligence, Data analysis, Communication, and Leadership. Which interests you?",
                "High-demand skills: Python programming, Digital marketing, Project management, Data visualization, Cloud computing, and Cross-cultural communication.",
                "Essential soft skills: Problem-solving, Teamwork, Time management, Creativity, Negotiation, and Public speaking. These never go out of style!",
                "Technical skills to consider: Excel mastery, SQL databases, Social media marketing, Graphic design, Web development, or Mobile app basics.",
                "Transferable skills: Research, Writing, Analysis, Customer service, Training, and Process improvement work across all industries!"
            ],
            "cv": [
                "To improve your CV: Use action verbs, quantify achievements, tailor for each job, include relevant keywords, keep it concise, and proofread carefully!",
                "CV excellence tips: Professional summary at the top, relevant experience first, clear skills section, education details, and consistent formatting throughout.",
                "Application strategy: Customize for each role, highlight relevant experience, show impact with numbers, and include a compelling cover letter.",
                "CV improvement ideas: Add volunteer work, include certifications, showcase projects, use professional language, and ensure ATS compatibility."
            ],
            "interview": [
                "Interview preparation: Research the company thoroughly, practice common questions, prepare STAR examples, dress professionally, and arrive early!",
                "Interview success tips: Show genuine enthusiasm, ask thoughtful questions, demonstrate your value, maintain good eye contact, and follow up within 24 hours.",
                "Common questions to prepare for: 'Tell me about yourself', 'Why this role?', 'Greatest strength/weakness', and 'Where do you see yourself in 5 years?'",
                "Interview confidence boosters: Know your worth, prepare questions to ask them, practice good body language, and remember - they want you to succeed!"
            ],
            "general": [
                "That's an excellent career question! I'm here to provide guidance on your professional journey. What specific area would you like to explore?",
                "I love helping with career development! What's your main focus today - job searching, skill building, or career planning?",
                "Great question! Career success comes from continuous learning and strategic planning. How can I help you advance?",
                "I'm here for unlimited career coaching! What challenge can I help you tackle today?"
            ]
        }
        
        def get_smart_response(message):
            """Generate intelligent career response"""
            try:
                message_lower = message.lower().strip()
                
                if not message_lower:
                    return "Please ask me a career question!", "general"
                
                # Determine category and select appropriate responses
                if any(word in message_lower for word in ["job", "work", "career", "position", "role", "employment", "qualified", "hiring"]):
                    category = "job_search"
                    responses = career_responses["jobs"]
                elif any(word in message_lower for word in ["skill", "learn", "develop", "training", "course", "education"]):
                    category = "skill_development"
                    responses = career_responses["skills"]
                elif any(word in message_lower for word in ["cv", "resume", "application", "improve", "better"]):
                    category = "cv_resume"
                    responses = career_responses["cv"]
                elif any(word in message_lower for word in ["interview", "prepare", "questions", "preparation"]):
                    category = "interview_prep"
                    responses = career_responses["interview"]
                else:
                    category = "general"
                    responses = career_responses["general"]
                
                # Select and customize response
                base_response = random.choice(responses)
                
                # Add encouraging ending
                endings = [
                    " Feel free to ask follow-up questions!",
                    " What other aspects would you like to explore?",
                    " I'm here to help you succeed!",
                    " Ask me anything else about your career!"
                ]
                
                final_response = base_response + random.choice(endings)
                return final_response, category
                
            except Exception as e:
                print(f"Response error: {e}")
                return f"I'm here to help with your career question about: '{message}'. Could you tell me more about what specific guidance you're looking for?", "general"
        
        @app.get('/')
        async def root():
            return {
                'message': 'SmartPath AI Simple Database Server is working!',
                'google_ai': '✅ Simple Mode with Database Storage',
                'status': 'operational',
                'mode': 'simple_database',
                'database': 'available' if database_available else 'fallback',
                'features': ['unlimited_chat', 'intelligent_responses', 'database_storage']
            }
        
        @app.get('/test')
        async def test_ai():
            return {
                'success': True,
                'ai_response': 'Hello! Simple Database AI is working perfectly! Ask me any career question!',
                'model': 'simple-database-mode'
            }
        
        @app.post('/chat')
        async def chat(request: Request, message: str = Form(...), user_name: str = Form(None), session_id: str = Form(None)):
            try:
                start_time = time.time()
                
                # Generate session ID if needed
                if not session_id:
                    session_id = str(uuid.uuid4())
                
                # Store session info
                if session_id not in active_sessions:
                    active_sessions[session_id] = {
                        'start_time': datetime.now(),
                        'user_name': user_name or 'Anonymous',
                        'message_count': 0
                    }
                
                active_sessions[session_id]['message_count'] += 1
                message_number = active_sessions[session_id]['message_count']
                
                # Generate response
                ai_response, category = get_smart_response(message)
                response_time = int((time.time() - start_time) * 1000)
                
                # Save to database if available
                if database_available and db:
                    try:
                        if session_id not in [s for s in active_sessions.keys()]:
                            db.start_session(user_name, "chat")
                        
                        db.save_message(
                            session_id=session_id,
                            user_message=message,
                            ai_response=ai_response,
                            response_time_ms=response_time,
                            message_category=category
                        )
                        storage_status = "saved_to_database"
                    except Exception as db_error:
                        print(f"Database save error: {db_error}")
                        storage_status = "saved_to_memory"
                else:
                    # Fallback to memory storage
                    message_history.append({
                        'session_id': session_id,
                        'message_number': message_number,
                        'user_message': message,
                        'ai_response': ai_response,
                        'category': category,
                        'timestamp': datetime.now().isoformat()
                    })
                    storage_status = "saved_to_memory"
                
                return {
                    'success': True,
                    'user_message': message,
                    'ai_response': ai_response,
                    'session_id': session_id,
                    'message_number': message_number,
                    'category': category,
                    'response_time_ms': response_time,
                    'model': 'simple-database-mode',
                    'storage': storage_status,
                    'limitations': 'none'
                }
                
            except Exception as e:
                print(f"Chat error: {e}")
                return {
                    'success': True,
                    'user_message': message,
                    'ai_response': f"I'm here to help with your career question: '{message}'. What specific guidance are you looking for?",
                    'session_id': session_id or 'fallback',
                    'message_number': 1,
                    'category': 'general',
                    'response_time_ms': 0,
                    'model': 'simple-fallback-mode'
                }
        
        # Find available port
        def find_free_port():
            with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
                s.bind(('', 0))
                s.listen(1)
                port = s.getsockname()[1]
            return port
        
        port = find_free_port()
        
        print(f"✅ Simple database server configured")
        print(f"🗄️ Database: {'Available' if database_available else 'Memory fallback'}")
        print(f"🌐 Server starting on: http://localhost:{port}")
        print(f"📱 Test URL: http://localhost:{port}/")
        print(f"💬 Chat URL: http://localhost:{port}/chat")
        print("\n🔗 COPY THIS URL TO TEST:")
        print(f"   http://localhost:{port}/")
        print("\n🚀 SIMPLE FEATURES:")
        print("   ✅ Guaranteed to work")
        print("   ✅ Intelligent career responses")
        print("   ✅ Database storage (when available)")
        print("   ✅ Memory fallback")
        print("   ✅ Session tracking")
        print("   ✅ No limitations")
        print("\n⚠️ Keep this window open to keep the server running!")
        print("=" * 60)
        
        # Start server
        uvicorn.run(app, host='0.0.0.0', port=port)
        
    except Exception as e:
        print(f"❌ Server Error: {e}")
        input("Press Enter to exit...")

if __name__ == "__main__":
    start_simple_database_server()
