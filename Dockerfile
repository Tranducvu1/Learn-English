FROM php:8.4-cli-bookworm

RUN apt-get update && apt-get install -y \
    git unzip curl libpq-dev libzip-dev libpng-dev libonig-dev \
    nodejs npm \
    && docker-php-ext-install pdo pdo_pgsql pgsql pdo_mysql mysqli zip mbstring bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY . .

RUN composer install --no-dev --optimize-autoloader \
    && php artisan app:publish-frontend --force \
    && chmod -R 775 storage bootstrap/cache \
    && chmod +x docker/entrypoint.sh

EXPOSE 10000

ENTRYPOINT ["/bin/bash", "docker/entrypoint.sh"]
