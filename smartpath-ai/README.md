# SmartPath AI v2.0 - Your AI-Powered Personal Job & Skills Coach

SmartPath AI v2.0 is an advanced intelligent application powered by **Google Gemini AI** that analyzes your CV/resume and provides:
- 🤖 **AI-powered CV analysis** using Google's Gemini AI
- 🎯 **Enhanced job matching** with semantic understanding
- 🧠 **Smart skill extraction** from any CV format
- 🌍 **Multi-language support** (English/Kinyarwanda) with AI translation
- 📚 **Course recommendations** to fill skill gaps
- 🔍 **Advanced text processing** with AI-powered cleanup
- 📊 **Comprehensive analytics** and insights

## 🚀 Quick Start

### Option 1: Automatic Setup (Recommended)
```bash
python setup_and_run.py --start
```

### Option 2: Manual Setup

1. **Install Dependencies**
```bash
pip install -r requirements.txt
```

2. **Start Backend Server**
```bash
cd backend
python -m uvicorn main:app --reload
```

3. **Start Frontend (in a new terminal)**
```bash
cd Frontend
python -m streamlit run app.py
```

## 📱 Usage

1. Open your browser to http://localhost:8501
2. Upload your CV (PDF or TXT format)
3. Select your preferred language
4. View job matches, skill analysis, and course recommendations

## 🔧 API Endpoints

### Core Endpoints
- `GET /` - Health check and system information
- `POST /match` - Enhanced CV matching with AI analysis
- `POST /analyze` - Comprehensive CV analysis using AI
- `GET /recommend?skill={skill}` - Get course recommendations

### AI-Powered Endpoints
- `POST /translate` - AI-powered text translation
- `GET /skills/extract?text={text}` - Extract skills from text using AI
- `GET /docs` - Interactive API documentation

### Enhanced Features
- **AI-powered text cleanup** - Automatically fixes OCR errors and formatting
- **Smart language detection** - Automatically detects CV language
- **Advanced skill extraction** - Uses AI to identify skills beyond keywords
- **Semantic job matching** - Combines AI analysis with traditional matching

## 📁 Project Structure

```
smartpath-ai/
├── backend/
│   ├── main.py              # FastAPI application
│   ├── match_engine.py      # Job matching logic
│   ├── skill_detector.py    # Skill extraction and gap analysis
│   ├── course_recommender.py # Course recommendation engine
│   ├── translator.py        # Translation service
│   ├── jobs_dataset.csv     # Job database
│   └── courses_dataset.csv  # Course database
├── Frontend/
│   └── app.py              # Streamlit frontend
├── requirements.txt        # Python dependencies
├── setup_and_run.py       # Automated setup script
└── test_backend.py        # Backend testing script
```

## 🧪 Testing

Run the backend test to verify everything is working:
```bash
python test_backend.py
```

## 🔍 Features

### Job Matching
- Uses sentence transformers for semantic matching
- Analyzes CV content against job descriptions
- Returns similarity scores

### Skill Analysis
- Extracts skills from CV text
- Compares with job requirements
- Identifies skill gaps

### Course Recommendations
- Suggests relevant courses for missing skills
- Provides direct links to learning resources

### Multi-language Support
- English and Kinyarwanda support
- Translates job titles and content

## 🛠️ Troubleshooting

### Common Issues

1. **Import Errors**: Make sure all dependencies are installed
   ```bash
   python setup_and_run.py
   ```

2. **Backend Not Starting**: Check if port 8000 is available
   ```bash
   netstat -an | findstr :8000
   ```

3. **Frontend Connection Error**: Ensure backend is running on http://localhost:8000

4. **File Upload Issues**: Supported formats are PDF and TXT only

### Dependencies Issues
If you encounter dependency installation issues:
1. Make sure pip is installed: `python -m pip --version`
2. Update pip: `python -m pip install --upgrade pip`
3. Install packages individually if batch install fails

## 📊 Data Files

The application uses CSV files for job and course data:
- `jobs_dataset.csv`: Contains job titles, descriptions, and required skills
- `courses_dataset.csv`: Contains course recommendations with links

You can modify these files to add more jobs or courses.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is open source and available under the MIT License.
