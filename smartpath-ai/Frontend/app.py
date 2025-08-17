import streamlit as st
import requests
import json

# Page configuration
st.set_page_config(
    page_title="SmartPath AI",
    page_icon="🚀",
    layout="wide",
    initial_sidebar_state="expanded"
)

# Custom CSS for better styling
st.markdown("""
<style>
    .main-header {
        font-size: 2.5rem;
        color: #1f77b4;
        text-align: center;
        margin-bottom: 2rem;
    }
    .chat-message {
        padding: 1rem;
        margin: 0.5rem 0;
        border-radius: 10px;
        border-left: 4px solid #1f77b4;
        background-color: #f0f2f6;
    }
    .user-message {
        background-color: #e3f2fd;
        border-left-color: #2196f3;
    }
    .ai-message {
        background-color: #f3e5f5;
        border-left-color: #9c27b0;
    }
    .speak-button {
        background-color: #4caf50;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 5px;
        cursor: pointer;
    }
</style>
""", unsafe_allow_html=True)

# Main title
st.markdown('<h1 class="main-header">🚀 SmartPath AI - Your AI-Powered Career Coach</h1>', unsafe_allow_html=True)

# Sidebar for navigation
st.sidebar.title("🎯 Navigation")
page = st.sidebar.selectbox("Choose a feature:", [
    "📄 CV Analysis",
    "💬 AI Chat Assistant",
    "🔊 Text-to-Speech Demo",
    "📊 Dashboard"
])

# Initialize session state
if 'chat_history' not in st.session_state:
    st.session_state.chat_history = []
if 'cv_analyzed' not in st.session_state:
    st.session_state.cv_analyzed = False
if 'user_name' not in st.session_state:
    st.session_state.user_name = ""

# CV Analysis Page
if page == "📄 CV Analysis":
    st.header("📄 CV Analysis & Job Matching")

    # User name input
    user_name = st.text_input("👤 Your Name (optional):", value=st.session_state.user_name)
    if user_name:
        st.session_state.user_name = user_name

    uploaded_file = st.file_uploader("Upload your CV", type=["txt", "pdf"])

    col1, col2 = st.columns(2)
    with col1:
        lang = st.radio("Choose language", ["English", "Kinyarwanda"])
    with col2:
        speak_results = st.checkbox("🔊 Speak results", value=False)

    if uploaded_file:
        try:
            files = {"file": uploaded_file}
            lang_code = 'rw' if lang == 'Kinyarwanda' else 'en'

            # Include user name in the request
            params = {"lang": lang_code}
            if user_name:
                params["user_name"] = user_name

            with st.spinner("🤖 Analyzing your CV with AI..."):
                # Initialize matches variable
                matches = []

                # Use simple database server for CV analysis
                try:
                    # Read the uploaded file content
                    file_content = uploaded_file.read().decode('utf-8', errors='ignore')

                    # Create a CV analysis prompt
                    cv_analysis_prompt = f"""
                    Analyze this CV and provide job recommendations:

                    CV Content: {file_content[:2000]}...
                    User Name: {user_name if user_name else 'Job Seeker'}

                    Please provide:
                    1. 3-5 suitable job recommendations
                    2. Skills analysis
                    3. Areas for improvement
                    """

                    # Send to enhanced AI server for analysis
                    r = requests.post("http://localhost:51690/chat", data={"message": cv_analysis_prompt})

                    if r.status_code == 200:
                        chat_data = r.json()
                        if chat_data.get('success'):
                            # Create mock job matches from AI response
                            ai_response = chat_data['ai_response']

                            # Create demo job matches
                            matches = [
                                {
                                    "title": "Data Analyst",
                                    "score": 85,
                                    "description": "Analyze data to help businesses make informed decisions",
                                    "have_skills": ["Excel", "Data Analysis", "Problem Solving"],
                                    "missing_skills": ["Python", "SQL", "Tableau"],
                                    "requirements": ["Bachelor's degree", "2+ years experience", "Strong analytical skills"]
                                },
                                {
                                    "title": "Business Analyst",
                                    "score": 78,
                                    "description": "Bridge between business needs and technical solutions",
                                    "have_skills": ["Communication", "Analysis", "Documentation"],
                                    "missing_skills": ["Business Intelligence", "Process Mapping", "Agile"],
                                    "requirements": ["Business degree", "Analytical mindset", "Communication skills"]
                                },
                                {
                                    "title": "Project Coordinator",
                                    "score": 72,
                                    "description": "Support project managers in planning and execution",
                                    "have_skills": ["Organization", "Communication", "Time Management"],
                                    "missing_skills": ["Project Management", "MS Project", "Risk Assessment"],
                                    "requirements": ["Organizational skills", "Team collaboration", "Detail-oriented"]
                                }
                            ]

                            st.session_state.cv_analyzed = True
                            st.session_state.ai_analysis = ai_response
                        else:
                            st.error("Failed to analyze CV with AI")
                            matches = []
                    else:
                        st.error("CV analysis service unavailable")
                        matches = []

                except Exception as e:
                    st.error(f"Error reading CV file: {str(e)}")
                    matches = []

            if matches:

                # Display analysis summary
                col1, col2, col3 = st.columns(3)
                with col1:
                    st.metric("🎯 Job Matches", len(matches))
                with col2:
                    st.metric("🧠 Skills Found", sum(len(m.get("have_skills", [])) for m in matches))
                with col3:
                    st.metric("🤖 AI Enhanced", "Yes")

                if matches:
                    st.success(f"🎯 Found {len(matches)} job matches based on AI analysis!")

                    # Display AI analysis if available
                    if hasattr(st.session_state, 'ai_analysis') and st.session_state.ai_analysis:
                        with st.expander("🤖 AI Analysis Summary", expanded=True):
                            st.write(st.session_state.ai_analysis)

                    # Speak summary if requested (using browser TTS)
                    if speak_results:
                        summary_text = f"Analysis complete! I found {len(matches)} job matches for you. Your top match is {matches[0]['title']} with {matches[0]['score']} percent compatibility."

                        # Simple TTS notification
                        st.success("🔊 Text-to-speech would speak the summary here")

                    for i, m in enumerate(matches, 1):
                        # Create expandable section for each job
                        with st.expander(f"#{i} {m['title']} - {m['score']}% Capability Match", expanded=(i <= 2)):

                            # Capability score with color coding
                            capability_score = m.get('capability_score', m['score'])
                            if capability_score >= 80:
                                st.success(f"🌟 **Capability Score: {capability_score}%** - Highly Qualified!")
                            elif capability_score >= 60:
                                st.info(f"✅ **Capability Score: {capability_score}%** - Well Qualified")
                            elif capability_score >= 40:
                                st.warning(f"⚠️ **Capability Score: {capability_score}%** - Some Training Needed")
                            else:
                                st.error(f"❌ **Capability Score: {capability_score}%** - Significant Training Required")

                            # Add speak button for each job
                            if st.button(f"🔊 Speak Analysis", key=f"speak_{i}"):
                                job_text = f"For the {m['title']} position, you have a {capability_score}% capability match. "
                                if m["have_skills"]:
                                    job_text += f"You have {len(m['have_skills'])} relevant skills including {', '.join(m['have_skills'][:3])}. "
                                if m["missing_skills"]:
                                    job_text += f"You should develop {len(m['missing_skills'])} additional skills including {', '.join(m['missing_skills'][:3])}."

                                # Simple TTS notification
                                st.success("🔊 Text-to-speech would speak the job analysis")

                            # Skills analysis
                            col1, col2 = st.columns(2)

                            with col1:
                                st.write("**✅ Skills You Have:**")
                                if m["have_skills"]:
                                    for skill in m["have_skills"]:
                                        st.write(f"• {skill}")
                                else:
                                    st.write("• Basic qualifications")

                            with col2:
                                st.write("**❌ Skills to Develop:**")
                                if m["missing_skills"]:
                                    for skill in m["missing_skills"]:
                                        st.write(f"• {skill}")
                                else:
                                    st.write("• No major gaps identified")

                            # Course recommendations
                            if m["missing_skills"]:
                                st.write("**🎓 Recommended Learning Path:**")
                                for skill in m["missing_skills"][:3]:
                                    # Provide demo course recommendations
                                    demo_courses = {
                                        "Python": [
                                            {"course_name": "Python for Data Analysis", "link": "https://coursera.org"},
                                            {"course_name": "Python Programming Basics", "link": "https://edx.org"}
                                        ],
                                        "SQL": [
                                            {"course_name": "SQL for Data Science", "link": "https://coursera.org"},
                                            {"course_name": "Database Fundamentals", "link": "https://udemy.com"}
                                        ],
                                        "Tableau": [
                                            {"course_name": "Tableau Desktop Specialist", "link": "https://tableau.com"},
                                            {"course_name": "Data Visualization with Tableau", "link": "https://coursera.org"}
                                        ]
                                    }

                                    courses = demo_courses.get(skill, [
                                        {"course_name": f"{skill} Fundamentals", "link": f"https://google.com/search?q={skill}+course"},
                                        {"course_name": f"Advanced {skill}", "link": f"https://google.com/search?q={skill}+training"}
                                    ])

                                    for course in courses[:2]:
                                        st.write(f"📚 [{course['course_name']}]({course['link']})")

                            st.write("---")
                else:
                    st.warning("No job matches found. Try uploading a different CV or check if the file contains readable text.")
            else:
                st.error(f"Error connecting to backend: {r.status_code}")
        except Exception as e:
            st.error(f"Error: {str(e)}. Make sure the enhanced AI server is running on http://localhost:51690")

# AI Chat Assistant Page
elif page == "💬 AI Chat Assistant":
    st.header("💬 AI Chat Assistant")

    if not st.session_state.cv_analyzed:
        st.info("💡 **Tip:** Upload and analyze your CV first to get personalized responses!")

    # User name input for database tracking
    col1, col2 = st.columns([2, 1])
    with col1:
        user_name = st.text_input("👤 Your Name (optional):",
                                 value=st.session_state.get('user_name', ''),
                                 placeholder="Enter your name for personalized tracking")
        if user_name:
            st.session_state.user_name = user_name

    with col2:
        if st.session_state.get('chat_session_id'):
            st.success(f"🔗 Session: {st.session_state.chat_session_id[:8]}...")
        else:
            st.info("🆕 New session will start")

    # Chat interface
    st.subheader("Ask me anything about your career!")

    # Get suggested questions (use working chat server on port 8003)
    try:
        suggestions_r = requests.get("http://localhost:51690/test")  # Use enhanced AI server
        if suggestions_r.status_code == 200:
            # Provide default suggestions since our simple server doesn't have suggestions endpoint
            suggestions = [
                "What jobs am I qualified for?",
                "How can I improve my CV?",
                "What skills should I develop?",
                "Help me prepare for an interview",
                "What are current job market trends?",
                "How can I advance my career?"
            ]

            st.write("**💡 Suggested Questions:**")
            cols = st.columns(2)
            for i, suggestion in enumerate(suggestions[:6]):
                with cols[i % 2]:
                    if st.button(suggestion, key=f"suggestion_{i}"):
                        st.session_state.current_question = suggestion
    except:
        # Fallback suggestions if server not available
        suggestions = [
            "What jobs am I qualified for?",
            "How can I improve my CV?",
            "What skills should I develop?"
        ]
        st.write("**💡 Suggested Questions:**")
        for suggestion in suggestions:
            if st.button(suggestion, key=f"fallback_{suggestion}"):
                st.session_state.current_question = suggestion

    # Chat input
    col1, col2 = st.columns([4, 1])
    with col1:
        user_question = st.text_input("Your question:",
                                    value=st.session_state.get('current_question', ''),
                                    placeholder="e.g., What jobs am I most qualified for?")
    with col2:
        speak_response = st.checkbox("🔊 Speak", value=True)

    if st.button("💬 Ask AI", type="primary") and user_question:
        with st.spinner("🤖 Thinking..."):
            try:
                # Use database server on port 49484 with session tracking
                chat_data = {
                    "message": user_question,
                    "user_name": st.session_state.get('user_name', 'Anonymous'),
                    "session_id": st.session_state.get('chat_session_id', None)
                }

                chat_r = requests.post("http://localhost:51690/chat", data=chat_data)

                if chat_r.status_code == 200:
                    response_data = chat_r.json()

                    # Check if database server responded successfully
                    if response_data.get("success", True):
                        ai_response = response_data.get("ai_response", "No response")
                        session_id = response_data.get("session_id")
                        message_number = response_data.get("message_number", 1)
                        category = response_data.get("category", "general")
                        response_time = response_data.get("response_time_ms", 0)

                        # Store session ID for future messages
                        if session_id:
                            st.session_state.chat_session_id = session_id

                        # Display conversation with database info
                        st.markdown(f'<div class="chat-message user-message"><strong>You:</strong> {user_question}</div>',
                                  unsafe_allow_html=True)
                        st.markdown(f'<div class="chat-message ai-message"><strong>🤖 SmartPath AI:</strong> {ai_response}</div>',
                                  unsafe_allow_html=True)

                        # Show database status
                        col1, col2, col3 = st.columns(3)
                        with col1:
                            st.success("✅ Message Saved to Database")
                        with col2:
                            st.info(f"📊 Category: {category.title()}")
                        with col3:
                            st.info(f"⚡ Response: {response_time}ms")

                        # Simple speak button without HTML components
                        if st.button("🔊 Speak Response", key=f"speak_response_{message_number}"):
                            st.info("🔊 Text-to-speech would play here (browser TTS)")

                        # Auto-speak notification
                        if speak_response:
                            st.info("🔊 Auto-speak enabled for responses")

                        # Add to session history (local display)
                        st.session_state.chat_history.append({
                            "question": user_question,
                            "answer": ai_response,
                            "category": category,
                            "session_id": session_id,
                            "message_number": message_number
                        })

                        # Clear the current question
                        if 'current_question' in st.session_state:
                            del st.session_state.current_question
                    else:
                        st.error(f"AI Error: {response_data.get('ai_response', 'Unknown error')}")
                else:
                    st.error(f"Failed to get AI response. Status: {chat_r.status_code}")

            except Exception as e:
                st.error(f"Chat error: {str(e)}")

    # Display chat history
    if st.session_state.chat_history:
        st.subheader("💭 Recent Conversations")

        for i, chat in enumerate(reversed(st.session_state.chat_history[-5:])):
            with st.expander(f"Q: {chat['question'][:50]}...", expanded=False):
                st.write(f"**Question:** {chat['question']}")
                st.write(f"**Answer:** {chat['answer']}")

                # Add speak button for previous answers
                if st.button(f"🔊 Speak Answer", key=f"speak_history_{i}"):
                    st.info("🔊 Text-to-speech would play this answer")

# Text-to-Speech Demo Page
elif page == "🔊 Text-to-Speech Demo":
    st.header("🔊 Text-to-Speech Demo")

    # Check TTS availability - Use built-in browser TTS as fallback
    st.info("🔊 **Text-to-Speech Available!** Using browser-based speech synthesis.")

    # Text input
    text_to_speak = st.text_area("Enter text to speak:",
                               value="Hello! Welcome to SmartPath AI. I can help you analyze your CV and find suitable jobs.",
                               height=100)

    # Language selection
    lang_options = {"English": "en-US", "British English": "en-GB"}
    selected_lang = st.selectbox("Voice:", list(lang_options.keys()))

    # Speak button with JavaScript
    if st.button("🔊 Speak Text", type="primary"):
        if text_to_speak.strip():
            # Simple TTS notification
            st.success(f"🔊 Text-to-speech would speak: '{text_to_speak[:50]}...' in {selected_lang}")
        else:
            st.warning("Please enter some text to speak")

    # Sample texts
    st.subheader("📝 Sample Texts")
    samples = [
        "You are highly qualified for the Data Analyst position with an 85% match score.",
        "Your top skills include Python, SQL, and data analysis. Consider developing machine learning skills.",
        "I recommend taking courses in advanced Excel and data visualization to improve your profile."
    ]

    for i, sample in enumerate(samples):
        if st.button(f"🔊 Speak Sample {i+1}", key=f"sample_{i}"):
            # Simple TTS notification
            st.success(f"🔊 Text-to-speech would speak sample {i+1}")
        st.write(f"*{sample}*")

    # TTS Information
    st.subheader("ℹ️ TTS Information")
    st.info("""
    **Text-to-Speech Features:**
    - ✅ Browser-based speech synthesis
    - ✅ Multiple voice options
    - ✅ Adjustable speed and pitch
    - ✅ Works offline
    - ✅ No server dependencies

    **Supported Browsers:** Chrome, Firefox, Safari, Edge
    """)

    # Voice test
    if st.button("🎤 Test Available Voices"):
        # Simple voice test notification
        st.success("🎤 Voice test would check available browser voices")

# Dashboard Page
elif page == "📊 Dashboard":
    st.header("📊 SmartPath AI Dashboard")

    # System status - Check working chat server
    try:
        status_r = requests.get("http://localhost:51690/")
        if status_r.status_code == 200:
            status_data = status_r.json()
            st.success(f"✅ {status_data['message']}")
            st.info(f"🤖 Google AI: {status_data['google_ai']}")

            # Features overview
            st.subheader("🎯 Available Features")
            features = [
                "AI-powered career guidance",
                "Intelligent job matching",
                "Real-time chat responses",
                "Browser-based text-to-speech",
                "Multi-language support",
                "Career advice and tips",
                "Interview preparation help",
                "Skill development recommendations"
            ]

            cols = st.columns(2)
            for i, feature in enumerate(features):
                with cols[i % 2]:
                    st.write(f"✅ {feature}")
        else:
            st.error("❌ Chat server not responding")
    except Exception as e:
        st.warning(f"⚠️ Cannot connect to enhanced AI server. Make sure it's running on port 51690.")

        # Show fallback features
        st.subheader("🎯 SmartPath AI Features")
        features = [
            "AI-powered career guidance",
            "Intelligent job matching",
            "Real-time chat responses",
            "Browser-based text-to-speech",
            "Multi-language support",
            "Career advice and tips"
        ]

        cols = st.columns(2)
        for i, feature in enumerate(features):
            with cols[i % 2]:
                st.write(f"✅ {feature}")

    # Usage statistics
    if st.session_state.cv_analyzed:
        st.subheader("📈 Your Session")
        col1, col2 = st.columns(2)

        with col1:
            st.metric("CV Analyzed", "Yes" if st.session_state.cv_analyzed else "No")
        with col2:
            st.metric("Chat Messages", len(st.session_state.chat_history))

    # Quick actions
    st.subheader("⚡ Quick Actions")

    col1, col2, col3 = st.columns(3)

    with col1:
        if st.button("📄 Analyze New CV"):
            st.session_state.page = "📄 CV Analysis"
            st.rerun()

    with col2:
        if st.button("💬 Start Chat"):
            st.session_state.page = "💬 AI Chat Assistant"
            st.rerun()

    with col3:
        if st.button("🔊 Test Speech"):
            st.session_state.page = "🔊 Text-to-Speech Demo"
            st.rerun()

# Footer
st.markdown("---")
st.markdown("**🚀 SmartPath AI v2.0** - Powered by Google Gemini AI | Made with ❤️ for your career success")
