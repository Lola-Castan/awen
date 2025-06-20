FROM php:8.4-apache

RUN apt-get update && apt-get install -y libicu-dev libzip-dev unzip git \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache bcmath

RUN a2enmod rewrite

WORKDIR /var/www/html
COPY . .

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

RUN chown -R www-data:www-data /var/www/html/public

# TODO Add doctrine command

EXPOSE 8082

CMD ["apache2-foreground"]
