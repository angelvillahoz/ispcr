docker-compose exec php_server docker-php-ext-enable xdebug
make $(cat .env | xargs) xdebug
