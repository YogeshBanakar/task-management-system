Task Management System (Laravel 10)

A simple task management application built with Laravel 10.
The system includes three roles — Admin, Manager and Employee — each with different access levels for creating, assigning and viewing tasks.

Features

Role-based access (Admin, Manager, Employee)

Task creation, assignment and soft deletion

Task visibility controlled by user role

Server-side DataTables

AJAX-based Create/Edit/Delete

Activity logging with Laravel Auditing

Bootstrap 5 interface

How to Run the Project

Clone the repository
git clone https://github.com/YogeshBanakar/task-management-system.git

Install dependencies
composer install
npm install

Create the environment file
cp .env.example .env

Update database details inside .env as per your local setup

Generate application key
php artisan key:generate

Publish auditing migrations
php artisan vendor:publish --provider="OwenIt\Auditing\AuditingServiceProvider" --tag="migrations"

Run all migrations
php artisan migrate

Seed the database (required before login)
php artisan db:seed
This will create roles and sample users.

Start the application
php artisan serve
npm run dev

Login Credentials (after seeding)

Admin: admin@example.com / 12345678

Manager: manager@example.com / 12345678

Employee 1: employee1@example.com / 12345678

Employee 2: employee2@example.com/ 12345678

Notes

The project runs fully using migrations + seeders.

SQL export file is also included for backup.

Tasks, roles and permissions are handled through Laravel Policies.

Author: Yogesh Banakar