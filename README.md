## Oksana Bürki

[//]: # (<https://xeniaweb.art>)

### About this project

PhotoAlbums API Project - Laravel 11 
##

### License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Docker
### Add alias for sail
```sh
alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'
```

### Start containers
```sh
sail up -d
```
```sh
./vendor/bin/sail up -d
```

### Stop containers
```sh
sail down
```
```sh
./vendor/bin/sail down
```

## Frontend
```sh
./vendor/bin/sail npm install
```
```sh
./vendor/bin/sail npm run build
```

```sh
./vendor/bin/sail npm run dev
```

### Refresh Database
```sh
./vendor/bin/sail artisan migrate:fresh --force
```
### Refresh Database fake data
```sh
./vendor/bin/sail artisan db:seed --force
```

### If no ./vendor directory yet
<https://hub.docker.com/_/composer>
```sh
docker run --rm --interactive --tty \
  --volume $PWD:/app \
    --user $(id -u):$(id -g) \
  composer install
```
