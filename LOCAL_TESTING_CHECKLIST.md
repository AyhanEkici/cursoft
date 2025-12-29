# ✅ Local Testing Checklist - Before Pushing to GitHub

## 🎯 Goal
Test everything locally on XAMPP first, then push all files at once with GitHub Desktop.

## 📋 Pre-Push Checklist

### 1. Database Setup (Local XAMPP)
- [ ] MySQL is running in XAMPP
- [ ] Database `cursoft` exists
- [ ] All tables created (run `database/schema.sql` in phpMyAdmin)
- [ ] Test user can login: `test@example.com` / `password`

### 2. File Structure Check
- [ ] All PHP files have no syntax errors
- [ ] No trailing `?>` tags with whitespace
- [ ] All includes/requires use correct paths
- [ ] `public/health.php` exists and works

### 3. Test Core Functionality
- [ ] **Health Check**: `http://localhost/cursoft/public/health.php` returns JSON
- [ ] **Login Page**: `http://localhost/cursoft/pages/login.php` loads without errors
- [ ] **Signup Page**: `http://localhost/cursoft/pages/signup.php` loads without errors
- [ ] **Can create account**: Signup form works
- [ ] **Can login**: Login form works
- [ ] **No PHP warnings**: Check browser console for errors

### 4. Database Connection
- [ ] Database connects successfully
- [ ] Can query users table
- [ ] Can insert new users
- [ ] No "table does not exist" errors

### 5. Security Features
- [ ] CSRF tokens generate correctly
- [ ] Rate limiting works (if tables exist)
- [ ] No "headers already sent" errors
- [ ] Sessions work correctly

### 6. File Integrity
- [ ] All required files exist:
  - `index.php`
  - `includes/Database.php`
  - `includes/Auth.php`
  - `includes/Security.php`
  - `includes/SessionManager.php`
  - `includes/PathHelper.php`
  - `pages/login.php`
  - `pages/signup.php`
  - `public/health.php`
  - `render-build.sh`
  - `docker/Dockerfile.php`
  - `render.yaml`

### 7. Git Status
- [ ] All changes are committed locally
- [ ] No uncommitted files
- [ ] Ready to push

## 🧪 Quick Local Test Script

Run these URLs in your browser:

1. **Health Check**: http://localhost/cursoft/public/health.php
   - Should return: `{"status":"healthy",...}`

2. **Login Page**: http://localhost/cursoft/pages/login.php
   - Should show login form (no PHP errors)

3. **Signup Page**: http://localhost/cursoft/pages/signup.php
   - Should show signup form (no PHP errors)

4. **Test Signup**: Create a test account
   - Should redirect to login page

5. **Test Login**: Login with test account
   - Should redirect to dashboard

## 🚨 Common Issues to Check

### PHP Errors
- Open browser console (F12)
- Check for PHP warnings/errors
- Fix any "headers already sent" errors
- Fix any "undefined variable" errors

### Database Errors
- Check `includes/Database.php` connection settings
- Verify MySQL is running
- Check database credentials

### Path Issues
- Verify `PathHelper.php` works correctly
- Check all `require_once` paths
- Test on both root and subdirectory

## ✅ When Everything Works Locally

1. **Commit all changes** (if not already done)
2. **Open GitHub Desktop**
3. **Push to GitHub**
4. **Render will auto-deploy**
5. **Run database schema on Render** (PostgreSQL version)

---

**Next Step**: Test locally, then we'll push everything together!

