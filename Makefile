setup:
	@make build
	@make up 
	@make composer-update
build:
	docker compose up -d --build
	docker exec laravel-docker-vue bash -c "composer update --ignore-platform-req=ext-pcntl --ignore-platform-req=ext-exif"
	docker exec laravel-docker-vue bash -c "php artisan migrate:fresh --seed"
	docker exec laravel-docker-vue bash -c "npm i"
	docker exec laravel-docker-vue bash -c "npm install pm2 -g"
	docker exec laravel-docker-vue bash -c "npm install -g laravel-echo-server"
	docker exec laravel-docker-vue bash -c "pm2 start npm --name "vite" -- run dev"
	docker exec laravel-docker-vue bash -c "pm2 start php --name "queue" -- artisan queue:work --queue=high,default"
	docker exec laravel-docker-vue bash -c "pm2 start laravel-echo-server --name "echo" -- start"
	docker exec laravel-docker-vue bash -c "npm run build"
	rm laravel-app/public/hot
stop:
	docker-compose stop
up:
	docker compose up -d --build
composer-update:
	docker exec laravel-docker-vue bash -c "composer update"
data:
	docker exec laravel-docker-vue bash -c "php artisan migrate"
	docker exec laravel-docker-vue bash -c "php artisan db:seed"

