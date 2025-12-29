#!/bin/bash
# Deployment Script for Cursoft
# Usage: ./scripts/deploy.sh [environment]

set -e

ENVIRONMENT=${1:-production}
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"

echo "🚀 Starting deployment for environment: $ENVIRONMENT"
echo "Project directory: $PROJECT_DIR"

# Load environment variables
if [ -f "$PROJECT_DIR/.env.$ENVIRONMENT" ]; then
    source "$PROJECT_DIR/.env.$ENVIRONMENT"
    echo "✅ Loaded environment variables"
else
    echo "⚠️  Warning: .env.$ENVIRONMENT not found"
fi

# Backup database before deployment
echo "📦 Creating database backup..."
"$SCRIPT_DIR/backup_database.sh"

# Pull latest code (if using Git)
if [ -d "$PROJECT_DIR/.git" ]; then
    echo "📥 Pulling latest code..."
    cd "$PROJECT_DIR"
    git pull origin main || git pull origin master
fi

# Run database migrations (if any)
echo "🔄 Running database migrations..."
# Add migration commands here if needed

# Set permissions
echo "🔐 Setting file permissions..."
chmod -R 755 "$PROJECT_DIR"
chmod -R 777 "$PROJECT_DIR/logs" 2>/dev/null || true
chmod -R 777 "$PROJECT_DIR/workspaces" 2>/dev/null || true

# Clear caches
echo "🧹 Clearing caches..."
rm -rf "$PROJECT_DIR/tmp/*" 2>/dev/null || true

# Restart services (if using Docker)
if command -v docker-compose &> /dev/null; then
    echo "🐳 Restarting Docker containers..."
    cd "$PROJECT_DIR"
    docker-compose -f docker/docker-compose.prod.yml restart app
fi

# Health check
echo "🏥 Running health check..."
sleep 5
if curl -f http://localhost/cursoft/api/health.php > /dev/null 2>&1; then
    echo "✅ Health check passed"
else
    echo "❌ Health check failed - deployment may have issues"
    exit 1
fi

echo "✅ Deployment completed successfully!"
echo "📊 Check logs: $PROJECT_DIR/logs/"

