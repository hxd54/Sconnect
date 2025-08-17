# Sconnect Job Marketplace - Setup Guide

## 🚀 Quick Setup Instructions

### Step 1: Database Setup
1. **Start XAMPP** - Make sure Apache and MySQL are running
2. **Open your browser** and go to: `http://localhost/Sconnect/setup_database.php`
3. **Click "Setup Database Now"** to automatically create all tables
4. **Wait for confirmation** that tables were created successfully

### Step 2: Access the Platform
1. **Go to**: `http://localhost/Sconnect/`
2. **Register** as either a Job Seeker or Job Provider
3. **Login** and explore the platform

## 🔧 Manual Database Setup (if automatic setup fails)

### Option 1: Using phpMyAdmin
1. Open `http://localhost/phpmyadmin`
2. Create database `sconnect` if it doesn't exist
3. Select the `sconnect` database
4. Go to **SQL** tab
5. Copy and paste the entire contents of `complete_database_setup.sql`
6. Click **Go** to execute

### Option 2: Using MySQL Command Line
```bash
mysql -u root -p
CREATE DATABASE sconnect;
USE sconnect;
SOURCE C:/xampp/htdocs/Sconnect/complete_database_setup.sql;
```

## 📋 Required Tables
The system needs these tables to function:
- ✅ `job_categories` - Industry-based job categories (13 major industries)
- ✅ `skills` - Available skills (78 professional skills)
- ✅ `skill_categories` - Maps skills to categories (many-to-many)
- ✅ `users` - User accounts (job seekers & providers)
- ✅ `email_tokens` - Email verification and password reset
- ✅ `user_job_categories` - User's selected job categories
- ✅ `user_skills` - User's skills
- ✅ `job_postings` - Job opportunities
- ✅ `portfolio` - User posts and achievements
- ✅ `conversations` - Message threads
- ✅ `messages` - Individual messages
- ✅ `post_likes` - Like system
- ✅ `reviews` - Rating system
- ✅ `notifications` - User notifications
- ✅ `mentorship_requests` - Student-mentor connections

## 🌐 Access URLs

### Main Pages
- **Homepage**: `http://localhost/Sconnect/`
- **Database Setup**: `http://localhost/Sconnect/setup_database.php`
- **Database Reset**: `http://localhost/Sconnect/reset_database.php` (⚠️ Deletes all data!)
- **Registration**: `http://localhost/Sconnect/signup.php`
- **Login**: `http://localhost/Sconnect/login.php`

### Debug & Testing Tools
- **Debug Users**: `http://localhost/Sconnect/debug_users.php` (Shows all users in database)
- **Test Registration**: `http://localhost/Sconnect/test_registration.php` (Quick user creation)
- **Test Job Seeker Form**: `http://localhost/Sconnect/test_job_seeker_form.php` (Test registration form)

### Job Seeker Pages
- **Dashboard**: `http://localhost/Sconnect/dashboard_job_seeker.php`
- **Browse Jobs**: `http://localhost/Sconnect/browse_jobs.php`

### Job Provider Pages
- **Dashboard**: `http://localhost/Sconnect/dashboard_job_provider.php`
- **Search Talent**: `http://localhost/Sconnect/search_talent.php`

### Messaging
- **Chat**: `http://localhost/Sconnect/chat.php?to=USER_ID`

## 🛠️ Troubleshooting Common Issues

### Error: "Table doesn't exist"
**Solution**: Run the database setup script
1. Go to `http://localhost/Sconnect/setup_database.php`
2. Click "Setup Database Now"

### Error: "Database connection failed"
**Solutions**:
1. Check if MySQL is running in XAMPP
2. Verify database credentials in `inc/db.php`
3. Make sure `sconnect` database exists

### Error: "Permission denied" for file uploads
**Solution**: Create upload directories
```bash
mkdir uploads
mkdir uploads/chat
chmod 755 uploads
chmod 755 uploads/chat
```

### Error: "Call to undefined function"
**Solution**: Check PHP extensions
- Make sure `php_pdo_mysql` extension is enabled
- Restart Apache after enabling extensions

## 🎯 Testing the System

### 1. Test Registration
1. Go to signup page
2. Try registering as both Job Seeker and Job Provider
3. Verify skills selection works for Job Seekers

### 2. Test Dashboards
1. Login as Job Seeker - check dashboard loads
2. Login as Job Provider - check dashboard loads
3. Test posting jobs and posts

### 3. Test Messaging
1. Create two accounts
2. Try sending messages between them
3. Test file attachments

### 4. Test Search
1. As Job Provider, search for talent
2. As Job Seeker, browse jobs
3. Test filtering options

## 📁 File Structure
```
Sconnect/
├── complete_database_setup.sql    # Complete database setup
├── setup_database.php            # Database setup helper
├── index.php                     # Homepage
├── signup.php                    # Registration
├── login.php                     # Login
├── dashboard_job_seeker.php      # Job seeker dashboard
├── dashboard_job_provider.php    # Job provider dashboard
├── browse_jobs.php               # Job browsing
├── search_talent.php             # Talent search
├── chat.php                      # Messaging
├── get_skills.php                # AJAX skills endpoint
├── inc/
│   ├── db.php                    # Database connection
│   ├── auth.php                  # Authentication
│   └── components.php            # Reusable components
└── uploads/                      # File uploads directory
    └── chat/                     # Chat attachments
```

## 🔐 Default Admin Account
- **Email**: admin@sconnect.com
- **Password**: password
- **Phone**: +1234567890

## 📊 Sample Data
The setup script includes:
- **15 job categories** (Web Dev, Mobile Dev, UI/UX, etc.)
- **80+ skills** organized by category
- **1 admin user** for testing

## 🆘 Getting Help
If you encounter issues:
1. Check the database setup page: `setup_database.php`
2. Verify XAMPP is running properly
3. Check PHP error logs in XAMPP
4. Ensure all files are in the correct directory

## 🎉 Success Indicators
You'll know everything is working when:
- ✅ Database setup page shows all tables exist
- ✅ Registration page loads with job categories
- ✅ Dashboards load without errors
- ✅ You can create posts and jobs
- ✅ Messaging system works
- ✅ Search functionality works

The platform is now ready for use as a complete job marketplace!
