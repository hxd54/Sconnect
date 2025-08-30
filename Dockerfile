# Use the official PHP image with Apache
FROM php:8.1-apache

# Copy project files to the container
COPY . /var/www/html/

# Set the working directory
WORKDIR /var/www/html/

# Install PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache modules
RUN a2enmod rewrite

# Expose port 80
EXPOSE 80