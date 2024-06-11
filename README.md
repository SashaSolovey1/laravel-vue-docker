## Установка Laravel + VueJs + Docker

Для успішного налаштування та запуску вашого проекту виконайте наступні кроки:

1. Завантажте репозиторій командою:
   ```sh
   git clone https://github.com/SashaSolovey1/laravel-vue-docker.git
   ```

2. Перейменуйте файл `.env.example` в `.env` та налаштуйте його відповідно до вашого оточення.

3. Запустіть проект в контейнері Docker Compose командами:
   ```sh
   docker-compose build --no-cache --force-rm
   docker-compose up -d
   ```

4. Ввійдіть в контейнер:
   ```sh
   docker exec -it laravel-docker bash
   ```

5. Оновіть Composer всередині контейнера:
   ```sh
   composer update --ignore-platform-req=ext-pcntl --ignore-platform-req=ext-exif
   ```

6. Зробіть міграцію БД командою:
   ```sh
   php artisan migrate
   ```

7. Виконайте заповнення початковими даними (seed) БД командою:
   ```sh
   php artisan db:seed
   ```

8. Встановіть nvm:
   ```sh
   curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.7/install.sh | bash
   ```

9. Встановіть Node.js та всі необхідні модулі:
   ```sh
   nvm install 18
   npm install
   ```

10. Встановіть PM2:
    ```sh
    npm install pm2 -g
    ```

11. Запустіть процеси за допомогою PM2:
    ```sh
    pm2 start npm --name "vite" -- run dev
    pm2 start php --name "queue" -- artisan queue:work --queue=high,default
    pm2 start laravel-echo-server --name "echo" -- start
    ```

12. Запустіть білд Vue.js командою:
    ```sh
    npm run build
    ```

13. Вийдіть з контейнера:
    ```sh
    exit
    ```

14. Якщо у вас виникають помилки на сайті `Failed to load resource: net::ERR_CONNECTION_REFUSED`, видаліть файл `public/hot`.

15. Для використання івенту відправки листа на пошту після додання коментаря:
<<<<<<< HEAD
    - В файлі `.env` введіть валідні дані SMTP Mailtrap або іншої служби.
    - В контролері `app/Http/Controllers/TestEmailController.php` та в івенті `app/Events/CommentCreated.php` замініть адресу пошти на вашу поточну.
    - Перевірте роботу SMTP за посиланням `/send-test-email`.
=======
   - В файлі `.env` введіть валідні дані SMTP Mailtrap або іншої служби.
   - В контролері `app/Http/Controllers/TestEmailController.php` та в івенті `app/Events/CommentCreated.php` замініть адресу пошти на вашу поточну.
   - Перевірте роботу SMTP за посиланням `/send-test-email`.
>>>>>>> e6b5934 (add code styling with laravel/pint)





## Опис проекту

Цей проект представляє собою чат, який дозволяє залишати коментарі в режимі реального часу за допомогою вебсокетів Pusher та Laravel Echo. Бекенд сайту побудований на Laravel, а фронтенд - на Vue.js. Вивід коментарів здійснюється через підключення до API бекенду з фільтрацією за датою, ім'ям користувача та електронною поштою в обидві сторони.

Кешування забезпечується Redis і очищується при додаванні нового коментаря. Івенти використовуються для вебсокетів, а також для відправки електронної пошти при створенні коментаря та зміні рейтингу коментаря.

Для обробки зображень використовується бібліотека Spatie/MediaLibrary, яка також додає оптимізацію зображень в чергу (queue). Для бродкасту каналів використовується Pusher, а для прослуховування на фронтенді - Laravel Echo. Серверні процеси запускаються у фоновому режимі за допомогою PM2.

При додаванні нового коментаря від користувача, якого не існує в системі, він автоматично реєструється. Для капчі використовується бібліотека MeWebStudio/Captcha, яка створює зображення та ключ на бекенді і передає їх на фронт для валідації.

Цей проект забезпечує швидку та зручну взаємодію користувачів у режимі реального часу, забезпечуючи високу продуктивність та зручність використання.

## Схема бази даних

![laravel_docker](https://github.com/SashaSolovey1/laravel-vue-docker/assets/72560323/e8d54ecb-b2f8-4cb9-bbd5-50bf3947c78e)

