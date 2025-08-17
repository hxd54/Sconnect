# Configuration file for SmartPath AI
import os

# Google API Configuration
GOOGLE_API_KEY = "AIzaSyD7gjE9qQFCOMth91BfI2zOXwUHu7DxP3A"

# Set environment variable for Google API
os.environ["GOOGLE_API_KEY"] = GOOGLE_API_KEY

# AI Model Configuration
GEMINI_MODEL = "gemini-1.5-flash"
TEMPERATURE = 0.7
MAX_TOKENS = 1000

# Application Settings
DEFAULT_LANGUAGE = "en"
SUPPORTED_LANGUAGES = ["en", "rw"]
SIMILARITY_THRESHOLD = 0.3

# File Processing Settings
MAX_FILE_SIZE = 10 * 1024 * 1024  # 10MB
SUPPORTED_FILE_TYPES = [".txt", ".pdf"]

# Logging Configuration
LOG_LEVEL = "INFO"
LOG_FORMAT = "%(asctime)s - %(name)s - %(levelname)s - %(message)s"
