#!/usr/bin/env python3
"""
Test the enhanced CV analysis with AI
"""
import requests
import json

def test_cv_analysis():
    print('🔍 Testing Enhanced CV Analysis with AI...')
    
    # Read sample CV
    with open('sample_cv.txt', 'r', encoding='utf-8') as f:
        cv_content = f.read()
    
    # Test the enhanced match endpoint
    files = {'file': ('sample_cv.txt', cv_content, 'text/plain')}
    response = requests.post('http://localhost:8000/match', files=files, params={'lang': 'en'})
    
    if response.status_code == 200:
        data = response.json()
        print('✅ Analysis successful!')
        print(f'📊 Total matches: {data.get("total_matches", 0)}')
        print(f'🤖 AI Enhanced: {data.get("ai_enhanced", False)}')
        print(f'🌍 Language: {data.get("detected_language", "unknown")}')
        
        matches = data.get('matches', [])
        if matches:
            print(f'\n🎯 Top Job Matches:')
            for i, match in enumerate(matches[:5], 1):
                print(f'\n#{i} {match["title"]} - {match["score"]}% match')
                
                # Show skills analysis
                have_skills = match.get("have_skills", [])
                missing_skills = match.get("missing_skills", [])
                capability_score = match.get("capability_score", match["score"])
                
                print(f'   🎯 Capability Score: {capability_score}%')
                print(f'   ✅ Skills You Have ({len(have_skills)}): {", ".join(have_skills[:5])}')
                print(f'   ❌ Skills to Develop ({len(missing_skills)}): {", ".join(missing_skills[:5])}')
                
                # Show AI reasoning if available
                if match.get('reasoning'):
                    print(f'   🤖 AI Analysis: {match["reasoning"][:100]}...')
        else:
            print('❌ No matches found')
    else:
        print(f'❌ Error: {response.status_code}')
        print(response.text)

def test_skill_extraction():
    print('\n🧠 Testing AI Skill Extraction...')
    
    with open('sample_cv.txt', 'r', encoding='utf-8') as f:
        cv_content = f.read()
    
    response = requests.get('http://localhost:8000/skills/extract', params={'text': cv_content[:1000]})
    
    if response.status_code == 200:
        data = response.json()
        print('✅ Skill extraction successful!')
        print(f'🔢 Skills found: {data.get("skill_count", 0)}')
        skills = data.get("extracted_skills", [])
        print(f'📋 Skills: {", ".join(skills[:10])}')
        print(f'🤖 AI Powered: {data.get("ai_powered", False)}')
    else:
        print(f'❌ Skill extraction failed: {response.status_code}')

if __name__ == "__main__":
    print("🚀 SmartPath AI - Enhanced CV Analysis Test")
    print("=" * 60)
    
    test_cv_analysis()
    test_skill_extraction()
    
    print("\n" + "=" * 60)
    print("🎉 Test completed! Check the results above.")
    print("💡 Try the frontend at: http://localhost:8501")
