#!/usr/bin/env python3
"""
SmartPath AI Chat Database System
Stores all messaging chats with full history and analytics
"""
import sqlite3
import json
from datetime import datetime
import uuid
import os

class ChatDatabase:
    def __init__(self, db_path="smartpath_chats.db"):
        """Initialize the chat database"""
        self.db_path = db_path
        self.init_database()
    
    def init_database(self):
        """Create database tables if they don't exist"""
        conn = sqlite3.connect(self.db_path)
        cursor = conn.cursor()
        
        # Chat sessions table
        cursor.execute('''
            CREATE TABLE IF NOT EXISTS chat_sessions (
                session_id TEXT PRIMARY KEY,
                user_name TEXT,
                start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                end_time TIMESTAMP,
                total_messages INTEGER DEFAULT 0,
                session_type TEXT DEFAULT 'general',
                user_ip TEXT,
                user_agent TEXT
            )
        ''')
        
        # Individual messages table
        cursor.execute('''
            CREATE TABLE IF NOT EXISTS messages (
                message_id TEXT PRIMARY KEY,
                session_id TEXT,
                message_number INTEGER,
                timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                user_message TEXT NOT NULL,
                ai_response TEXT NOT NULL,
                response_time_ms INTEGER,
                message_category TEXT,
                keywords TEXT,
                sentiment TEXT,
                user_satisfaction INTEGER,
                FOREIGN KEY (session_id) REFERENCES chat_sessions (session_id)
            )
        ''')
        
        # User analytics table
        cursor.execute('''
            CREATE TABLE IF NOT EXISTS user_analytics (
                user_id TEXT PRIMARY KEY,
                user_name TEXT,
                total_sessions INTEGER DEFAULT 0,
                total_messages INTEGER DEFAULT 0,
                first_visit TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_visit TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                favorite_topics TEXT,
                career_stage TEXT,
                goals TEXT
            )
        ''')
        
        # Popular topics table
        cursor.execute('''
            CREATE TABLE IF NOT EXISTS popular_topics (
                topic_id TEXT PRIMARY KEY,
                topic_name TEXT UNIQUE,
                category TEXT,
                mention_count INTEGER DEFAULT 1,
                last_mentioned TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                sample_questions TEXT
            )
        ''')
        
        conn.commit()
        conn.close()
        print("✅ Chat database initialized successfully!")
    
    def start_session(self, user_name=None, session_type="general", user_ip=None, user_agent=None):
        """Start a new chat session"""
        session_id = str(uuid.uuid4())
        
        conn = sqlite3.connect(self.db_path)
        cursor = conn.cursor()
        
        cursor.execute('''
            INSERT INTO chat_sessions 
            (session_id, user_name, session_type, user_ip, user_agent)
            VALUES (?, ?, ?, ?, ?)
        ''', (session_id, user_name, session_type, user_ip, user_agent))
        
        conn.commit()
        conn.close()
        
        print(f"🆕 New chat session started: {session_id}")
        return session_id
    
    def save_message(self, session_id, user_message, ai_response, response_time_ms=0, 
                    message_category=None, keywords=None, sentiment=None):
        """Save a chat message to the database"""
        message_id = str(uuid.uuid4())
        
        conn = sqlite3.connect(self.db_path)
        cursor = conn.cursor()
        
        # Get message number for this session
        cursor.execute('SELECT COUNT(*) FROM messages WHERE session_id = ?', (session_id,))
        message_number = cursor.fetchone()[0] + 1
        
        # Determine category from keywords
        if not message_category:
            message_category = self._categorize_message(user_message)
        
        # Extract keywords if not provided
        if not keywords:
            keywords = self._extract_keywords(user_message)
        
        # Simple sentiment analysis
        if not sentiment:
            sentiment = self._analyze_sentiment(user_message)
        
        cursor.execute('''
            INSERT INTO messages 
            (message_id, session_id, message_number, user_message, ai_response, 
             response_time_ms, message_category, keywords, sentiment)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ''', (message_id, session_id, message_number, user_message, ai_response,
              response_time_ms, message_category, keywords, sentiment))
        
        # Update session message count
        cursor.execute('''
            UPDATE chat_sessions 
            SET total_messages = total_messages + 1 
            WHERE session_id = ?
        ''', (session_id,))
        
        # Update popular topics
        self._update_popular_topics(message_category, user_message)
        
        conn.commit()
        conn.close()
        
        print(f"💾 Message saved: {message_id}")
        return message_id
    
    def end_session(self, session_id):
        """End a chat session"""
        conn = sqlite3.connect(self.db_path)
        cursor = conn.cursor()
        
        cursor.execute('''
            UPDATE chat_sessions 
            SET end_time = CURRENT_TIMESTAMP 
            WHERE session_id = ?
        ''', (session_id,))
        
        conn.commit()
        conn.close()
        
        print(f"🔚 Session ended: {session_id}")
    
    def get_chat_history(self, session_id):
        """Get all messages from a chat session"""
        conn = sqlite3.connect(self.db_path)
        cursor = conn.cursor()
        
        cursor.execute('''
            SELECT message_number, timestamp, user_message, ai_response, 
                   message_category, sentiment
            FROM messages 
            WHERE session_id = ? 
            ORDER BY message_number
        ''', (session_id,))
        
        messages = cursor.fetchall()
        conn.close()
        
        return [
            {
                'message_number': msg[0],
                'timestamp': msg[1],
                'user_message': msg[2],
                'ai_response': msg[3],
                'category': msg[4],
                'sentiment': msg[5]
            }
            for msg in messages
        ]
    
    def get_user_sessions(self, user_name):
        """Get all sessions for a specific user"""
        conn = sqlite3.connect(self.db_path)
        cursor = conn.cursor()
        
        cursor.execute('''
            SELECT session_id, start_time, end_time, total_messages, session_type
            FROM chat_sessions 
            WHERE user_name = ? 
            ORDER BY start_time DESC
        ''', (user_name,))
        
        sessions = cursor.fetchall()
        conn.close()
        
        return [
            {
                'session_id': session[0],
                'start_time': session[1],
                'end_time': session[2],
                'total_messages': session[3],
                'session_type': session[4]
            }
            for session in sessions
        ]
    
    def get_analytics(self):
        """Get chat analytics and statistics"""
        conn = sqlite3.connect(self.db_path)
        cursor = conn.cursor()
        
        # Total statistics
        cursor.execute('SELECT COUNT(*) FROM chat_sessions')
        total_sessions = cursor.fetchone()[0]
        
        cursor.execute('SELECT COUNT(*) FROM messages')
        total_messages = cursor.fetchone()[0]
        
        cursor.execute('SELECT COUNT(DISTINCT user_name) FROM chat_sessions WHERE user_name IS NOT NULL')
        unique_users = cursor.fetchone()[0]
        
        # Popular categories
        cursor.execute('''
            SELECT message_category, COUNT(*) as count
            FROM messages 
            GROUP BY message_category 
            ORDER BY count DESC 
            LIMIT 10
        ''')
        popular_categories = cursor.fetchall()
        
        # Recent activity
        cursor.execute('''
            SELECT DATE(timestamp) as date, COUNT(*) as messages
            FROM messages 
            WHERE timestamp >= datetime('now', '-7 days')
            GROUP BY DATE(timestamp)
            ORDER BY date DESC
        ''')
        recent_activity = cursor.fetchall()
        
        conn.close()
        
        return {
            'total_sessions': total_sessions,
            'total_messages': total_messages,
            'unique_users': unique_users,
            'popular_categories': popular_categories,
            'recent_activity': recent_activity
        }
    
    def search_messages(self, search_term, limit=50):
        """Search through chat messages"""
        conn = sqlite3.connect(self.db_path)
        cursor = conn.cursor()
        
        cursor.execute('''
            SELECT m.timestamp, m.user_message, m.ai_response, m.message_category,
                   cs.user_name, cs.session_id
            FROM messages m
            JOIN chat_sessions cs ON m.session_id = cs.session_id
            WHERE m.user_message LIKE ? OR m.ai_response LIKE ?
            ORDER BY m.timestamp DESC
            LIMIT ?
        ''', (f'%{search_term}%', f'%{search_term}%', limit))
        
        results = cursor.fetchall()
        conn.close()
        
        return [
            {
                'timestamp': result[0],
                'user_message': result[1],
                'ai_response': result[2],
                'category': result[3],
                'user_name': result[4],
                'session_id': result[5]
            }
            for result in results
        ]
    
    def _categorize_message(self, message):
        """Categorize message based on content"""
        message_lower = message.lower()
        
        if any(word in message_lower for word in ["job", "work", "career", "position", "role"]):
            return "job_search"
        elif any(word in message_lower for word in ["cv", "resume", "application"]):
            return "cv_resume"
        elif any(word in message_lower for word in ["skill", "learn", "training", "course"]):
            return "skill_development"
        elif any(word in message_lower for word in ["interview", "prepare", "questions"]):
            return "interview_prep"
        elif any(word in message_lower for word in ["salary", "money", "pay", "negotiate"]):
            return "compensation"
        elif any(word in message_lower for word in ["network", "linkedin", "connections"]):
            return "networking"
        else:
            return "general"
    
    def _extract_keywords(self, message):
        """Extract keywords from message"""
        # Simple keyword extraction
        common_words = {'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'can', 'i', 'you', 'he', 'she', 'it', 'we', 'they', 'me', 'him', 'her', 'us', 'them', 'my', 'your', 'his', 'her', 'its', 'our', 'their'}
        
        words = message.lower().split()
        keywords = [word.strip('.,!?;:') for word in words if len(word) > 3 and word not in common_words]
        
        return ', '.join(keywords[:10])  # Top 10 keywords
    
    def _analyze_sentiment(self, message):
        """Simple sentiment analysis"""
        positive_words = ['good', 'great', 'excellent', 'amazing', 'wonderful', 'fantastic', 'love', 'like', 'happy', 'excited']
        negative_words = ['bad', 'terrible', 'awful', 'hate', 'dislike', 'sad', 'frustrated', 'angry', 'worried', 'confused']
        
        message_lower = message.lower()
        positive_count = sum(1 for word in positive_words if word in message_lower)
        negative_count = sum(1 for word in negative_words if word in message_lower)
        
        if positive_count > negative_count:
            return 'positive'
        elif negative_count > positive_count:
            return 'negative'
        else:
            return 'neutral'
    
    def _update_popular_topics(self, category, message):
        """Update popular topics tracking"""
        conn = sqlite3.connect(self.db_path)
        cursor = conn.cursor()
        
        cursor.execute('''
            INSERT OR REPLACE INTO popular_topics 
            (topic_id, topic_name, category, mention_count, last_mentioned, sample_questions)
            VALUES (?, ?, ?, 
                    COALESCE((SELECT mention_count FROM popular_topics WHERE topic_name = ?) + 1, 1),
                    CURRENT_TIMESTAMP, ?)
        ''', (str(uuid.uuid4()), category, category, category, message[:200]))
        
        conn.commit()
        conn.close()

# Test the database
if __name__ == "__main__":
    # Initialize database
    db = ChatDatabase()
    
    # Test session
    session_id = db.start_session("Test User", "demo")
    
    # Test messages
    db.save_message(session_id, "What jobs am I qualified for?", "Based on your background, you could be qualified for...")
    db.save_message(session_id, "How can I improve my CV?", "To improve your CV, focus on...")
    
    # End session
    db.end_session(session_id)
    
    # Get analytics
    analytics = db.get_analytics()
    print("📊 Analytics:", analytics)
    
    print("✅ Database test completed!")
