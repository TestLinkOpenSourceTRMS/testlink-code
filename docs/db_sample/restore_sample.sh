#!/bin/bash -e

DB_HOST=testlink-mysql
DB_ROOT_USER=root
DB_ROOT_PASS=masterkey
TL_DB_NAME=testlink_sample
TL_DB_USER=testlink
TL_DB_PASS=masterkey

# Restore the database
echo "Creating database '${TL_DB_NAME}'..."
echo "CREATE DATABASE IF NOT EXISTS \`${TL_DB_NAME}\`;" | mysql -h ${DB_HOST} -u ${DB_ROOT_USER} --password=${DB_ROOT_PASS}
echo "Restoring database '${TL_DB_NAME}' from DB dump..."
mysql -h ${DB_HOST} -u ${DB_ROOT_USER} --password=${DB_ROOT_PASS} --database=${TL_DB_NAME} < testlink_sample.sql

# Create the testlink user
echo "Creating user '${TL_DB_USER}'..."
echo "CREATE USER IF NOT EXISTS '${TL_DB_USER}'@'%' IDENTIFIED BY '${TL_DB_PASS}';" | mysql -h ${DB_HOST} -u ${DB_ROOT_USER} --password=${DB_ROOT_PASS}
echo "GRANT SELECT, UPDATE, DELETE, INSERT ON \`${TL_DB_NAME}\`.* TO '${TL_DB_USER}'@'%' WITH GRANT OPTION;" | mysql -h ${DB_HOST} -u ${DB_ROOT_USER} --password=${DB_ROOT_PASS}
echo "GRANT EXECUTE ON FUNCTION \`${TL_DB_NAME}\`.UDFStripHTMLTags TO '${TL_DB_USER}'@'%';" | mysql -h ${DB_HOST} -u ${DB_ROOT_USER} --password=${DB_ROOT_PASS}

# Update config_db.inc.php
echo "Creating 'config_db.inc.php' file..."
cat > ../../config_db.inc.php<< EOF
<?php
define('DB_TYPE', 'mysql');
define('DB_USER', '${TL_DB_USER}');
define('DB_PASS', '${TL_DB_PASS}');
define('DB_HOST', '${DB_HOST}');
define('DB_NAME', '${TL_DB_NAME}');
define('DB_TABLE_PREFIX', '');
EOF

echo "Run 'docker compose up -d' and browse to http://localhost:8080 to view the TestLink sample DB."
