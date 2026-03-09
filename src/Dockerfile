# FROM php:7.4-fpm-bullseye

# COPY php.ini /usr/local/etc/php/

# RUN apt-get update && apt-get install -y \
#     default-mysql-client \
#     zlib1g-dev \
#     libzip-dev \
#     unzip \
#     libpng-dev \
#     libjpeg-dev \
#     libfreetype6-dev \
#   && docker-php-ext-configure gd --with-freetype --with-jpeg \
#   && docker-php-ext-install pdo_mysql zip gd \
#   && rm -rf /var/lib/apt/lists/*

# RUN curl -sS https://getcomposer.org/installer | php \
#   && mv composer.phar /usr/local/bin/composer \
#   && composer self-update

# WORKDIR /var/www

# ENV HOME=/tmp

FROM php:7.4-fpm-bullseye

# PHP拡張
RUN apt-get update && apt-get install -y \
    nginx \
    default-mysql-client \
    zlib1g-dev \
    libzip-dev \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install pdo_mysql zip gd \
  && rm -rf /var/lib/apt/lists/*

# Composer
RUN curl -sS https://getcomposer.org/installer | php \
  && mv composer.phar /usr/local/bin/composer \
  && composer self-update

WORKDIR /var/www

COPY . .

# storage:link
RUN php artisan storage:link
RUN composer install --no-dev --optimize-autoloader

# Nginx 設定コピー
COPY ./src/nginx.conf /etc/nginx/sites-enabled/default

# ポート開放
EXPOSE 10000

# 起動コマンド
CMD ["sh", "-c", "nginx -g 'daemon off;' & php-fpm"]