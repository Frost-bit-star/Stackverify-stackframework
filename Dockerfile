# Use PHP 8.2 CLI image (no Apache)
FROM php:8.2-cli

# Install SQLite PDO dependencies
RUN apt-get update && \
    apt-get install -y libsqlite3-dev && \
    docker-php-ext-install pdo pdo_sqlite

# Copy project files into /app
COPY . /app

# Set working directory to /app
WORKDIR /app

# Copy entrypoint script
COPY entrypoint.sh /entrypoint.sh

# Make entrypoint script executable
RUN chmod +x /entrypoint.sh

# Adjust permissions for SQLite database if needed
RUN chown -R www-data:www-data app/db

# Expose port (Render maps it dynamically)
EXPOSE 3000

# Set entrypoint
ENTRYPOINT ["/entrypoint.sh"]