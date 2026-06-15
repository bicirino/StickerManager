FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql

# Aponta o DocumentRoot para a pasta public/
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

COPY . /var/www/html

# Railway define a porta via $PORT
RUN sed -ri -e 's!Listen 80!Listen ${PORT}!g' /etc/apache2/ports.conf \
    && sed -ri -e 's!:80!:${PORT}!g' /etc/apache2/sites-available/*.conf

EXPOSE 8080
CMD ["sh", "-c", "apache2-foreground"]
