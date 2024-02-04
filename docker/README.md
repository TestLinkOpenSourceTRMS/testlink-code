# Running Testlink using Docker

## Create a dotenv file for Docker

You're going to need a file named `.env` in the project root directory.  You can use `.env.example` or create your own.

```bash
cp -n .env.example .env
```

## Build the image

To  build a docker image of testlink, you can use the following command:

```bash
docker build --tag testlink:1.9.20 --tag testlink:latest .
```

Alternatively, build without cached layers:

```bash
docker build --no-cache --tag testlink-code:1.9.20 --tag testlink:latest .
```

## Starting up Testlink using `docker compose`

```bash
docker compose up -d
```

You should now be able to open https://localhost:8080 in your browser to proceed with the Testlink setup

### Database configuration

Based on the default `docker-compose.yml` and `.env` configuration, you'll use the following settings for the database setup:

| Key | Value |
| - | - |
| Database type | MySQL/MariaDB (5.6+ / 10.+) |
| Database host | testlink-mysql |
| Database admin login | root |
| Database admin password | masterkey |

You can provide your own values for `Database name`, `TestLink DB login` and `TestLink DB password`.

### Email configuration

Copy the mail configuration to your installation with:

```bash
cp -n docker/custom_config.inc.php ./
```

You can view the test emails at http://localhost:1080

### Restoring the sample database

There is a sample database in `docs/db_sample` which you can restore with:

```bash
docker compose up testlink-restore
```

## Troubleshooting

### Creating the Testlink database user manually

You'll need to create the testlink user yourself should you be presented with the following error during setup:

> 1045 - Access denied for user 'testlink'@'172.29.0.3' (using password: YES)
> TestLink ::: Fatal Error
> Connect to database testlink on Host testlink-mysql fails
> DBMS Error Message: 1045 - Access denied for user 'testlink'@'172.29.0.3' (using password: YES)

Connect to the app or database container and, using the `mysql` CLI, execute the following commands:

```sql
/* update the database name, user name and password
   values based on what you specified during setup */
USE `testlink`;
CREATE USER 'testlink'@'%' IDENTIFIED BY 'masterkey';
GRANT SELECT, UPDATE, DELETE, INSERT ON *.* TO 'testlink'@'%' WITH GRANT OPTION;
```

### Resetting your database

If you wish to reset your database, you'll need to delete the mysql volume and `config_db.inc.php`.

```bash
docker compose down
docker volume rm testlink-code_mysql
/bin/rm -f config_db.inc.php
docker compose up -d
```
