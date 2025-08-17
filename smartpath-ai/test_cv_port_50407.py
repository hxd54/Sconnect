#!/usr/bin/env python3
"""
Test CV analysis functionality on port 50407
"""
import requests

def test_cv_analysis():
    print('🧪 Testing CV analysis on port 50407...')
    
    try:
        # Test server status first
        response = requests.get('http://localhost:50407/')
        if response.status_code == 200:
            print('✅ Server is running')
            
            # Test CV analysis with sample CV content
            cv_analysis_prompt = """
            Analyze this CV and provide job recommendations:
            
            CV Content: John Doe
            Education: Bachelor's in Computer Science
            Experience: 2 years as Junior Developer
            Skills: Python, JavaScript, SQL, Git
            
            Please provide:
            1. 3-5 suitable job recommendations
            2. Skills analysis
            3. Areas for improvement
            """
            
            chat_response = requests.post('http://localhost:50407/chat', data={
                'message': cv_analysis_prompt,
                'user_name': 'CV Test User'
            })
            
            if chat_response.status_code == 200:
                chat_data = chat_response.json()
                if chat_data.get('success'):
                    print('✅ CV Analysis Test: SUCCESS')
                    ai_response = chat_data.get('ai_response', 'No response')
                    print(f'🤖 AI Analysis: {ai_response[:150]}...')
                    print(f'📊 Category: {chat_data.get("category", "Unknown")}')
                    print('🎉 CV ANALYSIS IS WORKING!')
                    return True
                else:
                    print(f'❌ CV Analysis Error: {chat_data.get("ai_response", "Unknown")}')
            else:
                print(f'❌ CV Analysis HTTP Error: {chat_response.status_code}')
        else:
            print(f'❌ Server Error: {response.status_code}')
            
    except Exception as e:
        print(f'❌ Connection Error: {e}')
        
    return False

if __name__ == "__main__":
    if test_cv_analysis():
        print("\n🎉 SUCCESS! CV analysis is working!")
        print("\n🚀 Your Streamlit CV analysis should now work!")
        print("   • Go to 'CV Analysis & Job Matching'")
        print("   • Upload a CV file")
        print("   • Click 'Analyze CV'")
        print("   • You should get job recommendations!")
    else:
        print("\n❌ CV analysis not working")
        print("💡 Make sure the server is running on port 50407")
