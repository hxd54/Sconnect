from fastapi import FastAPI, UploadFile, HTTPException, Form
from fastapi.middleware.cors import CORSMiddleware
from match_engine import match_jobs
from course_recommender import recommend_courses
from translator import translate_text, detect_language
from skill_detector import detect_skill_gaps, extract_skills
from text_to_speech import speak_text, is_tts_available, get_tts_info
from chat_interface import chat_with_ai, set_chat_context, get_chat_history, clear_chat_history, get_suggested_questions
import fitz  # PyMuPDF
import logging
import os
import sys
from config import GOOGLE_API_KEY, GEMINI_MODEL, MAX_FILE_SIZE, SUPPORTED_FILE_TYPES

# Add the backend directory to Python path to ensure all modules are found
backend_dir = os.path.dirname(os.path.abspath(__file__))
if backend_dir not in sys.path:
    sys.path.insert(0, backend_dir)

# Test Google AI import at startup with detailed debugging
GOOGLE_AI_AVAILABLE = False
print("🔍 Testing Google AI import during startup...")

try:
    print("  📦 Attempting to import google.generativeai...")
    import google.generativeai as genai
    print("  ✅ google.generativeai imported successfully")

    print(f"  🔑 Configuring with API key: {GOOGLE_API_KEY[:10]}...")
    genai.configure(api_key=GOOGLE_API_KEY)
    print("  ✅ Google AI configured")

    print(f"  🤖 Creating model: {GEMINI_MODEL}")
    test_model = genai.GenerativeModel(GEMINI_MODEL)
    print("  ✅ Model created")

    print("  🧪 Testing generation...")
    test_response = test_model.generate_content("Hello")
    print(f"  ✅ Test response: {test_response.text[:30]}...")

    print("✅ Google AI successfully imported, configured, and tested")
    GOOGLE_AI_AVAILABLE = True

except ImportError as e:
    print(f"❌ Google AI import error: {e}")
    print("💡 Please install: pip install google-generativeai")
    GOOGLE_AI_AVAILABLE = False

except Exception as e:
    print(f"⚠️ Google AI configuration error: {e}")
    print(f"   Error type: {type(e).__name__}")
    import traceback
    traceback.print_exc()
    GOOGLE_AI_AVAILABLE = False

print(f"🏁 Final GOOGLE_AI_AVAILABLE status: {GOOGLE_AI_AVAILABLE}")

# Setup logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

app = FastAPI(
    title="SmartPath AI",
    description="AI-powered CV analysis and job matching system",
    version="2.0.0"
)

async def extract_text_enhanced(file: UploadFile):
    """
    Enhanced text extraction with better error handling and AI-powered cleanup
    """
    # Check file size
    if file.size and file.size > MAX_FILE_SIZE:
        raise HTTPException(status_code=413, detail="File too large")

    # Check file type
    file_ext = f".{file.filename.split('.')[-1].lower()}" if '.' in file.filename else ""
    if file_ext not in SUPPORTED_FILE_TYPES:
        raise HTTPException(status_code=400, detail="Unsupported file type")

    try:
        if file.filename.lower().endswith(".txt"):
            content = await file.read()
            text = content.decode("utf-8")
        elif file.filename.lower().endswith(".pdf"):
            content = await file.read()
            pdf = fitz.open(stream=content, filetype="pdf")
            text = ""
            for page in pdf:
                text += page.get_text()
            pdf.close()
        else:
            return ""

        # Clean and enhance the extracted text using AI
        cleaned_text = clean_text_with_ai(text)

        logger.info(f"Successfully extracted {len(cleaned_text)} characters from {file.filename}")
        return cleaned_text

    except Exception as e:
        logger.error(f"Text extraction error: {e}")
        raise HTTPException(status_code=500, detail="Failed to extract text from file")

def clean_text_with_ai(text):
    """
    Use Google AI to clean and structure extracted text
    """
    if not text.strip():
        return text

    try:
        import google.generativeai as genai

        genai.configure(api_key=GOOGLE_API_KEY)
        model = genai.GenerativeModel(GEMINI_MODEL)

        prompt = f"""
        Clean and structure the following CV/resume text. Remove any OCR errors,
        fix formatting issues, and organize the content properly while preserving all information.
        Return only the cleaned text without any additional comments.

        Original text:
        {text[:2000]}

        Cleaned text:
        """

        response = model.generate_content(prompt)
        cleaned = response.text.strip()

        # If AI cleaning is too short or failed, return original
        if len(cleaned) < len(text) * 0.5:
            return text

        return cleaned

    except Exception as e:
        logger.warning(f"AI text cleaning failed: {e}, using original text")
        return text

# Legacy function for backward compatibility
async def extract_text(file: UploadFile):
    return await extract_text_enhanced(file)

@app.get("/")
def home():
    return {
        "message": "SmartPath AI v2.0 is running!",
        "features": [
            "AI-powered CV analysis",
            "Enhanced job matching",
            "Smart skill extraction",
            "Multi-language support",
            "Course recommendations",
            "Text-to-speech integration",
            "Conversational AI assistant"
        ],
        "powered_by": "Google Gemini AI",
        "google_ai_available": GOOGLE_AI_AVAILABLE
    }

@app.get("/test-ai")
async def test_ai():
    """Test Google AI connection at runtime"""
    try:
        import google.generativeai as genai

        # Configure and test
        genai.configure(api_key=GOOGLE_API_KEY)
        model = genai.GenerativeModel(GEMINI_MODEL)

        response = model.generate_content("Hello! Please respond with 'AI is working correctly!'")

        return {
            "success": True,
            "message": "Google AI is working!",
            "ai_response": response.text.strip(),
            "model": GEMINI_MODEL,
            "startup_flag": GOOGLE_AI_AVAILABLE
        }

    except ImportError as e:
        return {
            "success": False,
            "message": "Google AI import failed",
            "error": f"ImportError: {str(e)}",
            "startup_flag": GOOGLE_AI_AVAILABLE
        }

    except Exception as e:
        return {
            "success": False,
            "message": "Google AI test failed",
            "error": str(e),
            "startup_flag": GOOGLE_AI_AVAILABLE
        }

@app.post("/match")
async def match(file: UploadFile, lang: str = "en", user_name: str = ""):
    """
    Enhanced CV matching with AI-powered analysis
    """
    text = await extract_text_enhanced(file)
    if not text.strip():
        return {"matches": [], "error": "No text could be extracted from the file"}

    # Detect language if not specified
    if lang == "auto":
        lang = detect_language(text)

    jobs = match_jobs(text)
    jobs_with_gaps = detect_skill_gaps(text, jobs)

    # Extract skills for context
    skills = list(extract_skills(text))

    # If Kinyarwanda selected, translate job titles
    if lang == "rw":
        for job in jobs_with_gaps:
            job["title"] = translate_text(job["title"], "rw")

    # Set context for chat interface
    set_chat_context(
        cv_text=text,
        job_matches=jobs_with_gaps,
        skills=skills,
        user_name=user_name if user_name else None
    )

    return {
        "matches": jobs_with_gaps,
        "detected_language": lang,
        "total_matches": len(jobs_with_gaps),
        "skills_extracted": len(skills),
        "ai_enhanced": True,
        "chat_context_set": True
    }

@app.post("/analyze")
async def analyze_cv(file: UploadFile):
    """
    Comprehensive CV analysis using AI
    """
    text = await extract_text_enhanced(file)
    if not text.strip():
        return {"error": "No text could be extracted from the file"}

    # Extract skills using AI
    skills = extract_skills(text)

    # Detect language
    language = detect_language(text)

    # Get job matches
    jobs = match_jobs(text)

    return {
        "extracted_skills": list(skills),
        "skill_count": len(skills),
        "detected_language": language,
        "top_job_matches": jobs[:5],
        "text_length": len(text),
        "ai_powered": True
    }

@app.get("/recommend")
def recommend(skill: str):
    """
    Get course recommendations for a specific skill
    """
    courses = recommend_courses(skill)
    return {
        "skill": skill,
        "courses": courses,
        "count": len(courses)
    }

@app.post("/translate")
async def translate_endpoint(text: str, target_lang: str = "rw"):
    """
    Translate text using AI
    """
    if not text.strip():
        return {"error": "No text provided"}

    translated = translate_text(text, target_lang)
    detected_lang = detect_language(text)

    return {
        "original_text": text,
        "translated_text": translated,
        "source_language": detected_lang,
        "target_language": target_lang,
        "ai_powered": True
    }

@app.get("/skills/extract")
async def extract_skills_endpoint(text: str):
    """
    Extract skills from text using AI
    """
    if not text.strip():
        return {"error": "No text provided"}

    skills = extract_skills(text)

    return {
        "text_preview": text[:200] + "..." if len(text) > 200 else text,
        "extracted_skills": list(skills),
        "skill_count": len(skills),
        "ai_powered": True
    }

@app.post("/chat")
async def chat_endpoint(message: str = Form(...), speak_response: bool = Form(False), language: str = Form("en")):
    """
    Chat with SmartPath AI Assistant
    """
    if not message.strip():
        return {"error": "No message provided"}

    # Always try Google AI directly (ignore startup flag)
    try:
        import google.generativeai as genai

        # Configure Google AI
        genai.configure(api_key=GOOGLE_API_KEY)
        model = genai.GenerativeModel(GEMINI_MODEL)

        # Build a simple prompt
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
        ai_response = response.text.strip()

        # Speak response if requested
        if speak_response and is_tts_available():
            speak_text(ai_response, language, async_mode=True)

        return {
            "user_message": message,
            "ai_response": ai_response,
            "success": True,
            "spoken": speak_response and is_tts_available(),
            "language": language,
            "context_used": False,
            "tts_available": is_tts_available(),
            "method": "runtime_google_ai"
        }

    except ImportError as e:
        return {
            "user_message": message,
            "ai_response": f"Google AI module not found: {str(e)}. Please install google-generativeai.",
            "success": False,
            "spoken": False,
            "language": language,
            "context_used": False,
            "tts_available": is_tts_available(),
            "method": "import_error"
        }

    except Exception as e:
        return {
            "user_message": message,
            "ai_response": f"Google AI error: {str(e)}",
            "success": False,
            "spoken": False,
            "language": language,
            "context_used": False,
            "tts_available": is_tts_available(),
            "method": "runtime_error"
        }



@app.post("/speak")
async def speak_endpoint(text: str = Form(...), language: str = Form("en")):
    """
    Convert text to speech
    """
    if not text.strip():
        return {"error": "No text provided"}

    if not is_tts_available():
        return {"error": "Text-to-speech not available", "spoken": False}

    success = speak_text(text, language, async_mode=True)

    return {
        "text": text,
        "language": language,
        "spoken": success,
        "tts_info": get_tts_info()
    }

@app.get("/chat/history")
async def get_chat_history_endpoint():
    """
    Get chat conversation history
    """
    history = get_chat_history()

    return {
        "conversation_history": history,
        "total_exchanges": len(history)
    }

@app.delete("/chat/history")
async def clear_chat_history_endpoint():
    """
    Clear chat conversation history
    """
    clear_chat_history()

    return {
        "message": "Chat history cleared successfully"
    }

@app.get("/chat/suggestions")
async def get_chat_suggestions_endpoint():
    """
    Get suggested questions for the chat
    """
    suggestions = get_suggested_questions()

    return {
        "suggested_questions": suggestions,
        "count": len(suggestions)
    }

@app.get("/tts/info")
async def get_tts_info_endpoint():
    """
    Get text-to-speech engine information
    """
    return get_tts_info()

@app.post("/context/set")
async def set_context_endpoint(
    cv_text: str = Form(None),
    user_name: str = Form(None)
):
    """
    Set context for chat from CV analysis
    """
    try:
        # Extract skills and get job matches if CV text provided
        skills = None
        job_matches = None

        if cv_text:
            skills = list(extract_skills(cv_text))
            job_matches = match_jobs(cv_text)

        # Set context for chat
        set_chat_context(
            cv_text=cv_text,
            job_matches=job_matches,
            skills=skills,
            user_name=user_name
        )

        return {
            "message": "Context set successfully",
            "skills_count": len(skills) if skills else 0,
            "job_matches_count": len(job_matches) if job_matches else 0,
            "user_name": user_name
        }

    except Exception as e:
        return {"error": f"Failed to set context: {str(e)}"}
