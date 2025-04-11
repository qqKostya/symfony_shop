# Используем официальный PHP 8.3 с FPM
FROM php:8.3-fpm

# Устанавливаем нужные пакеты
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libzip-dev \
    unzip \
    git \
    librdkafka-dev \
    && docker-php-ext-install pdo pdo_pgsql gd zip \
    && pecl install rdkafka \
    && docker-php-ext-enable rdkafka



# Копируем Composer внутрь контейнера
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Устанавливаем рабочую директорию
WORKDIR /var/www

CMD ["php-fpm"]
