#!/usr/bin/env python3
"""
Text-to-Speech module for SmartPath AI
Provides speech synthesis capabilities for responses
"""
import logging
import os
import tempfile
import threading
from typing import Optional

# Setup logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class TextToSpeech:
    """Text-to-Speech handler with multiple engine support"""
    
    def __init__(self):
        self.engine = None
        self.engine_type = None
        self._initialize_engine()
    
    def _initialize_engine(self):
        """Initialize the best available TTS engine"""
        # Try pyttsx3 first (offline, fast)
        try:
            import pyttsx3
            self.engine = pyttsx3.init()
            self.engine_type = "pyttsx3"

            # Configure voice settings safely
            try:
                voices = self.engine.getProperty('voices')
                if voices and len(voices) > 0:
                    # Use the first available voice
                    self.engine.setProperty('voice', voices[0].id)

                # Set speech rate and volume
                self.engine.setProperty('rate', 180)  # Speed of speech
                self.engine.setProperty('volume', 0.9)  # Volume level (0.0 to 1.0)
            except Exception as voice_error:
                logger.warning(f"Voice configuration failed: {voice_error}")

            logger.info("✅ pyttsx3 TTS engine initialized")
            return

        except Exception as e:
            logger.warning(f"pyttsx3 not available: {e}")

        # Try Windows SAPI as fallback
        try:
            import win32com.client
            self.engine = win32com.client.Dispatch("SAPI.SpVoice")
            self.engine_type = "sapi"
            logger.info("✅ Windows SAPI TTS engine initialized")
            return

        except Exception as e:
            logger.warning(f"Windows SAPI not available: {e}")

        # No TTS engine available
        self.engine_type = None
        logger.error("❌ No TTS engine available")
    
    def speak(self, text: str, language: str = "en", async_mode: bool = True) -> bool:
        """
        Convert text to speech
        
        Args:
            text: Text to speak
            language: Language code ('en' for English, 'rw' for Kinyarwanda)
            async_mode: Whether to speak asynchronously
            
        Returns:
            bool: True if successful, False otherwise
        """
        if not text.strip():
            return False
        
        if self.engine_type is None:
            logger.error("No TTS engine available")
            return False
        
        try:
            if async_mode:
                # Run in separate thread to avoid blocking
                thread = threading.Thread(target=self._speak_sync, args=(text, language))
                thread.daemon = True
                thread.start()
                return True
            else:
                return self._speak_sync(text, language)
                
        except Exception as e:
            logger.error(f"Speech synthesis error: {e}")
            return False
    
    def _speak_sync(self, text: str, language: str = "en") -> bool:
        """Synchronous speech synthesis"""
        try:
            if self.engine_type == "pyttsx3":
                return self._speak_pyttsx3(text)
            elif self.engine_type == "sapi":
                return self._speak_sapi(text)
            else:
                return False

        except Exception as e:
            logger.error(f"Synchronous speech error: {e}")
            return False
    
    def _speak_pyttsx3(self, text: str) -> bool:
        """Speak using pyttsx3 (offline)"""
        try:
            self.engine.say(text)
            self.engine.runAndWait()
            return True
        except Exception as e:
            logger.error(f"pyttsx3 speech error: {e}")
            return False

    def _speak_sapi(self, text: str) -> bool:
        """Speak using Windows SAPI (offline)"""
        try:
            self.engine.Speak(text)
            return True
        except Exception as e:
            logger.error(f"SAPI speech error: {e}")
            return False
    
    def stop(self):
        """Stop current speech"""
        try:
            if self.engine_type == "pyttsx3" and self.engine:
                self.engine.stop()
            elif self.engine_type == "sapi" and self.engine:
                # SAPI doesn't have a direct stop method, but we can try
                pass
        except Exception as e:
            logger.error(f"Stop speech error: {e}")

    def is_available(self) -> bool:
        """Check if TTS is available"""
        return self.engine_type is not None

    def get_engine_info(self) -> dict:
        """Get information about the current TTS engine"""
        return {
            "engine_type": self.engine_type,
            "available": self.is_available(),
            "supports_languages": ["en"]  # Both engines support English
        }

# Global TTS instance
tts_engine = TextToSpeech()

def speak_text(text: str, language: str = "en", async_mode: bool = True) -> bool:
    """
    Convenience function to speak text
    
    Args:
        text: Text to speak
        language: Language code
        async_mode: Whether to speak asynchronously
        
    Returns:
        bool: True if successful
    """
    return tts_engine.speak(text, language, async_mode)

def stop_speech():
    """Stop current speech"""
    tts_engine.stop()

def is_tts_available() -> bool:
    """Check if TTS is available"""
    return tts_engine.is_available()

def get_tts_info() -> dict:
    """Get TTS engine information"""
    return tts_engine.get_engine_info()

if __name__ == "__main__":
    # Test the TTS functionality
    print("🔊 Testing Text-to-Speech...")
    
    if is_tts_available():
        print(f"✅ TTS Engine: {get_tts_info()}")
        
        # Test English
        print("🗣️ Speaking in English...")
        speak_text("Hello! Welcome to SmartPath AI. I can help you analyze your CV and find suitable jobs.", "en", False)
        
        # Test with a shorter message
        print("🗣️ Speaking job analysis result...")
        speak_text("You are highly qualified for the Data Analyst position with an 85% match score.", "en", False)
        
        print("🎉 TTS test completed!")
    else:
        print("❌ TTS not available. Please install pyttsx3 or gTTS.")
