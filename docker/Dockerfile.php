# PHP/Apache Dockerfile for Cursoft - Render Compatible
FROM --platform=linux/amd64 php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions (MySQL and PostgreSQL support)
RUN docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Enable Apache modules
RUN a2enmod rewrite headers

# Set working directory
WORKDIR /var/www/html

# CRITICAL: Remove ALL default files from base image
RUN rm -rf /var/www/html/* /var/www/html/.* 2>/dev/null || true

# Copy application files
COPY index.php /var/www/html/index.php
COPY includes/ /var/www/html/includes/
COPY pages/ /var/www/html/pages/
COPY api/ /var/www/html/api/
COPY config/ /var/www/html/config/
COPY database/ /var/www/html/database/

# Copy render-build.sh first and run it (creates public/ structure)
COPY render-build.sh /tmp/render-build.sh
RUN chmod +x /tmp/render-build.sh && /tmp/render-build.sh

# Copy scripts directory if it exists (optional)
COPY scripts/ /var/www/html/scripts/

# Copy health.php to root for easy access
RUN cp /var/www/html/public/health.php /var/www/html/health.php 2>/dev/null || true

# Fix permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Create startup script that uses PORT env variable
RUN echo '#!/bin/bash\n\
set -e\n\
PORT=${PORT:-10000}\n\
sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf\n\
sed -i "s/:80/:$PORT/g" /etc/apache2/sites-available/000-default.conf\n\
exec apache2-foreground' > /start.sh && \
chmod +x /start.sh

# Configure Apache VirtualHost
RUN echo '<VirtualHost *:10000>\n\
    ServerName localhost\n\
    DocumentRoot /var/www/html/public\n\
    DirectoryIndex index.php\n\
    <Directory /var/www/html/public>\n\
        Options -Indexes +FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Disable default site and enable our site
RUN a2dissite 000-default 2>/dev/null || true
RUN a2ensite 000-default

# Expose port (Render will override with PORT env var)
EXPOSE 10000

# Use startup script
CMD ["/start.sh"]
