backend setup

git clone https://github.com/harsha14581/laravel-activity-teest.git

cd laravel-activity-teest

composer install

cp .env-example .env (Note: change database credentials in .env file)

php artisan key:generate

php artisan migrate

php artisan passort:install

php artisan storage:link

php artisan serve


#For accessing apis

127.0.0.1/api -> Base url


