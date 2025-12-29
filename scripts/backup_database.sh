#!/bin/bash
# Database Backup Script
# Usage: ./scripts/backup_database.sh

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
BACKUP_DIR="$PROJECT_DIR/backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/cursoft_backup_$TIMESTAMP.sql"

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Database credentials (adjust as needed)
DB_HOST=${DB_HOST:-localhost}
DB_NAME=${DB_NAME:-cursoft}
DB_USER=${DB_USER:-root}
DB_PASS=${DB_PASS:-}

echo "📦 Creating database backup..."
echo "Database: $DB_NAME"
echo "Backup file: $BACKUP_FILE"

# Create backup
if [ -z "$DB_PASS" ]; then
    mysqldump -h "$DB_HOST" -u "$DB_USER" "$DB_NAME" > "$BACKUP_FILE"
else
    mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_FILE"
fi

# Compress backup
gzip "$BACKUP_FILE"
BACKUP_FILE="${BACKUP_FILE}.gz"

echo "✅ Backup created: $BACKUP_FILE"

# Keep only last 7 backups
echo "🧹 Cleaning old backups (keeping last 7)..."
cd "$BACKUP_DIR"
ls -t cursoft_backup_*.sql.gz | tail -n +8 | xargs rm -f 2>/dev/null || true

echo "✅ Backup completed!"

