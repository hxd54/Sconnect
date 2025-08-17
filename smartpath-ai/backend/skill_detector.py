import re
import pandas as pd
import os
import logging
from config import GOOGLE_API_KEY, GEMINI_MODEL

# Setup logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Get the directory of this script
script_dir = os.path.dirname(os.path.abspath(__file__))
jobs_df = pd.read_csv(os.path.join(script_dir, "jobs_dataset.csv"))

def extract_skills_with_ai(cv_text):
    """
    Extract skills and analyze capabilities using Google's Gemini AI
    """
    try:
        import google.generativeai as genai

        genai.configure(api_key=GOOGLE_API_KEY)
        model = genai.GenerativeModel(GEMINI_MODEL)

        prompt = f"""
        You are an expert HR professional. Analyze this CV/resume and extract ALL skills, capabilities, and competencies this person has.

        Look for:
        1. Technical skills (programming languages, software, tools)
        2. Soft skills (communication, leadership, teamwork)
        3. Industry knowledge and experience
        4. Educational background and certifications
        5. Work experience and achievements
        6. Transferable skills from different roles

        CV Content:
        {cv_text}

        Extract every skill, capability, and competency mentioned or implied. Include both explicit skills and those that can be inferred from their experience.

        Return the skills as a comma-separated list. Be comprehensive and include everything relevant.

        Skills:
        """

        response = model.generate_content(prompt)
        skills_text = response.text.strip()

        # Parse the response and clean up the skills
        ai_skills = []
        if skills_text:
            # Split by comma and clean each skill
            raw_skills = skills_text.split(',')
            for skill in raw_skills:
                cleaned_skill = skill.strip().strip('"').strip("'")
                if cleaned_skill and len(cleaned_skill) > 1:
                    ai_skills.append(cleaned_skill)

        # Combine with keyword-based extraction for completeness
        keyword_skills = extract_skills_keywords(cv_text)
        all_skills = set(ai_skills + list(keyword_skills))

        logger.info(f"AI extracted {len(ai_skills)} skills, keyword method found {len(keyword_skills)} skills, total: {len(all_skills)}")
        return all_skills

    except ImportError:
        logger.warning("Google Generative AI not available, using keyword-based extraction")
        return extract_skills_keywords(cv_text)
    except Exception as e:
        logger.error(f"AI skill extraction error: {e}")
        return extract_skills_keywords(cv_text)

def extract_skills_keywords(cv_text):
    """
    Fallback keyword-based skill extraction
    """
    skills_keywords = [
        "Python", "SQL", "Excel", "Data Analysis", "HTML", "CSS", "JavaScript", "React",
        "Circuits", "Troubleshooting", "Safety", "Digital Marketing", "SEO", "Social Media",
        "Photoshop", "Illustrator", "UI/UX Design", "Project Planning", "Team Leadership",
        "Agile", "Communication", "Customer Service", "Android", "iOS", "Flutter",
        "React Native", "Machine Learning", "Data Modeling", "Visualization",
        "Creative Writing", "Copywriting", "Research", "Project Management",
        "Leadership", "Marketing", "Design", "Programming", "Database", "Web Development",
        "Java", "C++", "C#", "PHP", "Ruby", "Swift", "Kotlin", "TypeScript",
        "Node.js", "Angular", "Vue.js", "Django", "Flask", "Spring", "Laravel",
        "MongoDB", "PostgreSQL", "MySQL", "Redis", "Docker", "Kubernetes",
        "AWS", "Azure", "Google Cloud", "Git", "Jenkins", "Terraform",
        "Scrum", "Kanban", "DevOps", "CI/CD", "Testing", "QA", "Automation"
    ]
    found = []
    for skill in skills_keywords:
        if re.search(rf"\b{skill}\b", cv_text, re.IGNORECASE):
            found.append(skill)
    return set(found)

def extract_skills(cv_text):
    """
    Main skill extraction function - tries AI first, falls back to keywords
    """
    return extract_skills_with_ai(cv_text)

def analyze_cv_for_job_capability(cv_text, job_title, job_description, required_skills):
    """
    Use AI to analyze if the person is capable of doing a specific job
    """
    try:
        import google.generativeai as genai

        genai.configure(api_key=GOOGLE_API_KEY)
        model = genai.GenerativeModel(GEMINI_MODEL)

        prompt = f"""
        You are an expert career counselor. Analyze this CV and determine the person's capability for the specific job.

        CV Content:
        {cv_text}

        Job: {job_title}
        Description: {job_description}
        Required Skills: {required_skills}

        Analyze:
        1. Which required skills does the person have?
        2. Which skills are they missing?
        3. What transferable skills do they have?
        4. Overall capability assessment

        Respond in this exact format:
        HAVE_SKILLS: skill1, skill2, skill3
        MISSING_SKILLS: skill4, skill5, skill6
        TRANSFERABLE_SKILLS: skill7, skill8
        CAPABILITY_SCORE: 85
        REASONING: Brief explanation of why they are/aren't suitable
        """

        response = model.generate_content(prompt)
        return parse_capability_analysis(response.text)

    except Exception as e:
        logger.error(f"AI capability analysis error: {e}")
        return None

def parse_capability_analysis(ai_response):
    """
    Parse AI capability analysis response
    """
    result = {
        "have_skills": [],
        "missing_skills": [],
        "transferable_skills": [],
        "capability_score": 50,
        "reasoning": "Analysis not available"
    }

    lines = ai_response.strip().split('\n')
    for line in lines:
        if line.startswith("HAVE_SKILLS:"):
            skills = line.replace("HAVE_SKILLS:", "").strip()
            result["have_skills"] = [s.strip() for s in skills.split(',') if s.strip()]
        elif line.startswith("MISSING_SKILLS:"):
            skills = line.replace("MISSING_SKILLS:", "").strip()
            result["missing_skills"] = [s.strip() for s in skills.split(',') if s.strip()]
        elif line.startswith("TRANSFERABLE_SKILLS:"):
            skills = line.replace("TRANSFERABLE_SKILLS:", "").strip()
            result["transferable_skills"] = [s.strip() for s in skills.split(',') if s.strip()]
        elif line.startswith("CAPABILITY_SCORE:"):
            try:
                score = int(line.replace("CAPABILITY_SCORE:", "").strip())
                result["capability_score"] = score
            except:
                pass
        elif line.startswith("REASONING:"):
            result["reasoning"] = line.replace("REASONING:", "").strip()

    return result

def detect_skill_gaps(cv_text, matched_jobs):
    """
    Enhanced skill gap detection using AI analysis
    """
    cv_skills = extract_skills(cv_text)
    results = []

    # Ensure we have some skills detected
    if not cv_skills:
        logger.warning("No skills detected from CV, using basic skill detection")
        cv_skills = {"Communication", "Problem Solving", "Teamwork"}

    for job in matched_jobs:
        try:
            row = jobs_df[jobs_df["title"] == job["title"]].iloc[0]
            required_skills_str = row["skills"]

            # Use AI to analyze capability for this specific job
            ai_analysis = analyze_cv_for_job_capability(
                cv_text,
                job["title"],
                row["description"],
                required_skills_str
            )

            if ai_analysis:
                # Use AI analysis results
                results.append({
                    "title": job["title"],
                    "score": job["score"],
                    "have_skills": ai_analysis["have_skills"],
                    "missing_skills": ai_analysis["missing_skills"],
                    "transferable_skills": ai_analysis.get("transferable_skills", []),
                    "capability_score": ai_analysis.get("capability_score", job["score"]),
                    "reasoning": ai_analysis.get("reasoning", "")
                })
            else:
                # Fallback to traditional analysis
                required_skills = set(required_skills_str.split(";"))
                missing = required_skills - cv_skills
                have_skills = cv_skills & required_skills

                results.append({
                    "title": job["title"],
                    "score": job["score"],
                    "have_skills": list(have_skills),
                    "missing_skills": list(missing),
                    "transferable_skills": [],
                    "capability_score": job["score"],
                    "reasoning": "Traditional skill matching analysis"
                })

        except IndexError:
            # If job not found in dataset, return basic info
            logger.warning(f"Job '{job['title']}' not found in dataset")
            results.append({
                "title": job["title"],
                "score": job["score"],
                "have_skills": ["Communication", "Problem Solving"],
                "missing_skills": ["Technical Skills", "Industry Knowledge"],
                "transferable_skills": [],
                "capability_score": job["score"],
                "reasoning": "Job details not found in database"
            })

    return results
