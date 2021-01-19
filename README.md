# isPCR

## TL;DR

 - Have `docker-ce`, `docker-compose`, `php`, and `php-cli` installed for the development environment.
 - Move `*.sql.gz` dump files into `dumps`. These will be used to automatically initialize the
   mariadb docker instance. **Note that file permissions must be world readable.**
 - Update `.env.dist` by filling out all fields denoted with `<...>` and save the edited file as `.env`
 - Most processes are handled by the Makefile in conjunction with the configuration information
   contained in the `.env` file.  Run `make help` to list available targets and a description of what
   they do.
 - Have the necessary PHP libraries installed by executing:
   ```
   make $(cat .env | xargs) vendor-install
   ```
 - Have the necessary JavaScript/ReactJS libraries installed by executing:
   ```
   make $(cat .env | xargs) node_modules-install
   ```
 - The configuration and build process is controlled via a Makekefile and uses the environment defined
   in the `.env` file. Run the following make targets to configure, build, and start the services:
   ```
   make $(cat .env | xargs) config
   make $(cat .env | xargs) build
   make $(cat .env | xargs) serve
   ```
 - Have a new database instance empty created by executing:
   ```
   make $(cat .env | xargs) database-initialize
   ```
- After copying a dump of the production database and rebuilding the development servers (e.g., using
  `make $(cat .env | xargs) serve` or `make $(cat .env | xargs) docker-restart`) the redfly user
  credentials will likely need to be updated for the development environment:
  ```
  make $(cat .env | xargs) devuser
  ```
- Make a manual dump of the database, if desired.
  ```
  make $(cat .env | xargs) database-backup
  ```

## Requirements

- Docker CE 20.10.2+
- Docker Compose 1.27.4+
- PHP 7.4.14+
