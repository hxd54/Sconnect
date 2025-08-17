#!/usr/bin/env python3
"""
Chat Interface module for SmartPath AI
Provides conversational AI capabilities using Google Gemini
"""
import logging
from typing import List, Dict, Optional
from config import GOOGLE_API_KEY, GEMINI_MODEL
from text_to_speech import speak_text, is_tts_available

# Setup logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class SmartPathChatBot:
    """AI-powered chatbot for SmartPath AI"""
    
    def __init__(self):
        self.conversation_history = []
        self.context = {
            "user_cv": None,
            "job_matches": None,
            "skills": None,
            "user_name": None
        }
        self.system_prompt = """
        You are SmartPath AI Assistant, an expert career counselor and job matching specialist.
        
        Your role:
        - Help users with career guidance and job search
        - Analyze CVs and provide insights
        - Recommend suitable jobs and career paths
        - Suggest skills to develop
        - Provide course recommendations
        - Answer questions about career development
        
        Personality:
        - Friendly, professional, and encouraging
        - Provide actionable advice
        - Be specific and helpful
        - Keep responses concise but informative
        - Always be supportive and positive
        
        When users ask questions, provide helpful, accurate responses based on their CV analysis and job market knowledge.
        """
    
    def set_context(self, cv_text: str = None, job_matches: List = None, skills: List = None, user_name: str = None):
        """Set conversation context from CV analysis"""
        if cv_text:
            self.context["user_cv"] = cv_text
        if job_matches:
            self.context["job_matches"] = job_matches
        if skills:
            self.context["skills"] = skills
        if user_name:
            self.context["user_name"] = user_name
    
    def chat(self, user_message: str, speak_response: bool = False, language: str = "en") -> Dict:
        """
        Process user message and generate AI response
        
        Args:
            user_message: User's question or message
            speak_response: Whether to speak the response
            language: Response language
            
        Returns:
            Dict with response, success status, and metadata
        """
        try:
            # Try to import Google AI with better error handling
            try:
                import google.generativeai as genai
                logger.info("✅ google.generativeai imported successfully")
            except ImportError as import_err:
                logger.error(f"❌ Failed to import google.generativeai: {import_err}")
                # Try alternative import paths
                import sys
                sys.path.append('.')
                sys.path.append('..')
                try:
                    import google.generativeai as genai
                    logger.info("✅ google.generativeai imported on retry")
                except ImportError:
                    raise ImportError("google.generativeai module not found. Please install with: pip install google-generativeai")

            # Check if API key is available
            if not GOOGLE_API_KEY or GOOGLE_API_KEY == "your_api_key_here":
                raise ValueError("Google API key not configured")

            genai.configure(api_key=GOOGLE_API_KEY)
            model = genai.GenerativeModel(GEMINI_MODEL)
            logger.info("✅ Google AI model configured successfully")
            
            # Build context-aware prompt
            context_info = self._build_context_prompt()
            
            full_prompt = f"""
            {self.system_prompt}
            
            Context Information:
            {context_info}
            
            Conversation History:
            {self._format_conversation_history()}
            
            User Question: {user_message}
            
            Please provide a helpful, specific response. Keep it conversational and under 200 words.
            If the user asks about their CV analysis, job matches, or skills, use the context information provided.
            """
            
            # Generate response
            response = model.generate_content(full_prompt)
            ai_response = response.text.strip()
            
            # Add to conversation history
            self.conversation_history.append({
                "user": user_message,
                "assistant": ai_response,
                "timestamp": self._get_timestamp()
            })
            
            # Limit conversation history to last 10 exchanges
            if len(self.conversation_history) > 10:
                self.conversation_history = self.conversation_history[-10:]
            
            # Speak response if requested
            if speak_response and is_tts_available():
                speak_text(ai_response, language, async_mode=True)
            
            logger.info(f"Chat response generated for: {user_message[:50]}...")
            
            return {
                "success": True,
                "response": ai_response,
                "spoken": speak_response and is_tts_available(),
                "language": language,
                "context_used": bool(context_info.strip())
            }
            
        except ImportError as e:
            error_msg = f"Google AI module not found: {str(e)}. Please install google-generativeai."
            logger.error(f"Google Generative AI import error: {e}")
            return {
                "success": False,
                "response": error_msg,
                "spoken": False,
                "error": "AI_MODULE_NOT_FOUND"
            }
        except ValueError as e:
            error_msg = "Google AI API key not configured properly."
            logger.error(f"API key error: {e}")
            return {
                "success": False,
                "response": error_msg,
                "spoken": False,
                "error": "API_KEY_ERROR"
            }
            
        except Exception as e:
            error_msg = f"I'm sorry, I encountered an error processing your question. Please try again."
            logger.error(f"Chat error: {e}")
            return {
                "success": False,
                "response": error_msg,
                "spoken": False,
                "error": str(e)
            }
    
    def _build_context_prompt(self) -> str:
        """Build context information for the AI"""
        context_parts = []
        
        if self.context["user_name"]:
            context_parts.append(f"User Name: {self.context['user_name']}")
        
        if self.context["skills"]:
            skills_text = ", ".join(self.context["skills"][:10])  # Limit to top 10 skills
            context_parts.append(f"User Skills: {skills_text}")
        
        if self.context["job_matches"]:
            matches_text = []
            for match in self.context["job_matches"][:5]:  # Top 5 matches
                matches_text.append(f"{match['title']} ({match['score']}% match)")
            context_parts.append(f"Top Job Matches: {', '.join(matches_text)}")
        
        if self.context["user_cv"]:
            cv_summary = self.context["user_cv"][:500] + "..." if len(self.context["user_cv"]) > 500 else self.context["user_cv"]
            context_parts.append(f"CV Summary: {cv_summary}")
        
        return "\n".join(context_parts)
    
    def _format_conversation_history(self) -> str:
        """Format conversation history for context"""
        if not self.conversation_history:
            return "No previous conversation."
        
        history_text = []
        for exchange in self.conversation_history[-5:]:  # Last 5 exchanges
            history_text.append(f"User: {exchange['user']}")
            history_text.append(f"Assistant: {exchange['assistant']}")
        
        return "\n".join(history_text)
    
    def _get_timestamp(self) -> str:
        """Get current timestamp"""
        from datetime import datetime
        return datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    
    def get_conversation_history(self) -> List[Dict]:
        """Get conversation history"""
        return self.conversation_history
    
    def clear_history(self):
        """Clear conversation history"""
        self.conversation_history = []
        logger.info("Conversation history cleared")
    
    def get_suggested_questions(self) -> List[str]:
        """Get suggested questions based on context"""
        suggestions = [
            "What jobs am I most qualified for?",
            "What skills should I develop to improve my career prospects?",
            "How can I improve my CV?",
            "What courses do you recommend for me?",
            "What are the current job market trends in my field?"
        ]
        
        # Add context-specific suggestions
        if self.context["job_matches"]:
            top_job = self.context["job_matches"][0]["title"]
            suggestions.insert(0, f"Tell me more about the {top_job} role")
            suggestions.insert(1, f"What skills do I need for {top_job}?")
        
        return suggestions[:6]  # Return top 6 suggestions

# Global chatbot instance
chatbot = SmartPathChatBot()

def chat_with_ai(message: str, speak_response: bool = False, language: str = "en") -> Dict:
    """
    Convenience function to chat with AI
    
    Args:
        message: User message
        speak_response: Whether to speak the response
        language: Response language
        
    Returns:
        Dict with response and metadata
    """
    return chatbot.chat(message, speak_response, language)

def set_chat_context(cv_text: str = None, job_matches: List = None, skills: List = None, user_name: str = None):
    """Set context for the chatbot"""
    chatbot.set_context(cv_text, job_matches, skills, user_name)

def get_chat_history() -> List[Dict]:
    """Get conversation history"""
    return chatbot.get_conversation_history()

def clear_chat_history():
    """Clear conversation history"""
    chatbot.clear_history()

def get_suggested_questions() -> List[str]:
    """Get suggested questions"""
    return chatbot.get_suggested_questions()

if __name__ == "__main__":
    # Test the chat functionality
    print("🤖 Testing SmartPath AI Chat...")
    
    # Set some context
    set_chat_context(
        skills=["Python", "SQL", "Data Analysis"],
        job_matches=[{"title": "Data Analyst", "score": 85}],
        user_name="John"
    )
    
    # Test questions
    test_questions = [
        "What jobs am I qualified for?",
        "What skills should I develop?",
        "How can I improve my career prospects?"
    ]
    
    for question in test_questions:
        print(f"\n❓ Question: {question}")
        response = chat_with_ai(question, speak_response=False)
        
        if response["success"]:
            print(f"🤖 Response: {response['response'][:100]}...")
        else:
            print(f"❌ Error: {response['response']}")
    
    print("\n🎉 Chat test completed!")
