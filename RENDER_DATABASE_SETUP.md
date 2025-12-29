# 🗄️ Render PostgreSQL Database Setup

## Issue
The `rate_limits` table (and other security tables) don't exist in your Render PostgreSQL database.

## Solution: Run Security Schema

You need to run the PostgreSQL security schema on your Render database.

### Option 1: Using Render Dashboard (Easiest)

1. **Go to Render Dashboard**
   - Navigate to: https://dashboard.render.com
   - Click on your database: `cursoft-db`

2. **Open Database Shell**
   - Click on **"Connect"** or **"Shell"** tab
   - This opens a PostgreSQL shell

3. **Run the Schema**
   - Copy the contents of `database/schema_security_postgresql.sql`
   - Paste into the shell and press Enter
   - Or use `\i` command if you can upload the file

### Option 2: Using psql Command Line

If you have `psql` installed locally:

```bash
# Get connection string from Render dashboard
# Format: postgresql://user:password@host:port/database

psql "postgresql://cursoft_user:YOUR_PASSWORD@YOUR_HOST:5432/cursoft_prod" -f database/schema_security_postgresql.sql
```

### Option 3: Using pgAdmin or DBeaver

1. Connect to your Render PostgreSQL database using the connection string from Render dashboard
2. Open SQL query window
3. Copy and paste contents of `database/schema_security_postgresql.sql`
4. Execute the query

## What This Creates

The schema creates these tables:
- ✅ `rate_limits` - For rate limiting (prevents spam/abuse)
- ✅ `security_logs` - For security event logging
- ✅ `password_reset_tokens` - For password reset functionality
- ✅ `api_tokens` - For API authentication (future use)

## Verify Tables Were Created

After running the schema, verify with:

```sql
-- List all tables
\dt

-- Or check specific table
SELECT * FROM rate_limits LIMIT 1;
```

## Quick Fix Script

If you want to create just the `rate_limits` table quickly:

```sql
CREATE TABLE IF NOT EXISTS rate_limits (
    id SERIAL PRIMARY KEY,
    identifier VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    request_count INTEGER DEFAULT 1,
    reset_time INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unique_identifier_ip UNIQUE (identifier, ip_address)
);

CREATE INDEX IF NOT EXISTS idx_rate_limits_identifier_ip ON rate_limits(identifier, ip_address);
CREATE INDEX IF NOT EXISTS idx_rate_limits_reset_time ON rate_limits(reset_time);
```

## After Running Schema

Once the tables are created:
1. ✅ Signup page will work
2. ✅ Rate limiting will function
3. ✅ Security logging will work
4. ✅ Password reset will work (when implemented)

---

**File Location**: `database/schema_security_postgresql.sql`

