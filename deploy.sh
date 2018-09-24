docker-compose down

composer install

docker-compose up -d --force-recreate

sleep 5

php database/migrate.php $1
