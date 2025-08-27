#!/bin/bash

# Primeiro instalamos o composer
OS=$(uname)
if [ "$OS" = "Darwin" ]; then
    echo "Instalando composer no macOS"
    brew install composer
elif [ "$OS" = "Linux" ]; then
    echo "Instalando composer no Linux"
    curl -s https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
else
    echo "Sistema operacional não identificado: $OS"
    exit
fi

rm -rf var/cache/*
composer clearcache
composer install --no-dev --optimize-autoloader --classmap-authoritative
php bin/console cache:clear --env=prod --no-debug
php bin/console cache:warmup --env=prod --no-debug
chmod 777 -R var/cache

