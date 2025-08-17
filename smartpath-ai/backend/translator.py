# Enhanced translator.py with Google AI
import logging
from config import GOOGLE_API_KEY, GEMINI_MODEL

# Setup logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

def translate_text(text, target_lang):
    """
    Translate text using Google's Gemini AI
    """
    if target_lang == "en" or not text.strip():
        return text

    try:
        # Try to import and use Google Generative AI
        import google.generativeai as genai

        # Configure the API
        genai.configure(api_key=GOOGLE_API_KEY)
        model = genai.GenerativeModel(GEMINI_MODEL)

        # Create translation prompt
        if target_lang == "rw":
            prompt = f"""
            Translate the following English text to Kinyarwanda (Rwandan language).
            Provide only the translation, no explanations.

            Text to translate: "{text}"

            Translation:
            """
        else:
            prompt = f"""
            Translate the following text to {target_lang}.
            Provide only the translation, no explanations.

            Text to translate: "{text}"

            Translation:
            """

        # Generate translation
        response = model.generate_content(prompt)
        translation = response.text.strip()

        logger.info(f"Successfully translated '{text}' to {target_lang}")
        return translation

    except ImportError:
        logger.warning("Google Generative AI not available, using fallback translation")
        return fallback_translate(text, target_lang)
    except Exception as e:
        logger.error(f"Translation error: {e}")
        return fallback_translate(text, target_lang)

def fallback_translate(text, target_lang):
    """
    Fallback translation method when Google AI is not available
    """
    if target_lang == "rw":
        # Basic Kinyarwanda translations for common job titles
        translations = {
            "Data Analyst": "Usesengura Amakuru",
            "Web Developer": "Uwubaka Urubuga",
            "Software Engineer": "Injeniyeri ya Software",
            "Project Manager": "Umuyobozi w'Umushinga",
            "Marketing Specialist": "Impuguke mu Kwamamaza",
            "Graphic Designer": "Uwushushanya Amashusho",
            "Customer Support": "Ubufasha bw'Abakiriya",
            "Data Scientist": "Umuhanga mu Makuru",
            "Mobile App Developer": "Uwubaka Porogaramu z'Igikoresho",
            "Content Writer": "Uwandika Ibintu"
        }

        return translations.get(text, f"[Kinyarwanda] {text}")

    return text

def detect_language(text):
    """
    Detect the language of the input text using Google AI
    """
    try:
        import google.generativeai as genai

        genai.configure(api_key=GOOGLE_API_KEY)
        model = genai.GenerativeModel(GEMINI_MODEL)

        prompt = f"""
        Detect the language of the following text.
        Respond with only the language code (e.g., 'en' for English, 'rw' for Kinyarwanda, 'fr' for French).

        Text: "{text[:200]}"

        Language code:
        """

        response = model.generate_content(prompt)
        language = response.text.strip().lower()

        return language if language in ['en', 'rw', 'fr'] else 'en'

    except Exception as e:
        logger.error(f"Language detection error: {e}")
        return 'en'  # Default to English
