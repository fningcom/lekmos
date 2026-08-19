FROM php:8.3-cli

# Системные зависимости
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libonig-dev \
        unzip \
    && docker-php-ext-install mbstring \
    && pecl install dbase \
    && docker-php-ext-enable dbase \
    && apt-get purge -y --auto-remove libonig-dev \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

CMD ["php", "script.php"]
