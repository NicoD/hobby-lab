FROM php:8.4-cli-alpine

RUN apk add --no-cache \
        git \
        curl \
        unzip \
        libzip-dev \
        icu-dev \
        libpq-dev \
        entr \
    && docker-php-ext-install zip intl opcache pdo_pgsql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public/"]
