FROM php:8.1-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    zip \
    libzip-dev \
    subversion \
    net-tools \
    gettext \
    gnupg2 \
    jq \
   && docker-php-ext-install zip gettext \
   && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer