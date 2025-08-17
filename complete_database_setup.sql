-- Sconnect Complete Database Setup
-- This script creates ALL necessary tables for the job marketplace platform
-- Run this script to set up the complete database

-- Create database
CREATE DATABASE IF NOT EXISTS sconnect CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sconnect;

-- Drop existing tables if they exist (for clean setup)
DROP TABLE IF EXISTS post_likes;
DROP TABLE IF EXISTS user_job_categories;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS conversations;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS portfolio;
DROP TABLE IF EXISTS user_skills;
DROP TABLE IF EXISTS job_postings;
DROP TABLE IF EXISTS email_tokens;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS mentorship_requests;
DROP TABLE IF EXISTS skill_categories;
DROP TABLE IF EXISTS skills;
DROP TABLE IF EXISTS job_categories;
DROP TABLE IF EXISTS users;

-- Job categories table (main job categories)
CREATE TABLE job_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT NULL,
    icon VARCHAR(50) NULL,
    color VARCHAR(7) NULL,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_is_active (is_active),
    INDEX idx_sort_order (sort_order)
);

-- Skills table (available skills - no direct category relationship)
CREATE TABLE skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_is_active (is_active)
);

-- Skill categories junction table (many-to-many relationship)
CREATE TABLE skill_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    skill_id INT NOT NULL,
    category_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES job_categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_skill_category (skill_id, category_id),
    INDEX idx_skill_id (skill_id),
    INDEX idx_category_id (category_id)
);

-- Users table (main user accounts)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role ENUM('job_seeker', 'job_provider', 'admin') NOT NULL,
    name VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL UNIQUE,
    gender ENUM('Male', 'Female', 'Other') NULL,
    profession VARCHAR(200) NULL,
    location VARCHAR(100) NULL,
    password VARCHAR(255) NOT NULL,
    proof_path VARCHAR(255) NULL,
    verified TINYINT(1) DEFAULT 0,
    suspended TINYINT(1) DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 0.00,
    total_reviews INT DEFAULT 0,
    experience_level ENUM('entry', 'intermediate', 'senior', 'expert') NULL,
    hourly_rate DECIMAL(8,2) NULL,
    availability ENUM('full-time', 'part-time', 'freelance', 'contract') NULL,
    bio TEXT NULL,
    portfolio_url VARCHAR(255) NULL,
    linkedin_url VARCHAR(255) NULL,
    github_url VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_location (location),
    INDEX idx_verified (verified),
    INDEX idx_suspended (suspended),
    INDEX idx_rating (rating),
    INDEX idx_experience_level (experience_level),
    INDEX idx_availability (availability)
);

-- Email tokens table (for email verification and password reset)
CREATE TABLE email_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    type ENUM('verify', 'reset') NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_token (token),
    INDEX idx_type (type),
    INDEX idx_expires_at (expires_at)
);

-- User job category preferences (for job seekers)
CREATE TABLE user_job_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES job_categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_category (user_id, category_id),
    INDEX idx_user_id (user_id),
    INDEX idx_category_id (category_id)
);

-- User skills junction table
CREATE TABLE user_skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    skill_id INT NOT NULL,
    proficiency_level ENUM('beginner', 'intermediate', 'advanced', 'expert') DEFAULT 'intermediate',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_skill (user_id, skill_id),
    INDEX idx_user_id (user_id),
    INDEX idx_skill_id (skill_id)
);

-- Job postings table (job opportunities posted by job providers)
CREATE TABLE job_postings (
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

-- Portfolio table (user achievements and posts)
CREATE TABLE portfolio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    image_path VARCHAR(255) NULL,
    file_path VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
);

-- Conversations table (message threads between users)
CREATE TABLE conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user1_id INT NOT NULL,
    user2_id INT NOT NULL,
    last_message_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user1_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user2_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_conversation (user1_id, user2_id),
    INDEX idx_user1_id (user1_id),
    INDEX idx_user2_id (user2_id),
    INDEX idx_last_message_at (last_message_at)
);

-- Messages table (individual chat messages)
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    sender_id INT NOT NULL,
    message TEXT NOT NULL,
    message_type ENUM('text', 'image', 'file') DEFAULT 'text',
    file_path VARCHAR(255) NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_conversation_id (conversation_id),
    INDEX idx_sender_id (sender_id),
    INDEX idx_created_at (created_at)
);

-- Post likes table (for liking posts and profiles)
CREATE TABLE post_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    post_id INT NULL,
    job_posting_id INT NULL,
    profile_user_id INT NULL,
    like_type ENUM('post', 'job_posting', 'profile') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES portfolio(id) ON DELETE CASCADE,
    FOREIGN KEY (job_posting_id) REFERENCES job_postings(id) ON DELETE CASCADE,
    FOREIGN KEY (profile_user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_like (user_id, post_id, job_posting_id, profile_user_id),
    INDEX idx_user_id (user_id),
    INDEX idx_post_id (post_id),
    INDEX idx_job_posting_id (job_posting_id),
    INDEX idx_profile_user_id (profile_user_id),
    INDEX idx_like_type (like_type)
);

-- Reviews table (user ratings and feedback)
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reviewer_id INT NOT NULL,
    reviewed_id INT NOT NULL,
    job_posting_id INT NULL,
    rating TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (job_posting_id) REFERENCES job_postings(id) ON DELETE SET NULL,
    UNIQUE KEY unique_review (reviewer_id, reviewed_id, job_posting_id),
    INDEX idx_reviewer_id (reviewer_id),
    INDEX idx_reviewed_id (reviewed_id),
    INDEX idx_rating (rating)
);

-- Mentorship requests table (for student-mentor connections)
CREATE TABLE mentorship_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    mentor_id INT NOT NULL,
    message TEXT NULL,
    status ENUM('pending', 'accepted', 'rejected', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (mentor_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_student_id (student_id),
    INDEX idx_mentor_id (mentor_id),
    INDEX idx_status (status)
);

-- Notifications table (for user notifications)
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('message', 'review', 'mentorship', 'job', 'system') NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_type (type),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
);

-- Insert default admin user
INSERT INTO users (role, name, email, phone, password, verified) VALUES
('admin', 'Admin User', 'admin@sconnect.com', '+1234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Insert job categories
INSERT INTO job_categories (id, name, description, icon, color, sort_order) VALUES
(1, 'Agriculture, Forestry & Fishing', 'Farming, forestry, fishing and related agricultural activities', 'fas fa-seedling', '#22C55E', 1),
(2, 'Wholesale & Retail Trade', 'Buying and selling goods, customer service, and retail operations', 'fas fa-store', '#3B82F6', 2),
(3, 'Construction', 'Building construction, infrastructure development, and related trades', 'fas fa-hard-hat', '#F59E0B', 3),
(4, 'Transportation & Storage', 'Logistics, transportation services, and warehouse operations', 'fas fa-truck', '#8B5CF6', 4),
(5, 'Manufacturing', 'Production of goods, quality control, and industrial processes', 'fas fa-industry', '#EF4444', 5),
(6, 'Education', 'Teaching, training, curriculum development, and educational services', 'fas fa-graduation-cap', '#06B6D4', 6),
(7, 'Hospitality & Food Services', 'Hotels, restaurants, tourism, and food service operations', 'fas fa-utensils', '#EC4899', 7),
(8, 'Administrative & Support Services', 'Office administration, business support, and clerical services', 'fas fa-clipboard-list', '#6B7280', 8),
(9, 'Other Services', 'Personal services, repair services, and miscellaneous activities', 'fas fa-tools', '#84CC16', 9),
(10, 'Healthcare & Social Work', 'Medical services, nursing, social care, and health support', 'fas fa-heartbeat', '#DC2626', 10),
(11, 'Financial & Insurance Activities', 'Banking, insurance, accounting, and financial services', 'fas fa-coins', '#059669', 11),
(12, 'Information & Communication (ICT)', 'Technology, software development, and digital communications', 'fas fa-laptop-code', '#4F46E5', 12),
(13, 'Professional, Scientific & Technical Activities', 'Consulting, engineering, legal services, and technical expertise', 'fas fa-microscope', '#7C2D12', 13);

-- Insert skills (unique list)
INSERT INTO skills (name) VALUES
-- Agriculture
('Crop Management'), ('Irrigation Techniques'), ('Animal Husbandry'),
('Agri-Business Management'), ('Forestry Management'), ('Fishing & Aquaculture'),

-- Wholesale & Retail
('Customer Service'), ('Sales Management'), ('Inventory Control'),
('Negotiation'), ('Point of Sale Systems'), ('Retail Marketing'),

-- Construction
('Masonry'), ('Carpentry'), ('Plumbing'), ('Electrical Installation'),
('Project Management'), ('Surveying'),

-- Transportation & Storage
('Logistics Management'), ('Fleet Management'), ('Driving'),
('Supply Chain Planning'), ('Warehouse Operations'), ('Customs Procedures'),

-- Manufacturing
('Machine Operation'), ('Quality Control'), ('Production Planning'),
('Welding'), ('Textile Processing'), ('Food Processing'),

-- Education
('Curriculum Design'), ('Teaching'), ('Classroom Management'),
('E-Learning Tools'), ('Research Skills'), ('Educational Leadership'),

-- Hospitality & Food Services
('Culinary Skills'), ('Housekeeping'), ('Event Management'),
('Front Desk Operations'), ('Food Safety'), ('Tour Guiding'),

-- Administrative & Support
('Office Management'), ('Data Entry'), ('Bookkeeping'),
('Human Resources'), ('Customer Relations'), ('Secretarial Skills'),

-- Other Services
('Tailoring'), ('Beauty Therapy'), ('Barbering'),
('Repair & Maintenance'), ('Cleaning Services'), ('Craftsmanship'),

-- Healthcare & Social Work
('Nursing'), ('First Aid'), ('Public Health'),
('Counseling'), ('Medical Laboratory'), ('Community Outreach'),

-- Financial & Insurance
('Accounting'), ('Auditing'), ('Risk Management'),
('Financial Analysis'), ('Insurance Underwriting'), ('Taxation'),

-- ICT
('Software Development'), ('Database Management'), ('Networking'),
('Cybersecurity'), ('Digital Marketing'), ('Data Analysis'),

-- Professional, Scientific & Technical
('Legal Advisory'), ('Engineering Design'), ('Architecture'),
('Environmental Impact Assessment'), ('Consulting'), ('Research & Development');

-- Map Skills to Categories (junction table)
-- Agriculture (skills 1-6)
INSERT INTO skill_categories (skill_id, category_id) VALUES
(1,1),(2,1),(3,1),(4,1),(5,1),(6,1);

-- Wholesale & Retail (7-12)
INSERT INTO skill_categories (skill_id, category_id) VALUES
(7,2),(8,2),(9,2),(10,2),(11,2),(12,2);

-- Construction (13-18)
INSERT INTO skill_categories (skill_id, category_id) VALUES
(13,3),(14,3),(15,3),(16,3),(17,3),(18,3);

-- Transportation & Storage (19-24)
INSERT INTO skill_categories (skill_id, category_id) VALUES
(19,4),(20,4),(21,4),(22,4),(23,4),(24,4);

-- Manufacturing (25-30)
INSERT INTO skill_categories (skill_id, category_id) VALUES
(25,5),(26,5),(27,5),(28,5),(29,5),(30,5);

-- Education (31-36)
INSERT INTO skill_categories (skill_id, category_id) VALUES
(31,6),(32,6),(33,6),(34,6),(35,6),(36,6);

-- Hospitality & Food Services (37-42)
INSERT INTO skill_categories (skill_id, category_id) VALUES
(37,7),(38,7),(39,7),(40,7),(41,7),(42,7);

-- Admin & Support (43-48)
INSERT INTO skill_categories (skill_id, category_id) VALUES
(43,8),(44,8),(45,8),(46,8),(47,8),(48,8);

-- Other Services (49-54)
INSERT INTO skill_categories (skill_id, category_id) VALUES
(49,9),(50,9),(51,9),(52,9),(53,9),(54,9);

-- Healthcare & Social Work (55-60)
INSERT INTO skill_categories (skill_id, category_id) VALUES
(55,10),(56,10),(57,10),(58,10),(59,10),(60,10);

-- Financial & Insurance (61-66)
INSERT INTO skill_categories (skill_id, category_id) VALUES
(61,11),(62,11),(63,11),(64,11),(65,11),(66,11);

-- ICT (67-72)
INSERT INTO skill_categories (skill_id, category_id) VALUES
(67,12),(68,12),(69,12),(70,12),(71,12),(72,12);

-- Professional, Scientific & Technical (73-78)
INSERT INTO skill_categories (skill_id, category_id) VALUES
(73,13),(74,13),(75,13),(76,13),(77,13),(78,13);
