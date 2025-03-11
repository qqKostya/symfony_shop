# Используем официальный PHP 8.3 с FPM
FROM php:8.3-fpm

# Устанавливаем нужные расширения
RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql

# Копируем Composer внутрь контейнера
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Устанавливаем рабочую директорию
WORKDIR /var/www

CMD ["php-fpm"]
