# Database Setup Guide

## Setup Order

Run these SQL files in phpMyAdmin **in this exact order**:

### 1. Phase 1: Base Schema
**File:** `schema.sql`
- Creates the `cursoft` database
- Creates base tables: `users`, `projects`, `project_plans`, `project_logs`, `llm_configs`
- Inserts a test user

### 2. Phase 2: Container Manager
**File:** `schema_phase2.sql`
- Adds `containers` table
- Adds `container_logs` table
- **IMPORTANT:** Run this AFTER `schema.sql`

### 3. Phase 3: LLM & Pipeline
**File:** `schema_phase3.sql`
- Adds `llm_requests` table
- Adds `pipeline_stages` table
- Adds `agent_actions` table
- **IMPORTANT:** Run this AFTER `schema_phase2.sql`

## Quick Setup (All at Once)

If you want to run everything at once, you can copy all three files in order:

1. Open `schema.sql` - Copy and paste into phpMyAdmin SQL tab
2. Open `schema_phase2.sql` - Copy and paste into phpMyAdmin SQL tab
3. Open `schema_phase3.sql` - Copy and paste into phpMyAdmin SQL tab

## Verification

After running all schemas, you should have these tables:

✅ **Phase 1:**
- users
- projects
- project_plans
- project_logs
- llm_configs

✅ **Phase 2:**
- containers
- container_logs

✅ **Phase 3:**
- llm_requests
- pipeline_stages
- agent_actions

**Total: 10 tables**

## Troubleshooting

### Error: "Table already exists"
- This is OK! The schemas use `CREATE TABLE IF NOT EXISTS`
- You can safely re-run them

### Error: "Foreign key constraint fails"
- Make sure you ran `schema.sql` FIRST
- The foreign keys depend on the base tables

### Error: "Database doesn't exist"
- Run `schema.sql` first - it creates the database

