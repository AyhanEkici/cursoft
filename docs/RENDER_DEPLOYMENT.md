# Render.com Deployment Guide for Cursoft

## 🚀 Complete Step-by-Step Deployment

This guide will walk you through deploying Cursoft to Render.com (free tier).

## Prerequisites

- ✅ Cursoft project complete (all 5 phases)
- ✅ Git installed
- ✅ GitHub account (free)
- ✅ Render.com account (free)

## Phase 1: Prepare Your Project

### Step 1.1: Run Database Converter

1. **Open in browser:**
   ```
   http://localhost/cursoft/database/convert_to_postgresql.php
   ```

2. **Enter your MySQL credentials:**
   - Database: `cursoft`
   - Host: `localhost`
   - Username: `root`
   - Password: (your XAMPP MySQL password, usually empty)

3. **Click "Generate PostgreSQL Migration"**

4. **Download the generated file:** `postgres-export.sql`
   - Save this file - you'll need it later!

### Step 1.2: Test Locally

Make sure everything works:
```bash
# Test health endpoint
curl http://localhost/cursoft/api/health.php

# Test login
# Open: http://localhost/cursoft/pages/login.php
```

## Phase 2: Git Setup

### Step 2.1: Initialize Git (if not already)

```bash
cd E:\xampp\htdocs\cursoft
git init
```

### Step 2.2: Create .gitignore

Already created! Check `.gitignore` exists.

### Step 2.3: Commit Files

```bash
git add .
git commit -m "Prepare for Render deployment"
```

### Step 2.4: Push to GitHub

1. **Create new repository on GitHub:**
   - Go to: https://github.com/new
   - Name: `cursoft` (or your choice)
   - Make it **Private** (recommended)
   - Click "Create repository"

2. **Connect and push:**
   ```bash
   git remote add origin https://github.com/YOUR_USERNAME/cursoft.git
   git branch -M main
   git push -u origin main
   ```

## Phase 3: Render Setup

### Step 3.1: Create Render Account

1. Go to: https://render.com
2. Sign up with GitHub (recommended)
3. Authorize Render to access your repositories

### Step 3.2: Create Web Service

1. **In Render Dashboard, click "New +" → "Web Service"**

2. **Connect Repository:**
   - Select your `cursoft` repository
   - Click "Connect"

3. **Configure Service:**
   - **Name:** `cursoft-app`
   - **Region:** Choose closest to you
   - **Branch:** `main`
   - **Runtime:** `PHP`
   - **Build Command:** `./render-build.sh`
   - **Start Command:** `php -S 0.0.0.0:$PORT -t public`
   - **Health Check Path:** `/api/health.php`

4. **Environment Variables:**
   - `APP_ENV` = `production`
   - `RENDER` = `true`
   - `BASE_PATH` = (leave empty)

5. **Click "Create Web Service"**

### Step 3.3: Create Database

1. **In Render Dashboard, click "New +" → "PostgreSQL"**

2. **Configure Database:**
   - **Name:** `cursoft-db`
   - **Database Name:** `cursoft_prod`
   - **User:** `cursoft_user`
   - **Plan:** `Free` (or paid if you prefer)

3. **Click "Create Database"**

4. **Link Database to Web Service:**
   - Go to your web service settings
   - Under "Environment", find "Add Environment Variable"
   - Add: `DATABASE_URL` → Select from `cursoft-db` → `Connection String`
   - Save changes

## Phase 4: First Deployment

### Step 4.1: Trigger Deployment

Render will automatically deploy when you:
- Push to GitHub, OR
- Click "Manual Deploy" in Render dashboard

### Step 4.2: Monitor Build

1. Watch the build logs in Render dashboard
2. Wait for "Your service is live" message
3. Note your service URL: `https://cursoft-app.onrender.com`

### Step 4.3: Import Database

1. **Get PostgreSQL Connection Info:**
   - Go to your database in Render
   - Click "Connect"
   - Copy the "External Connection String"

2. **Import SQL:**
   - Use a PostgreSQL client (pgAdmin, DBeaver, or command line)
   - Connect using the connection string
   - Run the `postgres-export.sql` file you created earlier

   **OR use Render's built-in SQL editor:**
   - Go to database → "Connect" → "psql"
   - Copy/paste your SQL file contents

## Phase 5: Test Live Site

### Step 5.1: Health Check

```bash
curl https://your-app.onrender.com/api/health.php
```

Should return:
```json
{
    "status": "healthy",
    "service": "cursoft",
    ...
}
```

### Step 5.2: Test Application

1. **Visit your site:** `https://your-app.onrender.com`
2. **Create account:** Sign up page
3. **Login:** Test authentication
4. **Create project:** Test project creation
5. **Check dashboard:** Verify everything works

## Phase 6: Post-Deployment

### Step 6.1: Configure LLM Keys

1. Login to your live site
2. Go to LLM Configuration page
3. Add your API keys (OpenAI, etc.)

### Step 6.2: Set Up Monitoring

- Render provides basic monitoring
- Check logs in Render dashboard
- Set up alerts if needed

### Step 6.3: Custom Domain (Optional)

1. In Render dashboard → Your service → Settings
2. Add custom domain
3. Follow DNS configuration instructions

## Troubleshooting

### Build Fails

**Error:** "Build command failed"
- Check `render-build.sh` has execute permissions
- Verify all paths in build script are correct
- Check build logs for specific errors

**Error:** "Start command failed"
- Verify PHP is available
- Check `public/` directory exists after build
- Review start command syntax

### Database Connection Fails

**Error:** "Database connection failed"
- Verify `DATABASE_URL` environment variable is set
- Check database is running in Render
- Verify connection string format
- Check `includes/Database.php` handles PostgreSQL correctly

### Path Issues

**Error:** "404 Not Found" or broken links
- Verify `PathHelper.php` is working
- Check all hardcoded `/cursoft/` paths are updated
- Review `.htaccess` in public directory

### Performance Issues

**Slow loading:**
- Render free tier spins down after inactivity
- First request after spin-down takes ~30 seconds
- Consider paid tier for always-on service

## Important Notes

### Free Tier Limitations

- ⚠️ **Spins down after 15 minutes of inactivity**
- ⚠️ **First request after spin-down is slow (~30s)**
- ⚠️ **Limited resources** (512MB RAM)
- ⚠️ **PostgreSQL free tier:** 90 days retention

### Production Recommendations

1. **Upgrade to paid tier** for:
   - Always-on service
   - Better performance
   - More resources

2. **Set up backups:**
   - Regular database backups
   - Export data periodically

3. **Monitor costs:**
   - Track usage
   - Set up alerts

## Next Steps

After successful deployment:

1. ✅ Test all features
2. ✅ Configure LLM API keys
3. ✅ Set up monitoring
4. ✅ Document your deployment
5. ✅ Share your live URL!

## Support

- **Render Docs:** https://render.com/docs
- **Render Support:** support@render.com
- **Cursoft Issues:** Check `docs/TROUBLESHOOTING.md`

---

**Congratulations! Your Cursoft app is now live on Render! 🎉**

