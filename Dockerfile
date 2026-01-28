FROM php:8.2-fpm

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Set permissions for uploads
RUN chown -R www-data:www-data /var/www/html/uploads
