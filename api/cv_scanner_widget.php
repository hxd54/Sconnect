<?php
// CV Scanner Widget - Integrates with SmartPath AI CV Analysis
// This widget allows users to upload and analyze CVs using your existing SmartPath AI system
?>

<!-- CV Scanner Widget -->
<div id="cv-scanner-widget" class="cv-scanner-widget">
  <!-- CV Scanner Toggle Button -->
  <div id="cv-scanner-toggle" class="cv-scanner-toggle" onclick="toggleCVScanner()">
    <i class="fas fa-file-alt"></i>
    <span class="cv-scanner-badge">CV</span>
  </div>

  <!-- CV Scanner Window -->
  <div id="cv-scanner-window" class="cv-scanner-window">
    <!-- Header -->
    <div class="cv-scanner-header">
      <div class="cv-scanner-title">
        <i class="fas fa-file-alt"></i>
        <span>SmartPath CV Analyzer</span>
      </div>
      <div class="cv-scanner-controls">
        <button class="cv-scanner-close" onclick="closeCVScanner()">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>

    <!-- CV Upload Section -->
    <div class="cv-upload-section" id="cv-upload-section">
      <div class="upload-area" id="upload-area">
        <i class="fas fa-cloud-upload-alt"></i>
        <h3>Upload Your CV</h3>
        <p>Drag & drop your CV here or click to browse</p>
        <input type="file" id="cv-file-input" accept=".pdf,.doc,.docx,.txt" style="display: none;">
        <button class="upload-btn" onclick="document.getElementById('cv-file-input').click()">
          Choose File
        </button>
      </div>
      
      <div class="analysis-options">
        <label>
          <input type="radio" name="analysis_type" value="analyze" checked>
          <span>Full CV Analysis</span>
        </label>
        <label>
          <input type="radio" name="analysis_type" value="match">
          <span>Job Matching</span>
        </label>
      </div>
    </div>

    <!-- Analysis Results -->
    <div class="cv-analysis-results" id="cv-analysis-results" style="display: none;">
      <div class="analysis-header">
        <h3>Analysis Results</h3>
        <button class="new-analysis-btn" onclick="resetCVScanner()">
          <i class="fas fa-plus"></i> New Analysis
        </button>
      </div>
      <div class="analysis-content" id="analysis-content">
        <!-- Results will be populated here -->
      </div>
    </div>

    <!-- Loading State -->
    <div class="cv-loading" id="cv-loading" style="display: none;">
      <div class="loading-spinner"></div>
      <p>Analyzing your CV with SmartPath AI...</p>
    </div>
  </div>
</div>

<style>
/* CV Scanner Widget Styles */
.cv-scanner-widget {
  position: fixed;
  bottom: 80px;
  right: 20px;
  z-index: 999;
}

.cv-scanner-toggle {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 8px 32px rgba(40, 167, 69, 0.4);
  transition: all 0.3s ease;
  color: white;
  font-size: 1.5rem;
  position: relative;
}

.cv-scanner-toggle:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 40px rgba(40, 167, 69, 0.6);
}

.cv-scanner-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: #dc3545;
  color: white;
  border-radius: 10px;
  padding: 2px 6px;
  font-size: 0.7rem;
  font-weight: bold;
}

.cv-scanner-window {
  position: absolute;
  bottom: 70px;
  right: 0;
  width: 400px;
  height: 500px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.2);
  display: none;
  flex-direction: column;
  animation: slideUp 0.3s ease;
}

.cv-scanner-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  color: white;
  border-radius: 16px 16px 0 0;
}

.cv-scanner-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  font-size: 1rem;
}

.cv-scanner-close {
  background: none;
  border: none;
  color: white;
  font-size: 1.2rem;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 50%;
  transition: background 0.2s ease;
}

.cv-scanner-close:hover {
  background: rgba(255, 255, 255, 0.2);
}

.cv-upload-section {
  padding: 1.5rem;
  flex: 1;
}

.upload-area {
  border: 2px dashed #28a745;
  border-radius: 12px;
  padding: 2rem 1rem;
  text-align: center;
  background: rgba(40, 167, 69, 0.05);
  transition: all 0.3s ease;
  cursor: pointer;
}

.upload-area:hover {
  background: rgba(40, 167, 69, 0.1);
  border-color: #20c997;
}

.upload-area i {
  font-size: 3rem;
  color: #28a745;
  margin-bottom: 1rem;
}

.upload-area h3 {
  margin: 0 0 0.5rem 0;
  color: #333;
}

.upload-area p {
  margin: 0 0 1rem 0;
  color: #666;
  font-size: 0.9rem;
}

.upload-btn {
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s ease;
}

.upload-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

.analysis-options {
  margin-top: 1rem;
  display: flex;
  gap: 1rem;
}

.analysis-options label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  font-size: 0.9rem;
}

.cv-analysis-results {
  padding: 1rem;
  flex: 1;
  overflow-y: auto;
}

.analysis-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.new-analysis-btn {
  background: #28a745;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  gap: 0.3rem;
}

.analysis-content {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 1rem;
  font-size: 0.9rem;
  line-height: 1.5;
}

.cv-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 1rem;
  text-align: center;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #28a745;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Mobile responsive */
@media (max-width: 768px) {
  .cv-scanner-window {
    width: 320px;
    height: 450px;
  }
  
  .cv-scanner-widget {
    bottom: 80px;
    right: 10px;
  }
}
</style>

<script>
// CV Scanner Widget JavaScript
let cvScannerOpen = false;

function toggleCVScanner() {
  const widget = document.getElementById('cv-scanner-window');
  if (cvScannerOpen) {
    closeCVScanner();
  } else {
    openCVScanner();
  }
}

function openCVScanner() {
  const widget = document.getElementById('cv-scanner-window');
  widget.style.display = 'flex';
  cvScannerOpen = true;
}

function closeCVScanner() {
  const widget = document.getElementById('cv-scanner-window');
  widget.style.display = 'none';
  cvScannerOpen = false;
}

function resetCVScanner() {
  document.getElementById('cv-upload-section').style.display = 'block';
  document.getElementById('cv-analysis-results').style.display = 'none';
  document.getElementById('cv-loading').style.display = 'none';
  document.getElementById('cv-file-input').value = '';
}

// File upload handling
document.addEventListener('DOMContentLoaded', function() {
  const fileInput = document.getElementById('cv-file-input');
  const uploadArea = document.getElementById('upload-area');

  // File input change handler
  fileInput.addEventListener('change', function(e) {
    if (e.target.files.length > 0) {
      handleFileUpload(e.target.files[0]);
    }
  });

  // Drag and drop handlers
  uploadArea.addEventListener('dragover', function(e) {
    e.preventDefault();
    uploadArea.style.background = 'rgba(40, 167, 69, 0.15)';
  });

  uploadArea.addEventListener('dragleave', function(e) {
    e.preventDefault();
    uploadArea.style.background = 'rgba(40, 167, 69, 0.05)';
  });

  uploadArea.addEventListener('drop', function(e) {
    e.preventDefault();
    uploadArea.style.background = 'rgba(40, 167, 69, 0.05)';

    if (e.dataTransfer.files.length > 0) {
      handleFileUpload(e.dataTransfer.files[0]);
    }
  });

  // Click to upload
  uploadArea.addEventListener('click', function() {
    fileInput.click();
  });
});

function handleFileUpload(file) {
  // Validate file type
  const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
  if (!allowedTypes.includes(file.type)) {
    alert('Please upload a PDF, DOC, DOCX, or TXT file.');
    return;
  }

  // Validate file size (10MB max)
  if (file.size > 10 * 1024 * 1024) {
    alert('File too large. Maximum size is 10MB.');
    return;
  }

  // Get analysis type
  const analysisType = document.querySelector('input[name="analysis_type"]:checked').value;

  // Show loading state
  document.getElementById('cv-upload-section').style.display = 'none';
  document.getElementById('cv-loading').style.display = 'flex';

  // Prepare form data
  const formData = new FormData();
  formData.append('cv_file', file);
  formData.append('analysis_type', analysisType);
  formData.append('user_name', getCurrentUserName());
  formData.append('language', 'en');

  // Send to CV analysis handler
  fetch('cv_scan_handler.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      displayAnalysisResults(data.analysis, data.analysis_type);
    } else {
      showError(data.error || 'Analysis failed');
    }
  })
  .catch(error => {
    console.error('CV Analysis error:', error);
    showError('Network error. Please try again.');
  })
  .finally(() => {
    document.getElementById('cv-loading').style.display = 'none';
  });
}

function displayAnalysisResults(analysis, analysisType) {
  const resultsSection = document.getElementById('cv-analysis-results');
  const contentDiv = document.getElementById('analysis-content');

  let html = '';

  if (analysisType === 'analyze') {
    // Full CV Analysis Results
    html = `
      <h4><i class="fas fa-chart-line"></i> CV Analysis Summary</h4>
      <div class="analysis-section">
        ${analysis.analysis_summary || 'Analysis completed successfully.'}
      </div>
    `;

    if (analysis.recommendations) {
      html += `
        <h4><i class="fas fa-lightbulb"></i> Recommendations</h4>
        <ul>
          ${analysis.recommendations.map(rec => `<li>${rec}</li>`).join('')}
        </ul>
      `;
    }

    if (analysis.skills_extracted) {
      html += `
        <h4><i class="fas fa-tools"></i> Skills Found</h4>
        <p>Found ${analysis.skills_extracted} skills in your CV.</p>
      `;
    }

  } else if (analysisType === 'match') {
    // Job Matching Results
    html = `
      <h4><i class="fas fa-briefcase"></i> Job Matching Results</h4>
      <div class="analysis-section">
        ${analysis.match_summary || 'Job matching completed.'}
      </div>
    `;

    if (analysis.matched_jobs) {
      html += `
        <h4><i class="fas fa-star"></i> Recommended Jobs</h4>
        <p>Found ${analysis.matched_jobs.length} matching job opportunities.</p>
      `;
    }
  }

  if (analysis.next_steps) {
    html += `
      <h4><i class="fas fa-arrow-right"></i> Next Steps</h4>
      <ul>
        ${analysis.next_steps.map(step => `<li>${step}</li>`).join('')}
      </ul>
    `;
  }

  if (analysis.server_status) {
    html += `
      <div class="server-status" style="background: #fff3cd; padding: 0.5rem; border-radius: 4px; margin-top: 1rem;">
        <small><i class="fas fa-info-circle"></i> ${analysis.server_status}</small>
      </div>
    `;
  }

  contentDiv.innerHTML = html;
  resultsSection.style.display = 'block';
}

function showError(message) {
  const resultsSection = document.getElementById('cv-analysis-results');
  const contentDiv = document.getElementById('analysis-content');

  contentDiv.innerHTML = `
    <div class="error-message" style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 4px;">
      <i class="fas fa-exclamation-triangle"></i> ${message}
    </div>
    <div style="margin-top: 1rem;">
      <h4>Troubleshooting:</h4>
      <ul>
        <li>Make sure SmartPath AI server is running</li>
        <li>Check if the file is a valid CV (PDF, DOC, DOCX, TXT)</li>
        <li>Ensure file size is under 10MB</li>
        <li>Try again in a few moments</li>
      </ul>
    </div>
  `;

  resultsSection.style.display = 'block';
}

function getCurrentUserName() {
  // Try to get user name from various sources
  const userNameElement = document.querySelector('.post-username, .user-name, [data-user-name]');
  if (userNameElement) {
    return userNameElement.textContent || userNameElement.getAttribute('data-user-name') || 'User';
  }
  return 'User';
}
</script>
