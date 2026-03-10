FROM php:7.4-fpm-bullseye

# PHP拡張 + Nginx
RUN apt-get update && apt-get install -y \
    nginx \
    default-mysql-client \
    libpq-dev \
    zlib1g-dev \
    libzip-dev \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install pdo_mysql pdo_pgsql zip gd \
  && rm -rf /var/lib/apt/lists/*

# Composer
RUN curl -sS https://getcomposer.org/installer | php \
  && mv composer.phar /usr/local/bin/composer \
  && composer self-update

WORKDIR /var/www

# アプリコピー
COPY . .

# storage と cache に書き込み権限
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Composer install
RUN composer install --no-dev --optimize-autoloader

RUN php artisan config:clear
RUN php artisan cache:clear
RUN php artisan view:clear

# Nginx 設定コピー
COPY ./nginx.conf /etc/nginx/sites-enabled/default

# ポート開放
EXPOSE 10000

# CMD: Nginx と PHP-FPM を起動
CMD service nginx start \
 && php artisan migrate --force --seed \
 && php artisan storage:link \
 && php-fpm