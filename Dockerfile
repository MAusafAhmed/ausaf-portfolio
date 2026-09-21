FROM php:8.2-apache

# Enable PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable Apache mod_rewrite (useful for clean URLs)
RUN a2enmod rewrite

# Copy project files into the Apache web root
COPY . /var/www/html/

# Allow .htaccess overrides
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html

# Render provides the PORT env var; Apache must listen on it
ENV APACHE_LISTEN_PORT=80

EXPOSE 80

CMD ["apache2-foreground"]