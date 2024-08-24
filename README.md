## Project Setup
# 1 git clone https://github.com/mahatab-uddin-shuvo/project-management-system.git
# 2 must be your php version 8.2 or grater then 8.2
# 3 .env.example file copy to .env 
# 4 composer install
# 5 create a database then database info paste to .env file like this 
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=YOUR_DB_NAME
    DB_USERNAME=YOUR_DB_USERNAME
    DB_PASSWORD=YOUR_DB_PASSWORD
# 6 php artisan passport:install
# 7 php artisan migrate
# 8 php artisan serve
# 9 php artisan db:seed --class=DatabaseSeeder 
    Create a user and role and SuperAdmin role assign to user
    email: admin@admin.com
    password: 123456
    url: YOUR_BASE_URL/api/login
# 10 YOUR_BASE_URL/api/permission/sync-route-to-permission 
    after login this user then hit this route, then all api permission name save in database
# 11 npm install 
    
# 12 when you use websocket then 
    php artisan queue:work
    php artisan reverb:start
    those command run for realtime data fetching 

    



