FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y unzip git

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

# Install PHP dependencies
RUN composer install

CMD php -S 0.0.0.0:$PORT