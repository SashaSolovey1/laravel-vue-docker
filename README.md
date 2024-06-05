Laravel + VueJs + Docker
Установка:
1. Завантажте репозиторій командою `git clone https://github.com/SashaSolovey1/laravel-vue-docker.git`
2. Перейменуйте `.env.example` в `.env` (всю необхідну конфігурацію для роботи додатку в контейнері я запишу в .env.example)
3. Запустіть проект в контейнері docker-compose командами:
   - `docker-compose build --no-cache --force-rm`
   - `docker-compose up -d`
   - `docker exec laravel-docker bash -c "composer update"`  
4. Зробіть міграцію БД командою `docker exec laravel-docker bash -c "php artisan migrate"`
5. Виконайте seed БД командою `docker exec laravel-docker bash -c "php artisan db:seed"`
6. Перейдіть в корінь додатку та встановіть всі бібліотеки для vue.js командами `cd laravel-app` та `npm i`
7. Запустіть Vue.js на окремому терміналі командою `npm run dev`
8. Поставте виконання черг на бекграунді вашого проекта командою `exec laravel-docker bash -c "nohup php artisan queue:work --daemon"`
9. Для використання івенту відправки листа на пошту після додання коментару:
    - в файлі .env введіть валідні данні smtp mailtrap, або іншої служби
    - в контролері `app/Http/Controllers/TestEmailController.php` та в слухачі `/app/Listeners/SendCommentNotification.php` замініть поточний адрес пошти на вашу
    - перевірте роботу smtp по посиланню `/send-test-email`
