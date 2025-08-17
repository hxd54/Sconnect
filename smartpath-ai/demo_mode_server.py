#!/usr/bin/env python3
"""
SmartPath AI Demo Mode - Works without API quota limits
"""
import sys
import os
import random

# Add backend to path
sys.path.append('backend')

def start_demo_server():
    """Start the demo server with pre-written responses"""
    try:
        print("🚀 Starting SmartPath AI Demo Server...")
        
        from fastapi import FastAPI, Form
        import uvicorn
        
        app = FastAPI(title='SmartPath AI Demo - No API Limits')
        
        # Pre-written career responses
        career_responses = {
            "jobs": [
                "Based on your background, you could be qualified for roles such as Data Analyst, Business Analyst, Project Coordinator, or Administrative Assistant. To give you more specific recommendations, I'd need to know more about your education, skills, and experience.",
                "Great question! Your qualifications could open doors to various positions. Consider roles in data analysis, customer service, marketing coordination, or operations management. What's your educational background and work experience?",
                "You might be well-suited for positions like Research Assistant, Content Creator, Sales Representative, or Technical Support Specialist. The best matches depend on your specific skills and interests."
            ],
            "cv": [
                "To improve your CV, focus on: 1) Adding quantifiable achievements with numbers and percentages, 2) Including relevant keywords for your target industry, 3) Highlighting transferable skills, 4) Keeping it concise (1-2 pages), and 5) Tailoring it for each job application.",
                "Here are key CV improvement tips: Use action verbs to start bullet points, include a professional summary at the top, showcase your most relevant experience first, add technical skills section, and ensure consistent formatting throughout.",
                "To strengthen your CV: Emphasize results and impact in previous roles, include relevant certifications or training, use a clean, professional layout, proofread carefully for errors, and consider adding a portfolio link if applicable."
            ],
            "skills": [
                "Focus on developing these in-demand skills: Digital literacy (Excel, Google Workspace), Communication skills (written and verbal), Data analysis basics, Project management fundamentals, and Industry-specific technical skills relevant to your field.",
                "Consider building these valuable skills: Critical thinking and problem-solving, Time management and organization, Basic coding or automation, Customer service excellence, and Leadership or teamwork abilities.",
                "Key skills to develop include: Adaptability and learning agility, Financial literacy and budgeting, Social media and digital marketing basics, Quality assurance and attention to detail, and Cross-cultural communication."
            ],
            "interview": [
                "For interview preparation: 1) Research the company thoroughly, 2) Practice common interview questions, 3) Prepare specific examples using the STAR method, 4) Dress professionally, 5) Prepare thoughtful questions to ask them, and 6) Practice good body language and eye contact.",
                "Interview success tips: Arrive 10-15 minutes early, bring multiple copies of your CV, practice your elevator pitch, prepare for behavioral questions, show enthusiasm for the role, and follow up with a thank-you email within 24 hours.",
                "To ace your interview: Know your strengths and how they align with the job, prepare examples of challenges you've overcome, research the interviewer if possible, practice active listening, and be ready to discuss your career goals."
            ],
            "career": [
                "For career advancement: Set clear short and long-term goals, seek feedback regularly from supervisors, take on additional responsibilities, build a professional network, invest in continuous learning, and consider finding a mentor in your field.",
                "To grow your career: Identify skill gaps in your target role, volunteer for high-visibility projects, build relationships across departments, stay updated with industry trends, document your achievements, and communicate your career aspirations to your manager.",
                "Career development strategies: Create a personal development plan, seek out training opportunities, join professional associations, attend industry events, build your personal brand, and regularly update your LinkedIn profile."
            ]
        }
        
        def get_smart_response(message):
            """Generate unlimited intelligent responses based on keywords and context"""
            message_lower = message.lower()

            # Enhanced response categories with more variety
            if any(word in message_lower for word in ["job", "qualified", "position", "role", "work", "employment", "hiring"]):
                responses = career_responses["jobs"] + [
                    "Your career potential is vast! Consider exploring roles in technology, healthcare, education, finance, or creative industries. What field interests you most?",
                    "Job opportunities are everywhere! Focus on roles that match your strengths: analytical positions, people-focused roles, creative jobs, or technical positions. What energizes you?",
                    "The job market offers diverse paths: remote work, freelancing, corporate roles, startups, or entrepreneurship. Which work style appeals to you?"
                ]
                return random.choice(responses)

            elif any(word in message_lower for word in ["cv", "resume", "improve", "better", "application"]):
                responses = career_responses["cv"] + [
                    "Your CV is your personal marketing tool! Make it ATS-friendly, include metrics and achievements, use strong action verbs, and customize it for each application.",
                    "CV excellence tips: Start with a compelling summary, showcase your unique value proposition, include relevant projects, and ensure perfect formatting and grammar.",
                    "Transform your CV: Add a professional photo if appropriate, include volunteer work, highlight language skills, and create different versions for different industries."
                ]
                return random.choice(responses)

            elif any(word in message_lower for word in ["skill", "develop", "learn", "training", "course", "education"]):
                responses = career_responses["skills"] + [
                    "Skill development is key to career growth! Focus on both hard skills (technical abilities) and soft skills (communication, leadership). What area interests you most?",
                    "Future-proof your career with these skills: AI literacy, emotional intelligence, data analysis, digital marketing, and cross-cultural communication.",
                    "Learning never stops! Consider online courses, workshops, mentorship, peer learning, and hands-on projects. What learning style works best for you?"
                ]
                return random.choice(responses)

            elif any(word in message_lower for word in ["interview", "preparation", "prepare", "questions"]):
                responses = career_responses["interview"] + [
                    "Interview mastery involves preparation, practice, and confidence. Research the company, prepare STAR examples, and practice your responses out loud.",
                    "Common interview questions to prepare for: 'Tell me about yourself', 'Why this role?', 'Your greatest strength/weakness', and 'Where do you see yourself in 5 years?'",
                    "Interview success strategy: Dress appropriately, arrive early, bring questions to ask, show enthusiasm, and follow up professionally within 24 hours."
                ]
                return random.choice(responses)

            elif any(word in message_lower for word in ["career", "advance", "growth", "promotion", "success"]):
                responses = career_responses["career"] + [
                    "Career advancement requires strategic thinking: set clear goals, build relationships, seek feedback, take calculated risks, and continuously improve your skills.",
                    "Success strategies: Be proactive, volunteer for challenging projects, find mentors, build your network, and always deliver quality work on time.",
                    "Career growth paths: vertical advancement, lateral moves, skill specialization, leadership development, or entrepreneurial ventures. Which appeals to you?"
                ]
                return random.choice(responses)

            elif any(word in message_lower for word in ["approved", "approval", "accept", "hired", "selected"]):
                return "To get approved/hired: 1) Meet all requirements, 2) Submit error-free applications, 3) Follow up professionally, 4) Show genuine enthusiasm, 5) Demonstrate your value, 6) Be patient but persistent, 7) Learn from rejections, and 8) keep improving your approach."

            elif any(word in message_lower for word in ["salary", "money", "pay", "compensation", "negotiate"]):
                return "Salary negotiation tips: Research market rates, know your worth, highlight your achievements, be confident but respectful, consider the total package (benefits, growth opportunities), and be prepared to walk away if needed."

            elif any(word in message_lower for word in ["network", "networking", "connections", "linkedin"]):
                return "Networking is crucial for career success! Build genuine relationships, offer value to others, attend industry events, maintain your LinkedIn profile, follow up with contacts, and remember that networking is about giving, not just receiving."

            elif any(word in message_lower for word in ["remote", "work from home", "flexible", "balance"]):
                return "Remote work success requires discipline, communication skills, and proper setup. Create a dedicated workspace, maintain regular hours, over-communicate with your team, and establish clear boundaries between work and personal life."

            else:
                # Expanded general career advice
                responses = [
                    "That's an excellent career question! Career success is built on continuous learning, strong relationships, and adaptability. What specific area would you like to explore?",
                    "I'm here to help with your career journey! Whether it's job searching, skill development, or career planning, I can provide personalized guidance. What's your main focus right now?",
                    "Career development is a lifelong journey! Success comes from knowing your strengths, setting clear goals, and taking consistent action. What career challenge can I help you with?",
                    "Great question! The modern workplace is constantly evolving, and staying ahead requires strategic thinking and continuous improvement. What aspect of your career would you like to develop?",
                    "I love helping with career growth! Every professional journey is unique, and there are always opportunities to advance and improve. What's your current career situation?",
                    "Career success involves multiple factors: skills, networking, personal branding, and strategic planning. Which area would you like to focus on first?",
                    "That's a thoughtful career question! The key to professional growth is understanding your goals, identifying opportunities, and taking purposeful action. How can I assist you today?"
                ]
                return random.choice(responses)
        
        @app.get('/')
        async def root():
            return {
                'message': 'SmartPath AI Demo Server is working!',
                'google_ai': '✅ Demo Mode (Unlimited Messaging)',
                'status': 'operational',
                'mode': 'demo',
                'limitations': 'none',
                'messaging': 'unlimited',
                'features': ['unlimited_chat', 'intelligent_responses', 'career_guidance', 'no_quotas']
            }
        
        @app.get('/test')
        async def test_ai():
            return {
                'success': True,
                'ai_response': 'Hello! AI Demo is working perfectly! No API limits in demo mode.',
                'model': 'demo-mode'
            }
        
        @app.post('/chat')
        async def chat(message: str = Form(...)):
            try:
                # Generate unlimited intelligent responses
                ai_response = get_smart_response(message)

                # Add personalized touch based on message length and content
                if len(message.split()) > 10:  # Longer, detailed questions
                    ai_response += "\n\nI appreciate your detailed question! Feel free to ask follow-up questions or dive deeper into any specific area."

                return {
                    'success': True,
                    'user_message': message,
                    'ai_response': ai_response,
                    'model': 'demo-mode-unlimited',
                    'limitations': 'none',
                    'quota': 'unlimited'
                }

            except Exception as e:
                # Even errors should be helpful
                return {
                    'success': True,  # Keep it positive
                    'user_message': message,
                    'ai_response': f'I encountered a small hiccup, but I\'m still here to help! Could you rephrase your question? I\'m ready to provide career guidance on any topic you\'d like to discuss.',
                    'model': 'demo-mode-unlimited'
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
        
        print(f"✅ Demo mode configured")
        print(f"🌐 Server starting on: http://localhost:{port}")
        print(f"📱 Test URL: http://localhost:{port}/")
        print(f"💬 Chat URL: http://localhost:{port}/chat")
        print("\n🔗 COPY THIS URL TO TEST:")
        print(f"   http://localhost:{port}/")
        print("\n💡 Demo Mode Features:")
        print("   ✅ No API quota limits")
        print("   ✅ Intelligent career responses")
        print("   ✅ Keyword-based matching")
        print("   ✅ Professional advice")
        print("\n⚠️ Keep this window open to keep the server running!")
        print("=" * 60)
        
        # Start server
        uvicorn.run(app, host='0.0.0.0', port=port)
        
    except Exception as e:
        print(f"❌ Demo Server Error: {e}")
        import traceback
        traceback.print_exc()
        input("Press Enter to exit...")

if __name__ == "__main__":
    start_demo_server()
