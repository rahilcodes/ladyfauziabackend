FROM serversideup/php:8.4-fpm-nginx

# Copy codebase with correct permissions
COPY --chown=webuser:webgroup . /var/www/html

# Run composer install ignoring platform requirements (since extensions are available at runtime)
RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

# Configure public directory as the web root
ENV DOCUMENT_ROOT=/var/www/html/public

# Run storage link during build
RUN php artisan storage:link --force
