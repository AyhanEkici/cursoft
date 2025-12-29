#!/bin/bash
set -e

echo "🚀 Starting Render build process for Cursoft..."

# Create public directory for web files (Render serves from public/)
mkdir -p public
mkdir -p public/api
mkdir -p public/pages
mkdir -p public/includes
mkdir -p public/config
mkdir -p public/database

# Copy all PHP files to public (Render serves from public/)
echo "📦 Copying application files..."

# Copy main entry point
if [ -f "index.php" ]; then
  cp index.php public/
  echo "✓ Copied index.php"
fi

# Copy API endpoints
if [ -d "api" ]; then
  cp -r api/* public/api/ 2>/dev/null || true
  echo "✓ Copied API endpoints"
fi

# Copy pages
if [ -d "pages" ]; then
  cp -r pages/* public/pages/ 2>/dev/null || true
  echo "✓ Copied pages"
fi

# Copy includes (PHP classes)
if [ -d "includes" ]; then
  cp -r includes/* public/includes/ 2>/dev/null || true
  echo "✓ Copied includes"
fi

# Copy public assets (CSS, JS) - these stay in public/ root
# Check multiple possible locations for health.php
if [ -d "public" ]; then
  # Copy CSS, JS, and health.php to public root
  if [ -d "public/css" ]; then
    cp -r public/css public/ 2>/dev/null || true
  fi
  if [ -d "public/js" ]; then
    cp -r public/js public/ 2>/dev/null || true
  fi
  # Try multiple locations for health.php
  if [ -f "public/health.php" ]; then
    cp public/health.php public/ 2>/dev/null || true
  elif [ -f "/tmp/public/health.php" ]; then
    cp /tmp/public/health.php public/ 2>/dev/null || true
  elif [ -f "../public/health.php" ]; then
    cp ../public/health.php public/ 2>/dev/null || true
  fi
  echo "✓ Copied public assets"
fi

# CRITICAL: Ensure health.php exists in public/ directory
# Create a simple one if it doesn't exist
if [ ! -f "public/health.php" ]; then
  echo '<?php header("Content-Type: application/json"); echo json_encode(["status" => "healthy", "service" => "cursoft", "timestamp" => date("Y-m-d H:i:s")]); ?>' > public/health.php
  echo "✓ Created health.php fallback"
fi

# Copy config
if [ -d "config" ]; then
  cp -r config/* public/config/ 2>/dev/null || true
  echo "✓ Copied config"
fi

# Create necessary directories
mkdir -p public/logs
mkdir -p public/workspaces
mkdir -p public/tmp

# Set permissions
chmod -R 755 public
chmod -R 777 public/logs 2>/dev/null || true
chmod -R 777 public/workspaces 2>/dev/null || true
chmod -R 777 public/tmp 2>/dev/null || true

# Create router.php for PHP built-in server (Render uses PHP -S)
cat > public/router.php << 'ROUTER_EOF'
<?php
// Router for PHP built-in server
// This allows clean URLs and proper routing

$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Remove query string
$path = strtok($path, '?');

// If requesting root, serve index.php
if ($path === '/' || $path === '') {
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    require __DIR__ . '/index.php';
    return true;
}

// If file exists, serve it directly
$filePath = __DIR__ . $path;
if (file_exists($filePath) && is_file($filePath)) {
    return false; // Let PHP server serve the file
}

// Otherwise, route to index.php (for clean URLs)
$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__ . '/index.php';
return true;
ROUTER_EOF

chmod +x public/router.php

echo "✅ Build completed successfully!"
echo "📁 Public directory structure:"
ls -la public/ | head -20

