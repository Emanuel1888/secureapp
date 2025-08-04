# Usa imagen oficial de PHP con Apache
FROM php:8.2-apache

# Instala extensiones necesarias
RUN docker-php-ext-install pdo pdo_mysql

# Habilita mod_rewrite para .htaccess
RUN a2enmod rewrite

# Instala soporte para variables de entorno desde .env
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copiar el contenido del proyecto al directorio público del servidor
COPY secureapp/ /var/www/html/

# Establece permisos adecuados
RUN chown -R www-data:www-data /var/www/html

# Asegura que Apache permita .htaccess
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Establece el directorio de trabajo
WORKDIR /var/www/html
