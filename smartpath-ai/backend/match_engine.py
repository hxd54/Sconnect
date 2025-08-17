import pandas as pd
from sentence_transformers import SentenceTransformer, util
import os
import logging
from config import GOOGLE_API_KEY, GEMINI_MODEL, SIMILARITY_THRESHOLD

# Setup logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

model = SentenceTransformer("all-MiniLM-L6-v2")
# Get the directory of this script
script_dir = os.path.dirname(os.path.abspath(__file__))
jobs_df = pd.read_csv(os.path.join(script_dir, "jobs_dataset.csv"))

def match_jobs_with_ai(cv_text):
    """
    AI-powered job matching that analyzes CV content and determines job capability
    """
    try:
        import google.generativeai as genai

        genai.configure(api_key=GOOGLE_API_KEY)
        model_ai = genai.GenerativeModel(GEMINI_MODEL)

        # Create detailed job analysis prompt
        job_list = []
        for _, row in jobs_df.iterrows():
            job_list.append(f"- {row['title']}: {row['description']} (Required skills: {row['skills']})")

        prompt = f"""
        You are an expert career counselor and HR professional. Analyze this CV/resume and determine which jobs this person is CAPABLE of doing based on their skills, experience, and background.

        CV/Resume Content:
        {cv_text}

        Available Jobs:
        {chr(10).join(job_list)}

        For each job, analyze:
        1. Does the person have the required technical skills?
        2. Do they have relevant experience or transferable skills?
        3. What is their overall capability level for this role?
        4. Consider both direct matches and potential with some training

        Provide a capability score from 0-100 for each job where:
        - 90-100: Highly qualified, ready to start immediately
        - 70-89: Well qualified, minor gaps that can be filled quickly
        - 50-69: Moderately qualified, some training needed
        - 30-49: Basic qualification, significant training required
        - 0-29: Not suitable for this role

        Return ONLY the results in this exact format (one per line):
        Job Title: Score

        Results:
        """

        response = model_ai.generate_content(prompt)
        ai_results = parse_ai_job_matches(response.text)

        logger.info(f"AI analyzed {len(ai_results)} job matches")

        # If AI found good matches, return them
        if ai_results and len(ai_results) > 0:
            return ai_results
        else:
            # Fallback to traditional matching if AI parsing failed
            logger.warning("AI parsing failed, falling back to traditional matching")
            return match_jobs_traditional(cv_text)

    except ImportError:
        logger.warning("Google Generative AI not available, using traditional matching")
        return match_jobs_traditional(cv_text)
    except Exception as e:
        logger.error(f"AI job matching error: {e}")
        return match_jobs_traditional(cv_text)

def parse_ai_job_matches(ai_response):
    """
    Parse AI response to extract job matches and scores
    """
    results = []
    lines = ai_response.strip().split('\n')

    for line in lines:
        if ':' in line:
            try:
                parts = line.split(':')
                if len(parts) >= 2:
                    title = parts[0].strip()
                    score_text = parts[1].strip()

                    # Extract numeric score
                    import re
                    score_match = re.search(r'(\d+)', score_text)
                    if score_match:
                        score = float(score_match.group(1))
                        if score >= 30:  # Only include matches above 30%
                            results.append({"title": title, "score": score})
            except Exception as e:
                logger.warning(f"Error parsing AI match line '{line}': {e}")
                continue

    # If no results from AI parsing, return empty list to fall back to traditional
    if not results:
        logger.warning("AI parsing returned no results, will fall back to traditional matching")

    return sorted(results, key=lambda x: x["score"], reverse=True)

def match_jobs_traditional(cv_text):
    """
    Traditional semantic matching using sentence transformers
    """
    cv_embed = model.encode(cv_text, convert_to_tensor=True)
    results = []
    for _, row in jobs_df.iterrows():
        job_embed = model.encode(row["description"], convert_to_tensor=True)
        score = util.pytorch_cos_sim(cv_embed, job_embed).item()
        if score > SIMILARITY_THRESHOLD:
            results.append({"title": row["title"], "score": round(score*100, 2)})
    return sorted(results, key=lambda x: x["score"], reverse=True)

def combine_job_matches(ai_results, traditional_results):
    """
    Combine AI and traditional matching results with weighted scoring
    """
    combined = {}

    # Add AI results with 70% weight
    for result in ai_results:
        title = result["title"]
        combined[title] = result["score"] * 0.7

    # Add traditional results with 30% weight
    for result in traditional_results:
        title = result["title"]
        if title in combined:
            combined[title] += result["score"] * 0.3
        else:
            combined[title] = result["score"] * 0.3

    # Convert back to list format
    final_results = [
        {"title": title, "score": round(score, 2)}
        for title, score in combined.items()
        if score >= 30  # Only include matches above 30%
    ]

    return sorted(final_results, key=lambda x: x["score"], reverse=True)

def match_jobs(cv_text):
    """
    Main job matching function - tries AI first, falls back to traditional
    """
    # Always try traditional matching first to ensure we have results
    traditional_results = match_jobs_traditional(cv_text)

    # If traditional matching found results, try to enhance with AI
    if traditional_results:
        try:
            ai_results = match_jobs_with_ai(cv_text)
            if ai_results and len(ai_results) > 0:
                return ai_results
        except Exception as e:
            logger.warning(f"AI matching failed, using traditional results: {e}")

    # Return traditional results if AI fails or no traditional results
    return traditional_results if traditional_results else get_fallback_matches(cv_text)

def get_fallback_matches(cv_text):
    """
    Fallback matching with very low threshold to ensure some results
    """
    cv_embed = model.encode(cv_text, convert_to_tensor=True)
    results = []
    for _, row in jobs_df.iterrows():
        job_embed = model.encode(row["description"], convert_to_tensor=True)
        score = util.pytorch_cos_sim(cv_embed, job_embed).item()
        if score > 0.1:  # Very low threshold
            results.append({"title": row["title"], "score": round(score*100, 2)})

    # If still no results, return all jobs with basic scores
    if not results:
        logger.warning("No semantic matches found, returning all jobs with basic scoring")
        for _, row in jobs_df.iterrows():
            results.append({"title": row["title"], "score": 25.0})

    return sorted(results, key=lambda x: x["score"], reverse=True)[:5]
