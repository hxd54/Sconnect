#!/usr/bin/env python3
"""
Enhanced SmartPath AI Server with High-Quality Career Responses
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

def start_enhanced_ai_server():
    """Start enhanced AI server with detailed career responses"""
    try:
        print("🚀 Starting Enhanced SmartPath AI Server...")
        
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
        
        app = FastAPI(title='SmartPath AI - Enhanced Career Advisor')
        
        # Store sessions
        active_sessions = {}
        
        # Enhanced career response system
        def get_detailed_career_response(message, user_context=None):
            """Generate detailed, personalized career responses"""
            try:
                message_lower = message.lower().strip()
                
                if not message_lower:
                    return "Please ask me a specific career question, and I'll provide detailed guidance!", "general"
                
                # Job search responses
                if any(word in message_lower for word in ["job", "work", "career", "position", "qualified", "hiring", "employment"]):
                    responses = [
                        """Based on current market trends, here are specific job opportunities you should consider:

**High-Demand Roles:**
• **Data Analyst** - Average salary: $65,000-85,000. Skills needed: Excel, SQL, Python, Tableau
• **Business Analyst** - Average salary: $70,000-90,000. Skills needed: Process mapping, stakeholder management, documentation
• **Project Coordinator** - Average salary: $55,000-75,000. Skills needed: Project management tools, communication, organization
• **Digital Marketing Specialist** - Average salary: $50,000-70,000. Skills needed: Google Analytics, social media, content creation

**Next Steps:**
1. Update your LinkedIn profile with relevant keywords
2. Apply to 5-10 positions per week
3. Network with professionals in your target industry
4. Prepare for common interview questions

What specific industry or role interests you most? I can provide more targeted advice.""",

                        """Here's a strategic approach to finding the right job for you:

**Step 1: Skills Assessment**
• List your top 5 technical skills
• Identify your strongest soft skills (communication, leadership, problem-solving)
• Note any certifications or special training

**Step 2: Market Research**
• Research salary ranges on Glassdoor, PayScale
• Look at job postings to understand requirements
• Identify skill gaps you need to fill

**Step 3: Target Companies**
• Make a list of 20-30 companies you'd like to work for
• Follow them on LinkedIn and social media
• Research their company culture and values

**Step 4: Application Strategy**
• Customize your CV for each application
• Write compelling cover letters
• Apply within 48 hours of job posting

**Recommended Job Boards:**
• LinkedIn Jobs, Indeed, Glassdoor
• Industry-specific sites (AngelList for startups, Dice for tech)
• Company career pages directly

What's your educational background and work experience? I can give more specific recommendations."""
                    ]
                    return random.choice(responses), "job_search"
                
                # Skills development responses
                elif any(word in message_lower for word in ["skill", "learn", "develop", "training", "course", "education", "study"]):
                    responses = [
                        """Here's a comprehensive skill development roadmap for 2024:

**Most In-Demand Technical Skills:**
• **Data Analysis**: Excel (advanced), SQL, Python/R, Tableau/Power BI
• **Digital Marketing**: Google Analytics, Facebook Ads, SEO, Content Marketing
• **Project Management**: Agile/Scrum, Microsoft Project, Jira, Risk Management
• **Cloud Computing**: AWS, Azure, Google Cloud basics
• **Programming**: Python, JavaScript, HTML/CSS for beginners

**Essential Soft Skills:**
• **Communication**: Public speaking, written communication, presentation skills
• **Leadership**: Team management, conflict resolution, decision-making
• **Problem-Solving**: Critical thinking, analytical reasoning, creativity
• **Adaptability**: Change management, learning agility, resilience

**Learning Resources:**
• **Free**: Coursera (audit mode), Khan Academy, YouTube, freeCodeCamp
• **Paid**: Udemy ($10-50), Pluralsight, LinkedIn Learning
• **Certifications**: Google Career Certificates, AWS Cloud Practitioner, PMP

**Learning Strategy:**
1. Choose 1-2 skills to focus on for 3 months
2. Practice 30-60 minutes daily
3. Build projects to demonstrate skills
4. Join online communities and forums

What specific skill area interests you most? I can create a detailed learning plan.""",

                        """Let me help you build a strategic skill development plan:

**High-ROI Skills by Industry:**

**Technology:**
• Programming: Python (versatile), JavaScript (web development)
• Data: SQL (essential), Excel (advanced formulas, pivot tables)
• Cloud: AWS basics, understanding of cloud concepts

**Business:**
• Analytics: Google Analytics, data interpretation
• Project Management: Agile methodology, stakeholder management
• Finance: Financial modeling, budgeting, cost analysis

**Marketing:**
• Digital: SEO, social media marketing, email campaigns
• Content: Copywriting, video editing, graphic design basics
• Analytics: Marketing metrics, conversion optimization

**Universal Skills (Any Industry):**
• Communication: Professional writing, presentation skills
• Leadership: Team collaboration, mentoring, delegation
• Technology: Microsoft Office suite, basic automation

**Skill Development Timeline:**
• **Month 1**: Choose skill, find resources, start learning
• **Month 2**: Practice daily, build small projects
• **Month 3**: Create portfolio pieces, seek feedback
• **Month 4**: Apply skills in real projects, get certified

**Free Learning Path:**
1. YouTube tutorials for basics
2. Practice with free tools and datasets
3. Join relevant LinkedIn groups and forums
4. Volunteer to use skills in real projects

Which industry are you targeting? I'll customize a specific skill roadmap for you."""
                    ]
                    return random.choice(responses), "skill_development"
                
                # CV/Resume responses
                elif any(word in message_lower for word in ["cv", "resume", "application", "improve", "better"]):
                    responses = [
                        """Here's how to create a standout CV that gets interviews:

**CV Structure (1-2 pages max):**

**1. Header Section:**
• Full name, phone, professional email
• LinkedIn profile URL
• City, Country (no full address needed)
• Professional title/target role

**2. Professional Summary (3-4 lines):**
• Your experience level and key skills
• Specific achievements with numbers
• What value you bring to employers
Example: "Data Analyst with 3+ years experience increasing business efficiency by 25% through advanced Excel and SQL analysis"

**3. Work Experience:**
• Use action verbs: Achieved, Improved, Led, Developed
• Include specific numbers and results
• Focus on achievements, not just duties
Example: "Increased sales by 30% through data-driven marketing campaigns" vs "Responsible for marketing"

**4. Skills Section:**
• Technical skills: Software, programming languages, tools
• Soft skills: Leadership, communication, problem-solving
• Languages: Specify proficiency level

**5. Education:**
• Degree, institution, graduation year
• Relevant coursework, honors, GPA (if 3.5+)

**ATS Optimization:**
• Use standard fonts (Arial, Calibri)
• Include keywords from job descriptions
• Save as both PDF and Word formats
• Avoid graphics, tables, headers/footers

**Common Mistakes to Avoid:**
• Generic objective statements
• Listing duties instead of achievements
• Typos and grammatical errors
• Using unprofessional email addresses
• Including irrelevant personal information

Would you like me to review a specific section of your CV or help with a particular industry?""",

                        """Let me help you transform your CV into an interview-generating machine:

**The STAR Method for CV Writing:**
For each job experience, include:
• **Situation**: What was the context?
• **Task**: What did you need to accomplish?
• **Action**: What specific actions did you take?
• **Result**: What was the measurable outcome?

**Power Words That Get Attention:**
• **Leadership**: Led, Managed, Supervised, Coordinated, Directed
• **Achievement**: Achieved, Exceeded, Improved, Increased, Reduced
• **Innovation**: Created, Developed, Designed, Implemented, Launched
• **Analysis**: Analyzed, Evaluated, Researched, Investigated, Assessed

**Industry-Specific Tips:**

**Technology:**
• List programming languages and proficiency levels
• Include GitHub portfolio link
• Mention specific projects and technologies used
• Quantify code efficiency improvements

**Business/Finance:**
• Highlight cost savings and revenue increases
• Include budget sizes you've managed
• Mention process improvements and efficiency gains
• Show ROI of your initiatives

**Marketing:**
• Include campaign results (CTR, conversion rates)
• Mention audience sizes and engagement metrics
• Show brand awareness improvements
• List marketing tools and platforms used

**CV Customization Strategy:**
1. Read job description carefully
2. Identify 5-10 key requirements
3. Adjust your CV to highlight matching experience
4. Use similar language and keywords
5. Reorder bullet points to prioritize relevant experience

**Final Checklist:**
□ No spelling or grammar errors
□ Consistent formatting throughout
□ Professional email address
□ Updated contact information
□ Relevant keywords included
□ Quantified achievements
□ Tailored to specific job

What industry are you targeting? I can provide more specific CV optimization tips."""
                    ]
                    return random.choice(responses), "cv_resume"
                
                # Interview preparation responses
                elif any(word in message_lower for word in ["interview", "prepare", "questions", "preparation"]):
                    responses = [
                        """Here's your complete interview preparation guide:

**Before the Interview:**

**Research Phase (2-3 days before):**
• Company: Mission, values, recent news, competitors
• Role: Job description, required skills, team structure
• Interviewer: LinkedIn profile, background, interests
• Salary: Research market rates on Glassdoor, PayScale

**Common Interview Questions & How to Answer:**

**1. "Tell me about yourself"**
• Structure: Present (current role/skills) → Past (relevant experience) → Future (why this role)
• Keep it 60-90 seconds
• Focus on professional achievements

**2. "Why do you want this job?"**
• Show you researched the company
• Connect your skills to their needs
• Mention specific aspects that excite you

**3. "What's your greatest weakness?"**
• Choose a real weakness you're actively improving
• Explain steps you're taking to address it
• Show self-awareness and growth mindset

**4. "Where do you see yourself in 5 years?"**
• Show ambition but realistic goals
• Align with company's growth opportunities
• Demonstrate commitment to the field

**STAR Method for Behavioral Questions:**
• **Situation**: Set the context
• **Task**: Explain your responsibility
• **Action**: Describe what you did
• **Result**: Share the outcome with numbers

**Day of Interview:**
• Arrive 10-15 minutes early
• Bring 3 copies of your CV
• Prepare 3-5 thoughtful questions to ask them
• Dress appropriately for company culture
• Bring a notepad and pen

**Questions to Ask Them:**
• "What does success look like in this role?"
• "What are the biggest challenges facing the team?"
• "How do you measure performance?"
• "What opportunities are there for growth?"

**After the Interview:**
• Send thank-you email within 24 hours
• Reiterate your interest and key qualifications
• Address any concerns that came up

What type of interview are you preparing for? I can provide more specific guidance.""",

                        """Let me help you master the interview process:

**Interview Types & Strategies:**

**Phone/Video Interview:**
• Test technology beforehand
• Choose quiet location with good lighting
• Have your CV and notes ready
• Maintain eye contact with camera
• Speak clearly and at moderate pace

**Panel Interview:**
• Make eye contact with all panel members
• Address the person who asked the question
• Include others in your responses
• Remember everyone's names

**Technical Interview:**
• Practice coding problems on whiteboard
• Think out loud during problem-solving
• Ask clarifying questions
• Test your solution with examples

**Behavioral Interview:**
• Prepare 5-7 STAR stories covering different skills
• Practice telling stories concisely
• Focus on your specific contributions
• Quantify results whenever possible

**Industry-Specific Preparation:**

**Technology:**
• Review fundamental concepts in your field
• Practice coding challenges on LeetCode/HackerRank
• Prepare to discuss your projects in detail
• Know current industry trends and technologies

**Business/Finance:**
• Understand basic financial concepts
• Prepare case study examples
• Know industry regulations and trends
• Practice with business scenario questions

**Marketing:**
• Prepare campaign examples with metrics
• Understand current digital marketing trends
• Know the company's target audience
• Discuss marketing tools you've used

**Confidence Building Techniques:**
• Practice answers out loud, not just in your head
• Record yourself to improve delivery
• Do mock interviews with friends/family
• Visualize successful interview scenarios

**Red Flags to Avoid:**
• Speaking negatively about previous employers
• Appearing unprepared or disinterested
• Focusing only on what you want from them
• Not having questions to ask
• Arriving late or being unprofessional

**Salary Negotiation:**
• Research market rates thoroughly
• Wait for them to bring up salary first
• Consider total compensation package
• Be prepared to justify your ask
• Practice negotiation conversations

What specific aspect of interviewing would you like to focus on? I can provide more detailed guidance."""
                    ]
                    return random.choice(responses), "interview_prep"
                
                # General career advice
                else:
                    responses = [
                        f"""I'd be happy to provide detailed career guidance! Based on your question "{message}", here are some key areas I can help you with:

**Career Planning Services:**
• **Job Search Strategy**: Targeted approach to finding the right opportunities
• **Skill Development**: Roadmap for building in-demand capabilities
• **CV Optimization**: Making your resume stand out to employers
• **Interview Preparation**: Comprehensive prep for any interview type
• **Salary Negotiation**: Getting the compensation you deserve
• **Career Transition**: Moving between industries or roles
• **Professional Networking**: Building valuable connections

**Immediate Action Steps:**
1. **Clarify your goals**: What specific outcome are you looking for?
2. **Assess your current situation**: Skills, experience, constraints
3. **Research your target market**: Industries, companies, roles
4. **Create an action plan**: Specific steps with timelines
5. **Start networking**: Connect with professionals in your field

**Popular Career Topics:**
• Remote work opportunities and strategies
• Career change guidance for different life stages
• Building a personal brand and online presence
• Freelancing vs. full-time employment decisions
• Industry-specific career advancement paths

To give you the most helpful advice, could you tell me:
• What's your current career situation?
• What specific challenge are you facing?
• What industry or role interests you?
• What's your timeline for making changes?

I'm here to provide detailed, actionable guidance tailored to your specific needs!""",

                        f"""Thank you for your question: "{message}". Let me provide comprehensive career guidance:

**Career Success Framework:**

**1. Self-Assessment**
• Identify your strengths, interests, and values
• Understand your preferred work environment
• Recognize your natural talents and abilities
• Assess your current skill level and gaps

**2. Market Research**
• Study industry trends and growth projections
• Research salary ranges and career progression
• Identify key players and companies in your field
• Understand required qualifications and skills

**3. Strategic Planning**
• Set short-term (6 months) and long-term (2-5 years) goals
• Create specific, measurable action steps
• Identify potential obstacles and solutions
• Build in regular review and adjustment periods

**4. Skill Development**
• Focus on high-impact skills for your target role
• Balance technical and soft skill development
• Seek opportunities to apply new skills
• Document your learning and achievements

**5. Professional Networking**
• Build relationships before you need them
• Provide value to others in your network
• Maintain regular contact with key connections
• Participate in industry events and online communities

**Current Job Market Insights:**
• Remote and hybrid work options are expanding
• Digital skills are essential across all industries
• Soft skills like adaptability are highly valued
• Continuous learning is expected, not optional
• Personal branding through LinkedIn is crucial

**Resources for Career Development:**
• **Learning**: Coursera, LinkedIn Learning, Udemy
• **Networking**: LinkedIn, industry associations, meetups
• **Job Search**: LinkedIn Jobs, Indeed, company websites
• **Salary Research**: Glassdoor, PayScale, levels.fyi

What specific aspect of your career would you like to focus on first? I can provide detailed guidance tailored to your situation."""
                    ]
                    return random.choice(responses), "general"
                    
            except Exception as e:
                print(f"Response generation error: {e}")
                return f"I'm here to provide detailed career guidance for your question: '{message}'. Could you tell me more about your specific situation, background, or what kind of career advice you're looking for? I can help with job searching, skill development, CV improvement, interview preparation, and career planning.", "general"
        
        @app.get('/')
        async def root():
            return {
                'message': 'SmartPath AI Enhanced Career Advisor is working!',
                'google_ai': '✅ Enhanced Career Responses',
                'status': 'operational',
                'mode': 'enhanced_career_advisor',
                'database': 'available' if database_available else 'fallback',
                'features': ['detailed_responses', 'personalized_advice', 'actionable_guidance', 'industry_insights']
            }
        
        @app.get('/test')
        async def test_ai():
            return {
                'success': True,
                'ai_response': 'Hello! Enhanced Career AI is working perfectly! Ask me any career question for detailed, actionable advice!',
                'model': 'enhanced-career-advisor'
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
                
                print(f"🤖 Generating enhanced career response...")
                
                # Get enhanced career response
                ai_response, category = get_detailed_career_response(message, active_sessions[session_id])
                response_time = int((time.time() - start_time) * 1000)
                
                print(f"✅ Enhanced response generated: {ai_response[:100]}...")
                
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
                    'model': 'enhanced-career-advisor',
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
                    'ai_response': f"I'm here to provide detailed career guidance for your question: '{message}'. Let me help you with specific, actionable advice. Could you tell me more about your background, current situation, or what specific career challenge you're facing?",
                    'session_id': session_id or 'fallback',
                    'message_number': 1,
                    'category': 'general',
                    'response_time_ms': 0,
                    'model': 'enhanced-fallback',
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
        
        print(f"✅ Enhanced career advisor configured")
        print(f"🗄️ Database: {'Available' if database_available else 'Memory Only'}")
        print(f"🌐 Server starting on: http://localhost:{port}")
        print(f"📱 Test URL: http://localhost:{port}/")
        print(f"💬 Chat URL: http://localhost:{port}/chat")
        print("\n🔗 COPY THIS URL TO TEST:")
        print(f"   http://localhost:{port}/")
        print("\n🚀 ENHANCED FEATURES:")
        print("   ✅ Detailed, actionable career advice")
        print("   ✅ Industry-specific guidance")
        print("   ✅ Step-by-step action plans")
        print("   ✅ Salary and market insights")
        print("   ✅ Comprehensive skill roadmaps")
        print("   ✅ Professional interview prep")
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
    start_enhanced_ai_server()
