#!/bin/bash
# Log Cleanup Script
# Removes old log files to prevent disk space issues

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
LOG_DIR="$PROJECT_DIR/logs"
DAYS_TO_KEEP=${1:-30}

echo "🧹 Cleaning logs older than $DAYS_TO_KEEP days..."

if [ ! -d "$LOG_DIR" ]; then
    echo "Log directory not found: $LOG_DIR"
    exit 0
fi

# Find and remove old log files
find "$LOG_DIR" -type f -name "*.log" -mtime +$DAYS_TO_KEEP -delete
find "$LOG_DIR" -type f -name "*.log.*" -mtime +$DAYS_TO_KEEP -delete

echo "✅ Log cleanup completed!"

