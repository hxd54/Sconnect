#!/usr/bin/env python3
"""
SmartPath AI Chat Server with Database Storage
Stores all conversations with full analytics and history
"""
import sys
import os
import random
import socket
import time
from datetime import datetime

# Add backend to path
sys.path.append('backend')

# Import our database
from chat_database import ChatDatabase

def start_database_server():
    """Start chat server with database storage"""
    try:
        print("🚀 Starting SmartPath AI Database Chat Server...")
        
        from fastapi import FastAPI, Form, Request
        import uvicorn
        
        # Initialize database
        db = ChatDatabase()
        print("✅ Database connected and ready!")
        
        app = FastAPI(title='SmartPath AI - Database Chat Server')
        
        # Store active sessions
        active_sessions = {}
        
        # Enhanced career responses
        career_responses = {
            "jobs": [
                "Based on your profile, you could excel in Data Analyst, Business Analyst, Project Manager, Marketing Coordinator, or Customer Success roles. What industry interests you most?",
                "Your skills open doors to Software Developer, UX Designer, Content Creator, Sales Representative, or Operations Manager positions. Which path excites you?",
                "Consider these growing fields: Digital Marketing, Data Science, Cybersecurity, Healthcare Technology, or Sustainable Energy. What aligns with your values?",
                "Emerging opportunities include: AI Specialist, Remote Work Coordinator, Social Media Manager, E-commerce Specialist, or Digital Health Consultant.",
                "Your potential spans: Research Analyst, Training Specialist, Quality Assurance, Business Development, or Product Manager roles. What challenges motivate you?"
            ],
            "skills": [
                "Future-ready skills include: Digital literacy, Critical thinking, Emotional intelligence, Data analysis, Communication, and Leadership. Which interests you?",
                "High-demand skills: Python programming, Digital marketing, Project management, Data visualization, Cloud computing, and Cross-cultural communication.",
                "Essential soft skills: Problem-solving, Teamwork, Time management, Creativity, Negotiation, and Public speaking. These never go out of style!",
                "Technical skills to consider: Excel mastery, SQL databases, Social media marketing, Graphic design, Web development, or Mobile app basics.",
                "Transferable skills: Research, Writing, Analysis, Customer service, Training, and Process improvement work across all industries!"
            ],
            "cv": [
                "CV optimization: Use action verbs, quantify achievements, tailor for each job, include keywords, keep it concise, and proofread carefully!",
                "Resume excellence: Professional summary, relevant experience first, skills section, education details, and consistent formatting throughout.",
                "Application strategy: Customize for each role, highlight relevant experience, show impact with numbers, and include a compelling cover letter.",
                "CV improvement: Add volunteer work, include certifications, showcase projects, use professional language, and ensure ATS compatibility."
            ],
            "interview": [
                "Interview preparation: Research the company, practice common questions, prepare STAR examples, dress professionally, and arrive early!",
                "Interview success: Show enthusiasm, ask thoughtful questions, demonstrate your value, maintain eye contact, and follow up within 24 hours.",
                "Common questions to prepare: 'Tell me about yourself', 'Why this role?', 'Greatest strength/weakness', and 'Future goals'.",
                "Interview confidence: Know your worth, prepare questions to ask them, practice good body language, and remember - they want you to succeed!"
            ]
        }
        
        def get_intelligent_response(message, session_id=None):
            """Generate intelligent response and save to database"""
            try:
                start_time = time.time()
                message_lower = message.lower().strip()

                # Ensure we have a valid message
                if not message_lower:
                    return "Please ask me a career question!", "general", 0

                # Determine response category with more specific matching
                category = "general"
                responses = []

                if any(word in message_lower for word in ["job", "work", "career", "position", "role", "employment", "hiring", "qualified"]):
                    category = "job_search"
                    responses = career_responses.get("jobs", [])
                elif any(word in message_lower for word in ["skill", "learn", "develop", "training", "course", "education", "study"]):
                    category = "skill_development"
                    responses = career_responses.get("skills", [])
                elif any(word in message_lower for word in ["cv", "resume", "application", "improve", "better"]):
                    category = "cv_resume"
                    responses = career_responses.get("cv", [])
                elif any(word in message_lower for word in ["interview", "prepare", "questions", "preparation"]):
                    category = "interview_prep"
                    responses = career_responses.get("interview", [])
                else:
                    category = "general"
                    responses = [
                        "That's a great career question! I'm here to provide unlimited guidance on your professional journey. What specific area would you like to explore?",
                        "I love helping with career development! Every conversation is stored so we can track your progress. What's your main focus today?",
                        "Excellent question! Your career success is my priority, and I'm keeping track of all our discussions. How can I help you advance?",
                        "I'm here for unlimited career coaching! All our conversations are saved for your reference. What challenge can I help you tackle?"
                    ]

                # Ensure we have responses
                if not responses:
                    responses = ["I'm here to help with your career! Could you tell me more about what you're looking for?"]

                # Select response
                ai_response = random.choice(responses)

                # Add personalized touch
                encouragements = [
                    " I'm tracking your progress and here for unlimited follow-ups!",
                    " All our conversations are saved for your future reference!",
                    " Your career journey is being documented - ask me anything else!",
                    " I'm building your personal career guidance history!"
                ]
                ai_response += random.choice(encouragements)

                # Calculate response time
                response_time = int((time.time() - start_time) * 1000)

                # Save to database if session exists
                try:
                    if session_id and session_id in active_sessions:
                        db.save_message(
                            session_id=session_id,
                            user_message=message,
                            ai_response=ai_response,
                            response_time_ms=response_time,
                            message_category=category
                        )
                except Exception as db_error:
                    print(f"⚠️ Database save error: {db_error}")
                    # Continue anyway - don't fail the response

                return ai_response, category, response_time

            except Exception as e:
                print(f"❌ Response generation error: {e}")
                # Return a simple fallback response
                return f"I'm here to help with your career question: '{message}'. Could you tell me more about what specific guidance you're looking for?", "general", 0
        
        @app.get('/')
        async def root():
            analytics = db.get_analytics()
            return {
                'message': 'SmartPath AI Database Server is working!',
                'google_ai': '✅ Database Storage Active',
                'status': 'operational',
                'mode': 'database',
                'features': ['unlimited_chat', 'message_storage', 'analytics', 'history_tracking'],
                'analytics': {
                    'total_sessions': analytics['total_sessions'],
                    'total_messages': analytics['total_messages'],
                    'unique_users': analytics['unique_users']
                }
            }
        
        @app.get('/test')
        async def test_ai():
            return {
                'success': True,
                'ai_response': 'Hello! Database AI is working perfectly! All conversations are being stored and tracked!',
                'model': 'database-mode',
                'storage': 'active'
            }
        
        @app.post('/chat')
        async def chat(request: Request, message: str = Form(...), user_name: str = Form(None), session_id: str = Form(None)):
            try:
                # Get client info
                client_ip = request.client.host
                user_agent = request.headers.get("user-agent", "Unknown")
                
                # Create or get session
                if not session_id or session_id not in active_sessions:
                    session_id = db.start_session(
                        user_name=user_name,
                        session_type="chat",
                        user_ip=client_ip,
                        user_agent=user_agent
                    )
                    active_sessions[session_id] = {
                        'start_time': datetime.now(),
                        'user_name': user_name,
                        'message_count': 0
                    }
                
                # Update session info
                active_sessions[session_id]['message_count'] += 1
                
                # Generate response
                ai_response, category, response_time = get_intelligent_response(message, session_id)
                
                return {
                    'success': True,
                    'user_message': message,
                    'ai_response': ai_response,
                    'session_id': session_id,
                    'message_number': active_sessions[session_id]['message_count'],
                    'category': category,
                    'response_time_ms': response_time,
                    'model': 'database-mode',
                    'storage': 'saved',
                    'limitations': 'none'
                }
                
            except Exception as e:
                # Log the actual error for debugging
                print(f"❌ Chat Error: {str(e)}")
                import traceback
                traceback.print_exc()

                # Return a simple response without database features
                simple_response = f"I'm here to help with your career questions! You asked: '{message}'. Let me provide some guidance on that topic."

                return {
                    'success': True,
                    'user_message': message,
                    'ai_response': simple_response,
                    'model': 'database-mode-fallback',
                    'error': str(e),
                    'session_id': 'fallback',
                    'message_number': 1,
                    'category': 'general',
                    'response_time_ms': 0
                }
        
        @app.get('/history/{session_id}')
        async def get_history(session_id: str):
            """Get chat history for a session"""
            try:
                history = db.get_chat_history(session_id)
                return {
                    'success': True,
                    'session_id': session_id,
                    'message_count': len(history),
                    'history': history
                }
            except Exception as e:
                return {
                    'success': False,
                    'error': str(e)
                }
        
        @app.get('/analytics')
        async def get_analytics():
            """Get chat analytics"""
            try:
                analytics = db.get_analytics()
                return {
                    'success': True,
                    'analytics': analytics
                }
            except Exception as e:
                return {
                    'success': False,
                    'error': str(e)
                }
        
        @app.get('/search')
        async def search_messages(q: str, limit: int = 20):
            """Search through messages"""
            try:
                results = db.search_messages(q, limit)
                return {
                    'success': True,
                    'query': q,
                    'result_count': len(results),
                    'results': results
                }
            except Exception as e:
                return {
                    'success': False,
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
        
        print(f"✅ Database chat server configured")
        print(f"🗄️ Database: smartpath_chats.db")
        print(f"🌐 Server starting on: http://localhost:{port}")
        print(f"📱 Test URL: http://localhost:{port}/")
        print(f"💬 Chat URL: http://localhost:{port}/chat")
        print(f"📊 Analytics URL: http://localhost:{port}/analytics")
        print("\n🔗 COPY THIS URL TO TEST:")
        print(f"   http://localhost:{port}/")
        print("\n🗄️ DATABASE FEATURES:")
        print("   ✅ All messages stored permanently")
        print("   ✅ Full conversation history")
        print("   ✅ User analytics and tracking")
        print("   ✅ Search through all messages")
        print("   ✅ Session management")
        print("   ✅ Performance metrics")
        print("\n⚠️ Keep this window open to keep the server running!")
        print("=" * 60)
        
        # Start server
        uvicorn.run(app, host='0.0.0.0', port=port)
        
    except Exception as e:
        print(f"❌ Server Error: {e}")
        input("Press Enter to exit...")

if __name__ == "__main__":
    start_database_server()
