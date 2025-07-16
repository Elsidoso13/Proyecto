# Usa una imagen base de PHP con Apache
FROM php:8.2-apache

# Copia todos los archivos de tu app al directorio del servidor
COPY . /var/www/html/

# Da permisos a los archivos copiados
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expone el puerto 80 para que Railway pueda acceder
EXPOSE 80
