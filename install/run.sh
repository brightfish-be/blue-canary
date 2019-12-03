#!/usr/bin/env sh

env=${APP_ENV:-development}
name=${APP_NAME:-"Blue Canary"}


# Generate autoload & cache files
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if [[ "$env" != "development" ]]; then
  composer dump-autoload --no-interaction --optimize --no-dev
fi


# Start the foreground process
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~

exec php-fpm
