# Use official PHP 8.2 image with Apache
FROM php:8.2-apache

# Install SQLite PDO extension
RUN docker-php-ext-install pdo pdo_sqlite

# Enable Apache rewrite module (if using .htaccess)
RUN a2enmod rewrite

# Copy all project files into /var/www/html
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Adjust permissions for your SQLite database folder
RUN chown -R www-data:www-data app/db

# Expose port 80 for Render
EXPOSE 80
