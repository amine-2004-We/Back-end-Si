FROM php:8.3-fpm-alpine

# ===============================
# System & PHP dependencies + Node/npm pour Browsershot
# ===============================
RUN apk add --no-cache \
    bash git curl icu-dev oniguruma-dev libzip-dev unzip \
    freetype-dev libjpeg-turbo-dev libpng-dev \
    postgresql-dev \
    nodejs npm \
    chromium \
    nss freetype harfbuzz ca-certificates ttf-freefont \
    g++ make python3 \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql intl zip opcache gd xml simplexml xmlwriter mbstring bcmath

# ===============================
# Puppeteer ENV (pour Browsershot)
# ===============================
ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true
ENV PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium

# ===============================
# Composer
# ===============================
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
COPY . .

# ===============================
# PHP dependencies
# ===============================
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --optimize-autoloader

# ===============================
# Node dependencies pour Browsershot
# ===============================
COPY package*.json ./
RUN npm install --legacy-peer-deps

RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
CMD ["/entrypoint.sh"]
