#!/bin/bash
set -e
export PGPASSWORD=$POSTGRES_PASSWORD;

# The primary application database ($APP_DB_NAME) is already created by the
# postgres image via POSTGRES_DB. Here we create the application user, the
# dedicated testing database (<APP_DB_NAME>_test), the dedicated e2e database
# (<APP_DB_NAME>_e2e), and grant privileges on all of them.
# This runs only on first container init (empty data directory).
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
  CREATE USER $APP_DB_USER WITH PASSWORD '$APP_DB_PASS';
  CREATE DATABASE ${APP_DB_NAME}_test OWNER $APP_DB_USER;
  CREATE DATABASE ${APP_DB_NAME}_e2e OWNER $APP_DB_USER;
  GRANT ALL PRIVILEGES ON DATABASE $APP_DB_NAME TO $APP_DB_USER;
  GRANT ALL PRIVILEGES ON DATABASE ${APP_DB_NAME}_test TO $APP_DB_USER;
  GRANT ALL PRIVILEGES ON DATABASE ${APP_DB_NAME}_e2e TO $APP_DB_USER;
EOSQL

# Ensure the application user can create objects in the primary database's schema.
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$APP_DB_NAME" <<-EOSQL
  GRANT ALL ON SCHEMA public TO $APP_DB_USER;
EOSQL
