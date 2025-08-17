"""
SmartPath AI Backend Package
============================

This package contains all the backend modules for SmartPath AI:
- main.py: FastAPI application and API endpoints
- config.py: Configuration and API keys
- match_engine.py: AI-powered job matching
- skill_detector.py: AI skill extraction and gap analysis
- translator.py: AI translation services
- course_recommender.py: Course recommendation engine

All modules are designed to work together to provide intelligent CV analysis
and job matching capabilities using Google's Gemini AI.
"""

__version__ = "2.0.0"
__author__ = "SmartPath AI Team"

# Import key functions for easy access
try:
    from .match_engine import match_jobs
    from .skill_detector import extract_skills, detect_skill_gaps
    from .translator import translate_text, detect_language
    from .course_recommender import recommend_courses
    from .config import GOOGLE_API_KEY, GEMINI_MODEL
    
    __all__ = [
        'match_jobs',
        'extract_skills', 
        'detect_skill_gaps',
        'translate_text',
        'detect_language',
        'recommend_courses',
        'GOOGLE_API_KEY',
        'GEMINI_MODEL'
    ]
    
except ImportError as e:
    # If imports fail, modules might not be properly installed
    print(f"Warning: Some backend modules could not be imported: {e}")
    print("Please run: python backend/setup_backend.py")
    __all__ = []
