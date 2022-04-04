backend setup

git clone https://github.com/harsha14581/laravel-activity-teest.git

cd laravel-activity-teest

composer install

cp .env-example .env

php artisan key:generate

// change database credentials in .env file

php artisan passort:install

php artisan serve


