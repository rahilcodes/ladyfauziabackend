FROM serversideup/php:8.4-fpm-nginx

# Switch to root to configure directories and permissions
USER root

# Copy codebase with correct permissions
COPY --chown=www-data:www-data . /var/www/html

# Ensure the entire app directory (including storage and cache) is fully writeable by www-data
RUN mkdir -p /var/www/html/storage/framework/cache/data \
             /var/www/html/storage/framework/views \
             /var/www/html/storage/framework/sessions \
             /var/www/html/storage/logs \
             /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html

# Switch to www-data to safely run composer install
USER www-data

# Run composer install ignoring platform requirements and disabling scripts during build
RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs --no-scripts

# Switch back to root to configure system-level environment variables
USER root

# Configure public directory as the web root
ENV DOCUMENT_ROOT=/var/www/html/public

# Run storage link during build
RUN php artisan storage:link --force

# Return to www-data for container runtime execution
USER www-data
