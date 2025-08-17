#!/usr/bin/env python3
"""
SmartPath AI Database Viewer
View and analyze all stored chat conversations
"""
import streamlit as st
import pandas as pd
import sqlite3
from datetime import datetime, timedelta
import plotly.express as px
import plotly.graph_objects as go
from chat_database import ChatDatabase

# Page config
st.set_page_config(
    page_title="SmartPath AI Database Viewer",
    page_icon="🗄️",
    layout="wide"
)

# Initialize database
@st.cache_resource
def init_database():
    return ChatDatabase()

db = init_database()

# Title
st.title("🗄️ SmartPath AI Database Viewer")
st.markdown("**View and analyze all stored chat conversations**")

# Sidebar navigation
st.sidebar.title("📊 Navigation")
page = st.sidebar.selectbox("Choose a view:", [
    "📈 Analytics Dashboard",
    "💬 Chat History",
    "🔍 Message Search",
    "👥 User Analytics",
    "📊 Popular Topics",
    "⚙️ Database Management"
])

if page == "📈 Analytics Dashboard":
    st.header("📈 Analytics Dashboard")
    
    # Get analytics
    analytics = db.get_analytics()
    
    # Key metrics
    col1, col2, col3, col4 = st.columns(4)
    
    with col1:
        st.metric("Total Sessions", analytics['total_sessions'])
    with col2:
        st.metric("Total Messages", analytics['total_messages'])
    with col3:
        st.metric("Unique Users", analytics['unique_users'])
    with col4:
        avg_messages = analytics['total_messages'] / max(analytics['total_sessions'], 1)
        st.metric("Avg Messages/Session", f"{avg_messages:.1f}")
    
    # Charts
    col1, col2 = st.columns(2)
    
    with col1:
        st.subheader("📊 Popular Categories")
        if analytics['popular_categories']:
            categories_df = pd.DataFrame(analytics['popular_categories'], columns=['Category', 'Count'])
            fig = px.pie(categories_df, values='Count', names='Category', title="Message Categories")
            st.plotly_chart(fig, use_container_width=True)
        else:
            st.info("No category data available yet")
    
    with col2:
        st.subheader("📅 Recent Activity")
        if analytics['recent_activity']:
            activity_df = pd.DataFrame(analytics['recent_activity'], columns=['Date', 'Messages'])
            fig = px.bar(activity_df, x='Date', y='Messages', title="Messages per Day (Last 7 Days)")
            st.plotly_chart(fig, use_container_width=True)
        else:
            st.info("No recent activity data available")

elif page == "💬 Chat History":
    st.header("💬 Chat History")
    
    # Get all sessions
    conn = sqlite3.connect(db.db_path)
    sessions_df = pd.read_sql_query('''
        SELECT session_id, user_name, start_time, end_time, total_messages, session_type
        FROM chat_sessions 
        ORDER BY start_time DESC
    ''', conn)
    conn.close()
    
    if not sessions_df.empty:
        st.subheader("🗂️ All Chat Sessions")
        
        # Session selector
        selected_session = st.selectbox(
            "Select a session to view:",
            options=sessions_df['session_id'].tolist(),
            format_func=lambda x: f"{sessions_df[sessions_df['session_id']==x]['user_name'].iloc[0] or 'Anonymous'} - {sessions_df[sessions_df['session_id']==x]['start_time'].iloc[0]} ({sessions_df[sessions_df['session_id']==x]['total_messages'].iloc[0]} messages)"
        )
        
        if selected_session:
            # Get messages for selected session
            history = db.get_chat_history(selected_session)
            
            if history:
                st.subheader(f"💬 Messages in Session: {selected_session[:8]}...")
                
                for msg in history:
                    with st.expander(f"Message #{msg['message_number']} - {msg['timestamp']} ({msg['category']})"):
                        st.write("**👤 User:**")
                        st.write(msg['user_message'])
                        st.write("**🤖 AI Response:**")
                        st.write(msg['ai_response'])
                        st.write(f"**Category:** {msg['category']} | **Sentiment:** {msg['sentiment']}")
            else:
                st.info("No messages found in this session")
        
        # Sessions table
        st.subheader("📋 Sessions Overview")
        st.dataframe(sessions_df, use_container_width=True)
    else:
        st.info("No chat sessions found in the database")

elif page == "🔍 Message Search":
    st.header("🔍 Message Search")
    
    # Search interface
    search_term = st.text_input("🔍 Search messages:", placeholder="Enter keywords to search...")
    search_limit = st.slider("Maximum results:", 10, 100, 50)
    
    if search_term:
        with st.spinner("Searching messages..."):
            results = db.search_messages(search_term, search_limit)
        
        if results:
            st.success(f"Found {len(results)} messages containing '{search_term}'")
            
            for i, result in enumerate(results, 1):
                with st.expander(f"Result #{i} - {result['timestamp']} ({result['category']})"):
                    st.write(f"**👤 User ({result['user_name'] or 'Anonymous'}):**")
                    st.write(result['user_message'])
                    st.write("**🤖 AI Response:**")
                    st.write(result['ai_response'])
                    st.write(f"**Session:** {result['session_id'][:8]}... | **Category:** {result['category']}")
        else:
            st.warning(f"No messages found containing '{search_term}'")

elif page == "👥 User Analytics":
    st.header("👥 User Analytics")
    
    # Get user statistics
    conn = sqlite3.connect(db.db_path)
    
    # Users with most sessions
    user_sessions_df = pd.read_sql_query('''
        SELECT user_name, COUNT(*) as session_count, SUM(total_messages) as total_messages,
               MIN(start_time) as first_visit, MAX(start_time) as last_visit
        FROM chat_sessions 
        WHERE user_name IS NOT NULL
        GROUP BY user_name
        ORDER BY session_count DESC
    ''', conn)
    
    # Message distribution by hour
    hourly_df = pd.read_sql_query('''
        SELECT strftime('%H', timestamp) as hour, COUNT(*) as message_count
        FROM messages
        GROUP BY strftime('%H', timestamp)
        ORDER BY hour
    ''', conn)
    
    conn.close()
    
    col1, col2 = st.columns(2)
    
    with col1:
        st.subheader("👥 Most Active Users")
        if not user_sessions_df.empty:
            st.dataframe(user_sessions_df, use_container_width=True)
        else:
            st.info("No named users found")
    
    with col2:
        st.subheader("🕐 Messages by Hour")
        if not hourly_df.empty:
            fig = px.bar(hourly_df, x='hour', y='message_count', title="Message Activity by Hour")
            st.plotly_chart(fig, use_container_width=True)
        else:
            st.info("No hourly data available")

elif page == "📊 Popular Topics":
    st.header("📊 Popular Topics")
    
    # Get popular topics
    conn = sqlite3.connect(db.db_path)
    topics_df = pd.read_sql_query('''
        SELECT topic_name, category, mention_count, last_mentioned, sample_questions
        FROM popular_topics
        ORDER BY mention_count DESC
    ''', conn)
    conn.close()
    
    if not topics_df.empty:
        st.subheader("🔥 Most Discussed Topics")
        
        for _, topic in topics_df.iterrows():
            with st.expander(f"{topic['topic_name'].title()} ({topic['mention_count']} mentions)"):
                st.write(f"**Category:** {topic['category']}")
                st.write(f"**Last Mentioned:** {topic['last_mentioned']}")
                st.write(f"**Sample Question:** {topic['sample_questions']}")
        
        # Topic chart
        fig = px.bar(topics_df.head(10), x='mention_count', y='topic_name', 
                     orientation='h', title="Top 10 Topics by Mentions")
        st.plotly_chart(fig, use_container_width=True)
    else:
        st.info("No topics data available yet")

elif page == "⚙️ Database Management":
    st.header("⚙️ Database Management")
    
    # Database info
    st.subheader("📊 Database Information")
    
    try:
        import os
        db_size = os.path.getsize(db.db_path)
        st.write(f"**Database File:** {db.db_path}")
        st.write(f"**File Size:** {db_size / 1024:.2f} KB")
        st.write(f"**Last Modified:** {datetime.fromtimestamp(os.path.getmtime(db.db_path))}")
    except:
        st.error("Could not get database file information")
    
    # Database actions
    st.subheader("🔧 Database Actions")
    
    col1, col2, col3 = st.columns(3)
    
    with col1:
        if st.button("🔄 Refresh Analytics"):
            st.cache_resource.clear()
            st.success("Analytics refreshed!")
            st.rerun()
    
    with col2:
        if st.button("📊 Export Data"):
            # Export functionality could be added here
            st.info("Export functionality coming soon!")
    
    with col3:
        if st.button("🧹 Clean Old Data"):
            # Cleanup functionality could be added here
            st.info("Cleanup functionality coming soon!")
    
    # Raw database queries
    st.subheader("🔍 Custom Query")
    custom_query = st.text_area("Enter SQL query:", placeholder="SELECT * FROM messages LIMIT 10;")
    
    if st.button("Execute Query") and custom_query:
        try:
            conn = sqlite3.connect(db.db_path)
            result_df = pd.read_sql_query(custom_query, conn)
            conn.close()
            
            st.success("Query executed successfully!")
            st.dataframe(result_df, use_container_width=True)
        except Exception as e:
            st.error(f"Query error: {str(e)}")

# Footer
st.markdown("---")
st.markdown("🗄️ **SmartPath AI Database Viewer** - Monitor and analyze all chat conversations")

if __name__ == "__main__":
    st.write("Run with: streamlit run database_viewer.py")
