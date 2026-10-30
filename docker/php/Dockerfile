# Versión fijada a un parche concreto (no una etiqueta flotante como "8.2-fpm"):
# 8.4.27 corrige CVE-2026-109618 y CVE-2026-109619 (PHP2Shell).
# Al salir un nuevo parche de seguridad, sube este número y reconstruye con --pull.
FROM php:8.4.27-fpm

RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev \
    && docker-php-ext-install pdo pdo_mysql intl zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Endurecimiento de PHP (incluye mitigación de CVE-2026-115560, aún sin parche)
COPY docker/php/conf.d/security.ini /usr/local/etc/php/conf.d/zz-security.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
