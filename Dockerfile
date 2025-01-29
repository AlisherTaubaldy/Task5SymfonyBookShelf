# Используем PHP 8.2
FROM php:8.2-fpm
COPY . /var/www/html/
ENTRYPOINT ["docker-php-entrypoint"]

# Устанавливаем системные зависимости
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libonig-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install \
    pdo_mysql \
    pdo_pgsql \
    zip \
    && docker-php-ext-enable \
    pdo_mysql \
    pdo_pgsql

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Настраиваем рабочую директорию
WORKDIR /var/www/html

# Копируем файлы проекта
COPY . .

# Устанавливаем права доступа
RUN chown -R www-data:www-data /var/www/html

# Устанавливаем зависимости через Composer
RUN composer install

# Убедитесь, что расширения и зависимости настроены
CMD ["php-fpm"]
