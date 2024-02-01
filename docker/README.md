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

## Troubleshooting

### Creating the Testlink database user manually

> 1045 - Access denied for user 'testlink'@'172.29.0.3' (using password: YES)
> TestLink ::: Fatal Error
> Connect to database testlink on Host testlink-mysql fails
> DBMS Error Message: 1045 - Access denied for user 'testlink'@'172.29.0.3' (using password: YES)

Connect to the app or database container and, using the `mysql` CLI, execute the following commands:

```sql
use `testlink`;
CREATE USER 'testlink'@'%' IDENTIFIED BY 'pwd123';
GRANT SELECT, UPDATE, DELETE, INSERT ON *.* TO 'testlink'@'%' WITH GRANT OPTION;
```

### Resetting your database

If you wish to reset your database, you'll need to delete the mysql volume and `config_db.inc.php`.

```bash
docker compose down
docker volume rm testlink-code_mysql
/bin/rm -f config_db.inc.php
```
