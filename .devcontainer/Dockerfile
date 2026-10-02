FROM mcr.microsoft.com/devcontainers/php:8.4-bookworm

USER root

# Sem Node (ADR-005). Extensões do Laravel + MySQL; o cliente mysql serve ao post-create.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        default-mysql-client \
        libicu-dev \
        libonig-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        intl \
        mbstring \
        pcntl \
        pdo_mysql \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY php.ini /usr/local/etc/php/conf.d/99-cartorios.ini

USER vscode
