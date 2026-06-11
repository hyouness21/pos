FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions (calendar and gd are required)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd calendar pdo pdo_mysql zip opcache

# Install Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

# Install PHP and JS dependencies, build assets
RUN composer install --optimize-autoloader --no-dev --no-interaction \
    && npm ci \
    && npm run build

EXPOSE 8000

CMD sh -c "php artisan migrate --force && php artisan storage:link 2>/dev/null; php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"
