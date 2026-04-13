# event-planner

## source-code

1.  Переходим в директорию

        cd source-code

2.  Копируем .env.example в .env

3.  Скачиваем зависимости

        composer install

4.  Создаем базу данных

        CREATE USER "event_planner" WITH PASSWORD '';
        CREATE DATABASE "event_planner" WITH OWNER 'event_planner';
    
5.  Запускаем миграции базы данных:

        php artisan migrate

6.  Генерируем ключ приложения:

        php artisan key:generate

7.  Создаем ссылку на storage:

        php artisan storage:link

8.  Запускаем сервер

        php artisan serve
