#! /bin/bash

docker compose down

docker compose up -d

#./run composer install

#./run bin/console assets:install --symlink

#./run bin/console cache:clear