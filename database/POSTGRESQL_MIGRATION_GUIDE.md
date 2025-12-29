# PostgreSQL Migration Guide

## ⚠️ Important: SQL File Needs Fixes

The auto-generated `postgres-export.sql` file has some MySQL-specific syntax that won't work in PostgreSQL. I've created a **fixed version** for you.

## Two Options:

### Option 1: Use Fixed Schema (Recommended)

1. **Use the fixed schema file:** `postgres-export-fixed.sql`
   - This has proper PostgreSQL syntax
   - ENUMs are created correctly
   - Indexes are separate CREATE INDEX statements
   - Triggers handle `updated_at` automatically

2. **Then import your data:**
   - Copy the INSERT statements from `postgres-export.sql`
   - Run them after creating the tables

### Option 2: Manual Fixes Needed

If you want to use the original file, fix these issues:

#### Issues to Fix:

1. **ENUM Types** - Must be created first:
   ```sql
   -- Add at the top:
   CREATE TYPE agent_result_enum AS ENUM ('success', 'failed', 'warning');
   -- ... etc for all ENUMs
   ```

2. **Remove KEY syntax** - PostgreSQL doesn't support KEY in CREATE TABLE:
   ```sql
   -- Remove lines like:
   KEY "idx_project_id" ("project_id")
   
   -- Add separately:
   CREATE INDEX "idx_project_id" ON "table_name" ("project_id");
   ```

3. **Remove COMMENT syntax** - PostgreSQL uses different comment syntax:
   ```sql
   -- Remove: COMMENT 'description'
   -- Add after table creation:
   COMMENT ON COLUMN "table_name"."column_name" IS 'description';
   ```

4. **Fix AUTO_INCREMENT** - Should be SERIAL:
   ```sql
   -- Change: "id" INTEGER NOT NULL AUTO_INCREMENT
   -- To: "id" SERIAL PRIMARY KEY
   ```

5. **Remove ON UPDATE CURRENT_TIMESTAMP** - PostgreSQL doesn't support this:
   ```sql
   -- Remove: ON UPDATE CURRENT_TIMESTAMP
   -- Use triggers instead (see fixed file)
   ```

6. **Fix tinyINTEGER** - Should be SMALLINT:
   ```sql
   -- Change: tinyINTEGER
   -- To: SMALLINT
   ```

7. **Remove COLLATE** - PostgreSQL has different collation:
   ```sql
   -- Remove: COLLATE=utf8mb4_general_ci
   ```

8. **Fix JSON validation** - PostgreSQL uses JSONB:
   ```sql
   -- Change: longTEXT CHECK (json_valid("event_data"))
   -- To: JSONB
   ```

## Quick Migration Steps:

### Step 1: Create Tables
```sql
-- Run postgres-export-fixed.sql in Render PostgreSQL
-- This creates all tables with proper syntax
```

### Step 2: Import Data
```sql
-- Copy INSERT statements from postgres-export.sql
-- Run them in Render PostgreSQL
-- Example:
INSERT INTO "users" ("id", "email", "password_hash", "name", "created_at", "updated_at") 
VALUES (1, 'test@example.com', '$2y$10$...', 'Test User', '2025-12-28 23:52:02', '2025-12-28 23:52:02');
```

### Step 3: Verify
```sql
-- Check table counts
SELECT COUNT(*) FROM users;
SELECT COUNT(*) FROM projects;
-- etc.
```

## Recommended Approach:

1. **Use `postgres-export-fixed.sql`** for schema
2. **Extract INSERT statements** from `postgres-export.sql` for data
3. **Run both** in Render PostgreSQL

This ensures compatibility and preserves all your data!

