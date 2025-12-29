# Deployment Guide

## Quick Start

### Prerequisites
- Docker and Docker Compose installed
- Git installed
- MySQL client (for backups)

### 1. Clone Repository
```bash
git clone <repository-url> cursoft
cd cursoft
```

### 2. Configure Environment
```bash
cp docker/.env.production.example docker/.env.production
# Edit docker/.env.production with your values
```

### 3. Start Services
```bash
cd docker
docker-compose -f docker-compose.prod.yml up -d
```

### 4. Initialize Database
```bash
# Access MySQL container
docker exec -it cursoft-db mysql -u root -p

# Run schema files
source /docker-entrypoint-initdb.d/schema.sql
source /docker-entrypoint-initdb.d/schema_phase2.sql
source /docker-entrypoint-initdb.d/schema_phase3.sql
source /docker-entrypoint-initdb.d/schema_phase4.sql
```

### 5. Verify Deployment
```bash
# Health check
curl http://localhost/api/health.php

# Access application
open http://localhost
```

## Manual Deployment (Without Docker)

### 1. Copy Files
Copy all files to your web server directory (e.g., `/var/www/html/cursoft`)

### 2. Set Permissions
```bash
chown -R www-data:www-data /var/www/html/cursoft
chmod -R 755 /var/www/html/cursoft
chmod -R 777 /var/www/html/cursoft/logs
chmod -R 777 /var/www/html/cursoft/workspaces
```

### 3. Configure Database
Update `includes/Database.php` with production credentials

### 4. Run Database Schema
Execute all schema files in phpMyAdmin or via command line

### 5. Configure Apache
Ensure mod_rewrite is enabled and `.htaccess` is being read

## Automated Deployment

### Using Deployment Script
```bash
./scripts/deploy.sh production
```

### Using GitHub Actions
1. Set up secrets in GitHub:
   - `SSH_PRIVATE_KEY`
   - `SSH_USER`
   - `SSH_HOST`
   - `DOMAIN`

2. Push to main branch - deployment will trigger automatically

## Post-Deployment

1. Create admin user (if needed)
2. Configure LLM API keys
3. Set up SSL certificate (for HTTPS)
4. Configure monitoring (Prometheus/Grafana)
5. Set up automated backups

## Troubleshooting

See `docs/TROUBLESHOOTING.md` for common issues and solutions.

