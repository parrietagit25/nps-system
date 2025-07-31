# Usar imagen base de PHP con Apache
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
    default-mysql-client \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Habilitar mod_rewrite y mod_headers para Apache
RUN a2enmod rewrite
RUN a2enmod headers

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos de configuración de Composer
COPY composer.json composer.lock ./

# Instalar dependencias de Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-dev --optimize-autoloader

# Copiar el código de la aplicación
COPY . .

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Configurar Apache
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Copiar y configurar script de entrada
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Exponer puerto 80
EXPOSE 80

# Comando por defecto
CMD ["/usr/local/bin/entrypoint.sh"]
