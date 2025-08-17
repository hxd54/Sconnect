-- Add job postings table to existing Sconnect database
-- Run this script to add job posting functionality for seekers

USE sconnect;

-- Job postings table (seeker job opportunities)
CREATE TABLE IF NOT EXISTS job_postings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    budget_min DECIMAL(10,2) NULL,
    budget_max DECIMAL(10,2) NULL,
    location VARCHAR(100) NULL,
    job_type ENUM('full-time', 'part-time', 'contract', 'freelance') DEFAULT 'contract',
    requirements TEXT NULL,
    skills_required VARCHAR(500) NULL,
    deadline DATE NULL,
    status ENUM('open', 'in_progress', 'completed', 'cancelled') DEFAULT 'open',
    image_path VARCHAR(255) NULL,
    file_path VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_job_type (job_type),
    INDEX idx_location (location),
    INDEX idx_created_at (created_at)
);

-- Insert some sample job postings for testing (optional)
-- Uncomment the lines below if you want sample data

/*
INSERT INTO job_postings (user_id, title, description, budget_min, budget_max, location, job_type, requirements, skills_required, deadline) VALUES 
(1, 'E-commerce Website Development', 'Need a professional to build a modern e-commerce website with payment integration and inventory management.', 1500.00, 3000.00, 'Remote', 'contract', 'Experience with e-commerce platforms, payment gateways, and responsive design', 'PHP, JavaScript, MySQL, HTML, CSS', '2024-12-31'),
(1, 'Mobile App UI/UX Design', 'Looking for a talented designer to create intuitive and modern UI/UX for our mobile application.', 800.00, 1500.00, 'New York', 'freelance', 'Portfolio showcasing mobile app designs, understanding of user experience principles', 'Figma, Adobe XD, Sketch, Prototyping', '2024-11-15'),
(1, 'Social Media Marketing Campaign', 'Seeking a digital marketing expert to run a comprehensive social media campaign for our startup.', 500.00, 1200.00, 'Remote', 'part-time', 'Proven track record in social media marketing, content creation skills', 'Facebook Ads, Instagram Marketing, Content Creation, Analytics', '2024-10-30');
*/
