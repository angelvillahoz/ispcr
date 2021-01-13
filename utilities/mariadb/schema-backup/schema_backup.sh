#!/bin/sh

set -e;

mkdir -p /data/db;
mysqldump \
    --host=${MYSQL_HOST} \
    --port=${MYSQL_PORT} \
    --user=${MYSQL_BACKUP_USER} \
    --password=${MYSQL_BACKUP_PASSWORD} \
    --single-transaction \
    --routines \
    --triggers \
    --no-data \
    ${MYSQL_DATABASE} > /data/db/schema.sql;