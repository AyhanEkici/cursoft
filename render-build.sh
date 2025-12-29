#!/bin/bash
set -e

echo "🚀 Starting Render build process for Cursoft..."

# Create public directory for web files
mkdir -p public
mkdir -p public/api
mkdir -p public/pages
mkdir -p public/includes
mkdir -p public/public

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

# Copy public assets (CSS, JS)
if [ -d "public" ]; then
  cp -r public/* public/public/ 2>/dev/null || true
  echo "✓ Copied public assets"
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

# Create .htaccess for routing (if needed)
cat > public/.htaccess << 'EOF'
RewriteEngine On
RewriteBase /

# Redirect to index.php if file doesn't exist
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
EOF

echo "✅ Build completed successfully!"

