FROM php:8.3-fpm

# Установка зависимостей
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Настройки рабочей директории
WORKDIR /var/www/html

# Пользователь для artisan (не root)
RUN groupadd -g 1000 laravel && useradd -u 1000 -g laravel laravel
RUN chown -R laravel:laravel /var/www/html
USER laravel

EXPOSE 9000
CMD ["php-fpm"]