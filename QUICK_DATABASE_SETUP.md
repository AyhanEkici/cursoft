# 🚀 Quick Database Setup for Render

## ⚠️ CRITICAL: Your database is empty!

The error `relation "users" does not exist` means your Render PostgreSQL database has no tables yet.

## ✅ Quick Fix (5 minutes)

### Step 1: Go to Render Dashboard
1. Navigate to: https://dashboard.render.com
2. Click on your database: **`cursoft-db`**
3. Click **"Connect"** or find the **"Shell"** tab

### Step 2: Run Minimal Schema

Copy and paste this entire SQL into the Render database shell:

```sql
-- Users table (REQUIRED)
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);

-- User preferences table
CREATE TABLE IF NOT EXISTS user_preferences (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_preferences_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_user_preferences_user_id ON user_preferences(user_id);
```

### Step 3: Press Enter

The tables will be created immediately.

### Step 4: Test Signup

Go back to: **https://cursoft-app.onrender.com/pages/signup.php**

You should now be able to create an account! ✅

## 📋 Full Schema (Optional)

If you want all tables (projects, logs, etc.), use:
- **File**: `database/schema_postgresql_minimal.sql`
- Copy the entire file contents and run in Render database shell

## 🔍 Verify Tables Were Created

After running the SQL, verify with:

```sql
-- List all tables
\dt

-- Check users table exists
SELECT * FROM users LIMIT 1;
```

## ⚡ What This Creates

**Essential Tables:**
- ✅ `users` - For user accounts (REQUIRED)
- ✅ `user_preferences` - For user settings

**Optional Tables (in full schema):**
- `projects` - For AI projects
- `project_plans` - For project tasks
- `llm_configs` - For LLM API keys
- `project_logs` - For project activity logs

## 🎯 After Setup

Once `users` table exists:
1. ✅ Signup page will work
2. ✅ Login page will work
3. ✅ You can create accounts
4. ✅ You can login

---

**File Location**: `database/schema_postgresql_minimal.sql`

