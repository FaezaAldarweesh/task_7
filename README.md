## Task Management System

This repository contains a Task Management System built with Laravel. It allows managing tasks within a project, handling task dependencies, file attachments, task statuses. The system is designed to facilitate project management by organizing tasks based on roles and dependencies

## Features
- Task Creation and Management: Create tasks with statuses like open, blocked, and completed.
- Task Dependencies: Define task dependencies, ensuring tasks are opened or blocked based on the completion of their dependent tasks.
- Task Status Updates: Automatically update task statuses based on dependencies, with an efficient system to track completion and status reversion.
- File Attachments: Attach files to tasks and manage file updates with seamless replacements of old files.
- Caching: Caching mechanisms are implemented for task creation, updating, and retrieval functions to improve performance.

## Installation
- git clone https://github.com/FaezaAldarweesh/task_7.git
- composer install
- cp .env.example .env
- php artisan key:generate
- php artisan migrate
- php artisan db:seed
- php artisan serve

  ## postman
  - documentation link : https://documenter.getpostman.com/view/34467473/2sAXxWYTiz
