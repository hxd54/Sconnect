import pandas as pd
import os

# Get the directory of this script
script_dir = os.path.dirname(os.path.abspath(__file__))
courses_df = pd.read_csv(os.path.join(script_dir, "courses_dataset.csv"))

def recommend_courses(skill):
    return courses_df[courses_df["skill"].str.contains(skill, case=False)].to_dict(orient="records")
