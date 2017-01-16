#!/bin/bash
docker exec wpapp_db sh -c 'exec mysqldump --all-databases -uroot -p"$MYSQL_ROOT_PASSWORD"' > ./db/dump.sql
