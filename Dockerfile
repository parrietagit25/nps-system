FROM php:8.1-apache

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Habilitar mod_rewrite para Apache
RUN a2enmod rewrite

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos de configuración
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh

# Copiar archivos de la aplicación
COPY . .

# Instalar dependencias de Composer
RUN composer install --no-dev --optimize-autoloader

# Ajustar permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 644 /var/www/html/*.php \
    && chmod -R 644 /var/www/html/admin/*.php \
    && chmod -R 644 /var/www/html/includes/*.php \
    && chmod -R 644 /var/www/html/config/*.php

# Exponer puerto 80
EXPOSE 80

# Script de entrada
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]
