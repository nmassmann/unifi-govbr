#!/bin/bash

rm -rf var/cache/*
composer clearcache
composer install --no-dev --optimize-autoloader --classmap-authoritative
php bin/console cache:clear --env=prod --no-debug
php bin/console cache:warmup --env=prod --no-debug

