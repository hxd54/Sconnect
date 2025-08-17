-- Sconnect Database Setup
-- Complete database schema for the professional services connection platform

-- Create database
CREATE DATABASE IF NOT EXISTS sconnect CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sconnect;

-- Drop existing tables if they exist (for clean setup)
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS portfolio;
DROP TABLE IF EXISTS user_skills;
DROP TABLE IF EXISTS skills;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS mentorship_requests;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS conversations;
DROP TABLE IF EXISTS email_tokens;
DROP TABLE IF EXISTS admin_logs;
DROP TABLE IF EXISTS contract_templates;
DROP TABLE IF EXISTS faqs;
DROP TABLE IF EXISTS resources;
DROP TABLE IF EXISTS usage_reports;
DROP TABLE IF EXISTS users;

-- Users table (main user accounts)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role ENUM('job_seeker', 'job_provider', 'admin') NOT NULL,
    name VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(100)  NOT NULL,
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

-- Skills table (available skills/tags within categories)
CREATE TABLE skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    category_id INT NULL,
    description TEXT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES job_categories(id) ON DELETE SET NULL,
    INDEX idx_category_id (category_id),
    INDEX idx_is_active (is_active)
);

-- User skills relationship table
CREATE TABLE user_skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    skill_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_skill (user_id, skill_id),
    INDEX idx_user_id (user_id),
    INDEX idx_skill_id (skill_id)
);

-- Portfolio table (wager's work samples)
CREATE TABLE portfolio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    image_path VARCHAR(255) NULL,
    file_path VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
);

-- Job postings table (seeker job opportunities)
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

-- Reviews table (client reviews for wagers)
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    wager_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (wager_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_review (wager_id, reviewer_id),
    INDEX idx_wager_id (wager_id),
    INDEX idx_reviewer_id (reviewer_id),
    INDEX idx_rating (rating)
);

-- Conversations table (chat conversations)
CREATE TABLE conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user1_id INT NOT NULL,
    user2_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user1_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user2_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_conversation (LEAST(user1_id, user2_id), GREATEST(user1_id, user2_id)),
    INDEX idx_user1_id (user1_id),
    INDEX idx_user2_id (user2_id)
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

-- Mentorship requests table
CREATE TABLE mentorship_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    wager_id INT NOT NULL,
    message TEXT NULL,
    status ENUM('pending', 'accepted', 'rejected', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (wager_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_student_id (student_id),
    INDEX idx_wager_id (wager_id),
    INDEX idx_status (status)
);

-- Notifications table
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('message', 'review', 'mentorship', 'system') NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    related_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
);

-- Email tokens table (for verification and password reset)
CREATE TABLE email_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    type ENUM('verify', 'reset') NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
);

-- Admin logs table (for tracking admin actions)
CREATE TABLE admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    target_user_id INT NULL,
    details TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_admin_id (admin_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);

-- Contract templates table
CREATE TABLE contract_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    file_path VARCHAR(255) NOT NULL,
    category VARCHAR(100) NULL,
    is_active TINYINT(1) DEFAULT 1,
    downloads INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_is_active (is_active)
);

-- FAQs table
CREATE TABLE faqs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    category VARCHAR(100) NULL,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_is_active (is_active),
    INDEX idx_sort_order (sort_order)
);

-- Resources table (educational content)
CREATE TABLE resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    type ENUM('video', 'article', 'document', 'link') NOT NULL,
    content_url VARCHAR(500) NULL,
    file_path VARCHAR(255) NULL,
    category VARCHAR(100) NULL,
    is_active TINYINT(1) DEFAULT 1,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_category (category),
    INDEX idx_is_active (is_active)
);

-- Usage reports table
CREATE TABLE usage_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_type VARCHAR(100) NOT NULL,
    report_data JSON NOT NULL,
    generated_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_report_type (report_type),
    INDEX idx_created_at (created_at)
);

-- Insert default admin user
INSERT INTO users (role, name, email, phone, password, verified) VALUES
('admin', 'Admin User', 'admin@sconnect.com', '+1234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Insert job categories
INSERT INTO job_categories (name, description, icon, color, sort_order) VALUES
('Web Development', 'Frontend, backend, and full-stack web development', 'fas fa-code', '#3B82F6', 1),
('Mobile Development', 'iOS, Android, and cross-platform mobile app development', 'fas fa-mobile-alt', '#10B981', 2),
('UI/UX Design', 'User interface and user experience design', 'fas fa-paint-brush', '#F59E0B', 3),
('Graphic Design', 'Logo design, branding, print design, and digital graphics', 'fas fa-palette', '#EF4444', 4),
('Digital Marketing', 'SEO, social media marketing, content marketing, and advertising', 'fas fa-bullhorn', '#8B5CF6', 5),
('Content Writing', 'Blog writing, copywriting, technical writing, and content creation', 'fas fa-pen', '#06B6D4', 6),
('Data Science', 'Data analysis, machine learning, and business intelligence', 'fas fa-chart-bar', '#84CC16', 7),
('DevOps & Cloud', 'Cloud infrastructure, deployment, and system administration', 'fas fa-cloud', '#6366F1', 8),
('Video & Animation', 'Video editing, motion graphics, and 3D animation', 'fas fa-video', '#EC4899', 9),
('Photography', 'Product photography, event photography, and photo editing', 'fas fa-camera', '#F97316', 10),
('Translation', 'Language translation and localization services', 'fas fa-language', '#14B8A6', 11),
('Virtual Assistant', 'Administrative support, customer service, and data entry', 'fas fa-user-tie', '#6B7280', 12),
('Accounting & Finance', 'Bookkeeping, financial analysis, and tax preparation', 'fas fa-calculator', '#059669', 13),
('Legal Services', 'Legal consulting, contract review, and compliance', 'fas fa-gavel', '#7C2D12', 14),
('Music & Audio', 'Music production, audio editing, and voice-over services', 'fas fa-music', '#BE185D', 15);

-- Insert skills for each category
INSERT INTO skills (name, category_id) VALUES
-- Web Development
('HTML', 1), ('CSS', 1), ('JavaScript', 1), ('React', 1), ('Vue.js', 1), ('Angular', 1),
('Node.js', 1), ('PHP', 1), ('Python', 1), ('Django', 1), ('Laravel', 1), ('WordPress', 1),
-- Mobile Development
('Swift', 2), ('Kotlin', 2), ('React Native', 2), ('Flutter', 2), ('Ionic', 2), ('Xamarin', 2),
-- UI/UX Design
('Figma', 3), ('Adobe XD', 3), ('Sketch', 3), ('Prototyping', 3), ('User Research', 3), ('Wireframing', 3),
-- Graphic Design
('Photoshop', 4), ('Illustrator', 4), ('InDesign', 4), ('Canva', 4), ('Logo Design', 4), ('Branding', 4),
-- Digital Marketing
('SEO', 5), ('Google Ads', 5), ('Facebook Ads', 5), ('Social Media', 5), ('Email Marketing', 5), ('Analytics', 5),
-- Content Writing
('Blog Writing', 6), ('Copywriting', 6), ('Technical Writing', 6), ('SEO Writing', 6), ('Creative Writing', 6),
-- Data Science
('Python', 7), ('R', 7), ('SQL', 7), ('Machine Learning', 7), ('Tableau', 7), ('Power BI', 7),
-- DevOps & Cloud
('AWS', 8), ('Docker', 8), ('Kubernetes', 8), ('Jenkins', 8), ('Linux', 8), ('Azure', 8),
-- Video & Animation
('After Effects', 9), ('Premiere Pro', 9), ('Final Cut Pro', 9), ('Blender', 9), ('Cinema 4D', 9),
-- Photography
('Portrait Photography', 10), ('Product Photography', 10), ('Lightroom', 10), ('Photo Editing', 10),
-- Translation
('English', 11), ('Spanish', 11), ('French', 11), ('German', 11), ('Chinese', 11), ('Arabic', 11),
-- Virtual Assistant
('Data Entry', 12), ('Customer Service', 12), ('Email Management', 12), ('Scheduling', 12),
-- Accounting & Finance
('QuickBooks', 13), ('Excel', 13), ('Financial Analysis', 13), ('Tax Preparation', 13),
-- Legal Services
('Contract Law', 14), ('Corporate Law', 14), ('Legal Research', 14), ('Compliance', 14),
-- Music & Audio
('Audio Editing', 15), ('Music Production', 15), ('Voice Over', 15), ('Sound Design', 15);

-- Insert sample skills
INSERT INTO skills (name, category) VALUES 
('Web Development', 'Technology'),
('Graphic Design', 'Creative'),
('Digital Marketing', 'Marketing'),
('Content Writing', 'Creative'),
('SEO', 'Marketing'),
('Mobile Development', 'Technology'),
('UI/UX Design', 'Creative'),
('Data Analysis', 'Technology'),
('Project Management', 'Business'),
('Consulting', 'Business'),
('Photography', 'Creative'),
('Video Editing', 'Creative'),
('Social Media Management', 'Marketing'),
('E-commerce', 'Business'),
('Legal Services', 'Professional');

-- Insert sample FAQs
INSERT INTO faqs (question, answer, category, sort_order) VALUES 
('How do I verify my account?', 'After registration, you will receive an email with a verification link. Click the link to verify your account.', 'Account', 1),
('How can I become a mentor?', 'Wagers can apply to become mentors through their dashboard. This feature is available for verified professionals.', 'Mentorship', 2),
('How do reviews work?', 'Service seekers can leave reviews for wagers after completing a service. Reviews include ratings and comments.', 'Reviews', 3),
('Is my personal information secure?', 'Yes, we use industry-standard security measures to protect your personal information and never share it with third parties.', 'Security', 4),
('How do I report inappropriate behavior?', 'You can report users through the admin panel or contact support directly. All reports are reviewed promptly.', 'Support', 5);

-- Insert sample contract templates
INSERT INTO contract_templates (title, description, file_path, category) VALUES 
('Service Agreement Template', 'Standard service agreement for professional services', 'templates/service_agreement.pdf', 'Legal'),
('NDA Template', 'Non-disclosure agreement for confidential projects', 'templates/nda_template.pdf', 'Legal'),
('Project Contract', 'Comprehensive project contract template', 'templates/project_contract.pdf', 'Legal');

-- Insert sample resources
INSERT INTO resources (title, description, type, content_url, category) VALUES 
('Getting Started Guide', 'Complete guide for new users', 'document', NULL, 'Tutorials'),
('Best Practices for Wagers', 'Tips for professional service providers', 'article', NULL, 'Professional'),
('Marketing Your Services', 'Effective marketing strategies for freelancers', 'video', 'https://example.com/marketing-video', 'Marketing'),
('Legal Considerations', 'Important legal aspects of freelance work', 'document', NULL, 'Legal');

-- Create uploads directory reference
-- Note: You'll need to create the 'uploads' directory in your project folder
-- mkdir uploads
-- chmod 755 uploads

-- Create triggers to update user rating when reviews are added/updated
DELIMITER //

CREATE TRIGGER update_user_rating_insert
AFTER INSERT ON reviews
FOR EACH ROW
BEGIN
    UPDATE users 
    SET rating = (
        SELECT AVG(rating) 
        FROM reviews 
        WHERE wager_id = NEW.wager_id
    ),
    total_reviews = (
        SELECT COUNT(*) 
        FROM reviews 
        WHERE wager_id = NEW.wager_id
    )
    WHERE id = NEW.wager_id;
END//

CREATE TRIGGER update_user_rating_update
AFTER UPDATE ON reviews
FOR EACH ROW
BEGIN
    UPDATE users 
    SET rating = (
        SELECT AVG(rating) 
        FROM reviews 
        WHERE wager_id = NEW.wager_id
    ),
    total_reviews = (
        SELECT COUNT(*) 
        FROM reviews 
        WHERE wager_id = NEW.wager_id
    )
    WHERE id = NEW.wager_id;
END//

CREATE TRIGGER update_user_rating_delete
AFTER DELETE ON reviews
FOR EACH ROW
BEGIN
    UPDATE users 
    SET rating = (
        SELECT COALESCE(AVG(rating), 0) 
        FROM reviews 
        WHERE wager_id = OLD.wager_id
    ),
    total_reviews = (
        SELECT COUNT(*) 
        FROM reviews 
        WHERE wager_id = OLD.wager_id
    )
    WHERE id = OLD.wager_id;
END//

DELIMITER ;

-- Show success message
SELECT 'Sconnect database setup completed successfully!' AS message;
SELECT 'Default admin credentials: admin@sconnect.com / password' AS admin_info;
SELECT 'Remember to create the uploads directory in your project folder' AS reminder; 