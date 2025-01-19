# Użyj oficjalnego obrazu PHP
FROM php:8.2-cli

# Instaluj zależności systemowe
RUN apt-get update -qq && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instaluj Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && mv composer.phar /usr/local/bin/composer \
    && php -r "unlink('composer-setup.php');"

# Ustaw katalog roboczy
WORKDIR /app

# Skopiuj pliki projektu
COPY . .

# Ustaw wersję platformy PHP (dla Composer)
RUN composer config platform.php 8.2.0

# Instaluj zależności Symfony
RUN composer install --no-scripts --no-interaction

# Domyślne polecenie
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
