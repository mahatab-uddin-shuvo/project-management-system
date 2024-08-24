## Project Setup
Php Version: 8.2
Larevel version: 11.9 

### 1 Clone this URL
    git clone https://github.com/mahatab-uddin-shuvo/project-management-system.git
### 2 Your must be your php version 8.2 or grater then 8.2
### 3 .env.example file copy to .env 
### 4 After Clone this project then install 
    composer install
### 5 Create a database then database info paste to .env file like this 
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=YOUR_DB_NAME
    DB_USERNAME=YOUR_DB_USERNAME
    DB_PASSWORD=YOUR_DB_PASSWORD
    
### 6 After set this database configuration then run this command
    php artisan migrate
    
### 7 For Authentication use
    php artisan passport:install

### 8 This Command for Run this Project
    php artisan serve
    
### 9 Create a user and role and SuperAdmin role assign to user for run this seeder file in your command line
    php artisan db:seed --class=DatabaseSeeder 

### 10 Your login credentials and api route
    url: {{YOUR_BASE_URL}}/api/login
    email: admin@admin.com
    password: 123456
    
### 11 After login this user then hit this route, then all api permission name save in database
    {{YOUR_BASE_URL}}/api/permission/sync-route-to-permission 

### 12 Then run this command for using vite connection for check realtime data fetching in welcome.blade.php
    npm install
    
### 13 For dev mode 
    npm run dev
    
### 14 For production mode 
    npm run build
    
### 15 When you use websocket, those command run for realtime data fetching     
    php artisan reverb:start
    
### 16 Queue run for websocket
    php artisan queue:work

{{YOUR_BASE_URL}}/api/tasks/create
{{YOUR_BASE_URL}}/api/tasks/update/1
{{YOUR_BASE_URL}}/api/tasks/task-status-update/3

when those route are hit then check open in browser this URL {{YOUR_BASE_URL}} and open the inspect and then open in console then see the real time data fetching



    

    



