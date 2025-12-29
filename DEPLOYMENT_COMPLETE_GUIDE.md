# 🚀 Complete Deployment Guide - Cursoft to Render

## ✅ PRE-DEPLOYMENT: ALL COMPLETE

- ✅ Local tests passed
- ✅ Database exported (16 tables)
- ✅ PostgreSQL schema fixed
- ✅ Data export ready
- ✅ All files prepared

## 📋 DEPLOYMENT STEPS

### STEP 1: Git Setup & Push

```bash
# Navigate to project
cd E:\xampp\htdocs\cursoft

# Initialize Git (if not already)
git init

# Add all files
git add .

# Commit
git commit -m "Ready for Render deployment - All phases complete"

# Create GitHub repository first:
# 1. Go to: https://github.com/new
# 2. Repository name: cursoft
# 3. Make it Private (recommended)
# 4. Don't initialize with README
# 5. Click "Create repository"

# Connect and push
git remote add origin https://github.com/YOUR_USERNAME/cursoft.git
git branch -M main
git push -u origin main
```

### STEP 2: Create Render Account

1. **Sign up:** https://render.com
2. **Connect GitHub:** Authorize Render to access your repositories
3. **Verify:** You should see your repositories in Render dashboard

### STEP 3: Create PostgreSQL Database

1. **In Render Dashboard:**
   - Click "New +" → "PostgreSQL"

2. **Configure:**
   - **Name:** `cursoft-db`
   - **Database Name:** `cursoft_prod`
   - **User:** `cursoft_user`
   - **Plan:** `Free` (or paid if preferred)
   - **Region:** Choose closest to you

3. **Click "Create Database"**

4. **Save Connection Info:**
   - Note the "Internal Database URL" (you'll need this)
   - Or use "External Connection String" for local tools

### STEP 4: Create Web Service

1. **In Render Dashboard:**
   - Click "New +" → "Web Service"

2. **Connect Repository:**
   - Select your `cursoft` repository
   - Click "Connect"

3. **Configure Service:**
   - **Name:** `cursoft-app`
   - **Region:** Same as database
   - **Branch:** `main`
   - **Runtime:** `PHP`
   - **Build Command:** `./render-build.sh`
   - **Start Command:** `php -S 0.0.0.0:$PORT -t public`
   - **Health Check Path:** `/api/health.php`

4. **Environment Variables:**
   - Click "Add Environment Variable"
   - **Key:** `DATABASE_URL`
   - **Value:** Select from `cursoft-db` → `Connection String`
   - **Key:** `APP_ENV` → **Value:** `production`
   - **Key:** `RENDER` → **Value:** `true`
   - **Key:** `BASE_PATH` → **Value:** (leave empty)

5. **Click "Create Web Service"**

### STEP 5: Import Database

**Option A: Using Render's Built-in SQL Editor**

1. Go to your database in Render
2. Click "Connect" → "psql" (opens SQL editor)
3. Copy contents of `database/postgres-export-fixed.sql`
4. Paste and run (creates tables)
5. Copy contents of `database/postgres-data-only.sql`
6. Paste and run (imports data)

**Option B: Using External Tool**

1. Get "External Connection String" from Render database
2. Use pgAdmin, DBeaver, or command line:
   ```bash
   psql "your-connection-string" < database/postgres-export-fixed.sql
   psql "your-connection-string" < database/postgres-data-only.sql
   ```

### STEP 6: Deploy & Test

1. **Trigger Deployment:**
   - Render auto-deploys on push, OR
   - Click "Manual Deploy" in Render dashboard

2. **Monitor Build:**
   - Watch build logs
   - Wait for "Your service is live" message
   - Note your URL: `https://cursoft-app.onrender.com`

3. **Test Health Endpoint:**
   ```bash
   curl https://cursoft-app.onrender.com/api/health.php
   ```

4. **Test Application:**
   - Visit: `https://cursoft-app.onrender.com`
   - Should redirect to login
   - Test login with existing users:
     - `test@example.com` / `password`
     - `ayhan@ayhan.nl` / (your password)

### STEP 7: Post-Deployment

1. **Configure LLM Keys:**
   - Login to live site
   - Go to LLM Configuration
   - Add your API keys

2. **Test Features:**
   - Create new project
   - View dashboard
   - Test API endpoints

3. **Set Up Monitoring:**
   - Check Render logs
   - Monitor health endpoint
   - Set up alerts (if needed)

## 📁 IMPORTANT FILES

### For Database Migration:
- `database/postgres-export-fixed.sql` - Schema (run first)
- `database/postgres-data-only.sql` - Data (run second)
- `database/POSTGRESQL_MIGRATION_GUIDE.md` - Migration guide

### For Deployment:
- `render.yaml` - Render configuration
- `render-build.sh` - Build script
- `docs/RENDER_DEPLOYMENT.md` - Detailed guide

## ⚠️ IMPORTANT NOTES

### Free Tier Limitations:
- **Spins down after 15 min inactivity** - First request takes ~30 seconds
- **Limited resources** - 512MB RAM
- **Database:** 90 days retention on free tier

### Production Recommendations:
1. Upgrade to paid tier for always-on service
2. Set up regular database backups
3. Monitor usage and costs
4. Consider custom domain

## 🐛 TROUBLESHOOTING

### Build Fails:
- Check build logs in Render
- Verify `render-build.sh` has execute permissions
- Check file paths in build script

### Database Connection Fails:
- Verify `DATABASE_URL` environment variable
- Check database is running
- Verify connection string format

### 404 Errors:
- Check `PathHelper` is working
- Verify `.htaccess` in public directory
- Check base path configuration

## ✅ SUCCESS CHECKLIST

- [ ] Code pushed to GitHub
- [ ] Render account created
- [ ] PostgreSQL database created
- [ ] Web service created
- [ ] Database imported successfully
- [ ] Health endpoint working
- [ ] Application accessible
- [ ] Login working
- [ ] All features tested

## 🎉 CONGRATULATIONS!

Once all steps are complete, your Cursoft application will be live on Render!

**Your live URL:** `https://cursoft-app.onrender.com`

---

**Need Help?** Check `docs/RENDER_DEPLOYMENT.md` for detailed instructions.

