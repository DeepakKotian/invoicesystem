Invoice System - Laravel 11 Project Setup with MySQL

This project is built using Laravel 11 and MySQL. Follow the instructions below to clone the repository, set up the environment, and run the project using Artisan commands.

Prerequisites
-------------
Before setting up the project, ensure you have the following installed on your machine:

- PHP >= 8.1
- Composer
- MySQL
- Git

Getting Started
---------------
1. Clone the Repository

Start by cloning the project repository to your local machine:

git clone https://github.com/DeepakKotian/invoicesystem.git
cd invoicesystem

2. Install Dependencies

Run Composer to install the PHP dependencies:

composer install

3. Set Up Environment Variables

Copy the .env.example file to create a new .env file:

cp .env.example .env

Now, update the .env file with your MySQL database connection details:

  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=invoicesystem
  DB_USERNAME=root
  DB_PASSWORD=

4. Import MySQL Dump (Database Setup)

You can import the provided MySQL dump file to set up the database:

mysql -u root -p invoicesystem < invoicesystem.sql

OR

Migrate the Database

Run the migration to set up the database schema required data:

  php artisan migrate 

5. Serve the Application

Start the Laravel development server by running:

  php artisan serve

The application will be accessible at http://127.0.0.1:8000/api/.

Swagger Documentation is available at http://127.0.0.1:8000/api/documentation.

Collection file name - API-Collections_2024-11-13

Artisan Commands
----------------
Here are some useful Artisan commands:

- To run database migrations:
    php artisan migrate

- To run the application tests:
    php artisan test

- To list all available Artisan commands:
    php artisan list

References
----------
- MySQL Dump: The project includes a MySQL dump file to set up the database schema and initial data.
- Collections: For additional functionality, refer to the collections file (API-Collections_2024-11-13).
