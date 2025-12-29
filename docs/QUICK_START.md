# Quick Start Guide - Test Everything

## 🚀 5-Minute Test Run

### Step 1: Verify Setup
```bash
# Test health endpoint
curl http://localhost/cursoft/api/health.php

# Or use the test script
scripts\test_endpoints.bat
```

### Step 2: Create Account
1. Open: `http://localhost/cursoft/pages/signup.php`
2. Fill in:
   - Name: Test User
   - Email: test@example.com
   - Password: test123456
3. Click "Sign Up"

### Step 3: Configure LLM
1. Go to: `http://localhost/cursoft/pages/llm_config.php`
2. Add your OpenAI API key (or other provider)
3. Save configuration

### Step 4: Create Your First Project
1. Go to: `http://localhost/cursoft/pages/new_project.php`
2. Enter:
   - Project Name: "My First AI Project"
   - Prompt: "Create a simple calculator web app with HTML, CSS, and JavaScript"
3. Click "Create Project"

### Step 5: Start Pipeline
1. View project: `http://localhost/cursoft/pages/project_detail.php?id=1`
2. Click "Start Pipeline"
3. Watch real-time progress!

### Step 6: Explore
- View dashboard: `http://localhost/cursoft/pages/dashboard.php`
- Check metrics: `http://localhost/cursoft/api/metrics.php`
- Test API: Use `public/js/api.js` as reference

## ✅ Success Checklist

- [ ] Health endpoint returns "healthy"
- [ ] Can create user account
- [ ] Can login
- [ ] Can configure LLM keys
- [ ] Can create project
- [ ] Can view project details
- [ ] Pipeline can start (if LLM configured)
- [ ] Metrics endpoint shows data

## 🐛 Troubleshooting

**Can't access pages?**
- Check Apache is running in XAMPP
- Verify file paths are correct
- Check `.htaccess` is working

**Database errors?**
- Verify MySQL is running
- Check database credentials in `includes/Database.php`
- Run schema files in phpMyAdmin

**LLM errors?**
- Verify API keys are valid (not test keys)
- Check API key has credits/quota
- Review error messages in browser console

## 📖 Next Steps

See `docs/WHAT_NEXT.md` for:
- Enhancement ideas
- Production deployment
- Feature roadmap
- Learning resources

