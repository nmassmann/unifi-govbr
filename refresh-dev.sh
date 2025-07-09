#!/bin/bash

rm -rf var/cache/*
composer clearcache
composer install --optimize-autoloader --classmap-authoritative
php bin/console cache:clear --env=dev
php bin/console cache:warmup --env=dev
chmod 777 -R var/cache

