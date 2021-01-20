ISPCR = docker-compose exec ispcr_server blat
PHP = docker-compose exec php_server php
PHP_BASH = docker-compose exec php_server

.PHONY: help analyze build config database-backup docker docker-restart lint logs node_modules-install php-libraries-reload schema-backup serve vendor-install vendor-update xdebug

help:
	@ echo "Usage: make [target]"
	@ echo "  analyze                             Run the PHPStan static analysis tool on the codebase."
	@ echo "  build                               Build the project for the deployment."
	@ echo "  config                              Configure the isPCR application using the environment"
	@ echo "                                      variables. The ISPCR_BASE_URL, SITE_AUTH_REALM, and"
	@ echo "                                      MYSQL_PASSWORD environment variables must be set to"
	@ echo "                                      configure the application for the deployment."
	@ echo "  database-backup                     Dump the current database to the dumps directory."
	@ echo "  database-initialize                 Initialize a new database instance empty from the"
	@ echo "                                      database schema."
	@ echo "  docker                              Rebuild the images and start their docker containers."
	@ echo "  docker-restart                      Restart the docker containers. This will reload the database"
	@ echo "                                      from the files in the dumps directory."
	@ echo "  help                                Show the ISPCR Makefile arguments and their functions"
	@ echo "  lint                                Run the PHP CodeSniffer tool on the codebase and"
	@ echo "                                      attempts to fix any errors found with the PHP Code"
	@ echo "                                      Beautifier and Fixer tool."
	@ echo "  logs                                Make the logs directory for the ISPCR application"
	@ echo "  node_modules-install                Install all the JavaScript libraries"
	@ echo "  php-libraries-reload                Reload the (existing and new) PHP libraries used by the"
	@ echo "                                      BLAT application"
	@ echo "  schema-backup                       Dump the current database schema to the db directory."
	@ echo "  serve                               Tear down, rebuild, and restart all services."
	@ echo "  vendor-install                      Rebuilds the Composer image and install all the PHP libraries"
	@ echo "                                      used by the isPCR application"
	@ echo "  vendor-update                       Rebuilds the Composer image and upgrade/downgrade all the PHP"
	@ echo "                                      libraries used by the isPCR application"
	@ echo "  xdebug                              Activate the XDebug module for PHP debugging."

analyze: vendor
	$(PHP) ./vendor/bin/phpstan analyse -l 7 -c phpstan.neon lib

build: logs vendor-install node_modules-install 
	cd ./go/ispcrserver/assets && sh ./download-external-assets.sh

config:
	cp ./config/settings.yml.dist ./config/settings.yml
	chmod 0644 ./config/settings.yml
	sed -i 's,<ISPCR_BASE_URL>,$(ISPCR_BASE_URL),g' ./config/settings.yml
	sed -i 's,<SITE_AUTH_REALM>,$(ISPCR_BASE_URL),g' ./config/settings.yml
	sed -i 's,<MYSQL_PASSWORD>,$(MYSQL_PASSWORD),g' ./config/settings.yml

database-backup:
	docker build -t mariadb_database_backup ./utilities/mariadb/database-backup/
	docker run --rm -tv $(PWD)/dumps:/data/dumps --user $$(id -u):$$(id -g) --env-file ./.env --network redfly_default mariadb_database_backup

database-initialize:
	cp ./db/schema.sql ./dumps/
	docker-compose down -v
	docker-compose build
	docker-compose up -d
	echo "You have to delete the SQL file, schema.sql, from the dumps directory"

docker:
	docker-compose down -v
	docker-compose build
	docker-compose up -d

docker-restart:
	docker-compose down -v
	docker-compose up -d

lint: vendor
	$(PHP) ./vendor/bin/phpcbf | true
	$(PHP) ./vendor/bin/phpcs

logs:
	mkdir -p ./logs

node_modules-install: html/package.json
	cd ./html 
	yarn add react \
		react-dom \
		axios \
		@babel/standalone \
		eslint

php-libraries-reload: composer.json
	docker pull composer:latest
	docker run --rm --volume $(PWD):/app --user $$(id -u):$$(id -g) composer --version
	docker run --rm --volume $(PWD):/app --user $$(id -u):$$(id -g) composer validate
	docker run --rm --volume $(PWD):/app --user $$(id -u):$$(id -g) composer dump-autoload -o

schema-backup:
	docker build -t mariadb_schema_backup ./utilities/mariadb/schema-backup/
	docker run --rm -tv $(PWD)/db:/data/db --user $$(id -u):$$(id -g) --env-file ./.env --network redfly_default mariadb_schema_backup

serve: docker

vendor-install: composer.json
	[ -f composer.lock ] && rm composer.lock || true
	docker pull composer:latest
	docker run --rm --volume $(PWD):/app --user $$(id -u):$$(id -g) composer --version
	docker run --rm --volume $(PWD):/app --user $$(id -u):$$(id -g) composer validate
	docker run --rm --volume $(PWD):/app --user $$(id -u):$$(id -g) composer install

vendor-update: composer.json
	[ -f composer.lock ] && rm composer.lock || true
	docker pull composer:latest
	docker run --rm --volume $(PWD):/app --user $$(id -u):$$(id -g) composer --version
	docker run --rm --volume $(PWD):/app --user $$(id -u):$$(id -g) composer validate
	docker run --rm --volume $(PWD):/app --user $$(id -u):$$(id -g) composer update

xdebug:
	$(PHP_BASH) cp ./assets/xdebug.ini.dist /usr/local/etc/php/conf.d/xdebug.ini
	$(PHP_BASH) chmod 0644 /usr/local/etc/php/conf.d/xdebug.ini
	$(PHP_BASH) sed -i 's,<XDEBUG_IP_ADDRESS>,$(XDEBUG_IP_ADDRESS),g' /usr/local/etc/php/conf.d/xdebug.ini
	docker-compose restart php_server
