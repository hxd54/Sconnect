#!/usr/bin/env python3
"""
Simple working chat server with Google AI
"""
from fastapi import FastAPI, Form
import uvicorn
import google.generativeai as genai

# Configuration
GOOGLE_API_KEY = "AIzaSyD7gjE9qQFCOMth91BfI2zOXwUHu7DxP3A"
GEMINI_MODEL = "gemini-1.5-flash"

# Configure Google AI
genai.configure(api_key=GOOGLE_API_KEY)

app = FastAPI(title="SmartPath AI Chat - Working Version")

@app.get("/")
async def root():
    return {
        "message": "SmartPath AI Chat Server is working!",
        "google_ai": "✅ Connected",
        "status": "operational"
    }

@app.get("/test")
async def test_ai():
    """Test Google AI"""
    try:
        model = genai.GenerativeModel(GEMINI_MODEL)
        response = model.generate_content("Say 'Hello! AI is working perfectly!'")
        
        return {
            "success": True,
            "ai_response": response.text.strip(),
            "model": GEMINI_MODEL
        }
    except Exception as e:
        return {
            "success": False,
            "error": str(e)
        }

@app.post("/chat")
async def chat(message: str = Form(...)):
    """Chat with AI"""
    try:
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
        
        return {
            "success": True,
            "user_message": message,
            "ai_response": response.text.strip(),
            "model": GEMINI_MODEL
        }
        
    except Exception as e:
        return {
            "success": False,
            "user_message": message,
            "ai_response": f"Error: {str(e)}",
            "error": str(e)
        }

if __name__ == "__main__":
    print("🚀 Starting SmartPath AI Chat Server...")
    print("✅ Google AI configured")
    print("🌐 Server will be available at: http://localhost:8004")

    uvicorn.run(app, host="0.0.0.0", port=8004)
