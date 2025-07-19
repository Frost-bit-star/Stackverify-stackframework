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

# Adjust permissions for SQLite database if needed
RUN chown -R www-data:www-data app/db

# Expose port 3000
EXPOSE 3000

# Start PHP built-in server on 0.0.0.0:$PORT (Render uses env PORT)
CMD ["php", "-S", "0.0.0.0:${PORT}"]
