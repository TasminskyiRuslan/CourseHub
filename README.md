# CourseHub API 🎓

![Laravel](https://img.shields.io/badge/Laravel-12.x-red)
![PHP](https://img.shields.io/badge/PHP-8.4-blue)
![Swagger](https://img.shields.io/badge/Docs-Swagger-green)
![Testing](https://img.shields.io/badge/Tests-Pest-purple)
![License](https://img.shields.io/badge/license-MIT-brightgreen)

**CourseHub** is a robust LMS (Learning Management System) RESTful API built with Laravel.
It handles course creation, lesson management, user authentication with email verification, and uses a modern Docker-based infrastructure.

---

## 🛠 Tech Stack

* **Framework:** Laravel 12
* **Language:** PHP 8.4
* **Database:** MySQL 8.0
* **Cache & Queue:** Redis 7
* **Server:** Nginx (Alpine)
* **API Docs:** L5-Swagger (OpenAPI 3)
* **Testing:** Pest PHP
* **Utilities:**

    * Spatie Query Builder
    * Spatie Data
    * Spatie Sluggable

---

## 🐳 Prerequisites

Ensure you have installed:

* Docker
* Docker Compose

---

## 🚀 Installation & Setup

### 1. Clone repository

```bash
git clone https://github.com/TasminskyiRuslan/CourseHub.git
cd CourseHub
```

### 2. Configure environment

```bash
cp .env.example .env

# Sync your local user ID with Docker to avoid permission issues
echo "UID=$(id -u)" >> .env
echo "GID=$(id -g)" >> .env
```

The default configuration works out-of-the-box with Docker.

### 3. Start containers

```bash
docker compose up -d --build
```

### 4. Install dependencies

```bash
docker compose exec app composer install
```

### 5. Setup application

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

### 6. Generate Swagger documentation

```bash
docker compose exec app php artisan l5-swagger:generate
```

---

## 📚 API Documentation

Swagger / OpenAPI documentation is available once the server is running.

👉 **[View API Documentation](http://localhost/api/documentation)**

---

## 🧪 Running Tests

Run full test suite:

```bash
docker compose exec app php artisan test
```

---

## ✨ Key Features

### Authentication & User Management

* **Sanctum:** Token-based authentication (Bearer)
* **Flow:** Register, Login, Logout, Password Reset
* **Verification:** Email verification required for specific actions (verified middleware)
* **Admin:** Protected role management via Seeders

### Courses

* **CRUD:** Create, Read, Update, Delete courses
* **Publishing:** Unpublish/Publish workflow
* **Media:** Dedicated endpoints for course image management
* **Filtering:** Advanced filtering using `spatie/laravel-query-builder`
* **Slugs:** SEO-friendly URLs via `spatie/laravel-sluggable`

### Lessons

* Full CRUD for lessons nested within courses
* Optimized for course structure management

---

## 📂 Project Structure

```text
app/Http/Controllers/Api   # Handles API requests and returns responses
app/Models                 # Contains all Eloquent models
app/Http/Resources/Api     # API Resources for output formatting
app/Data                   # Defines structured input data for the API (DTOs)
app/Actions                # Performs single operations and business logic
app/Services               # Reusable services for complex logic
app/Policies               # Authorization logic / Permissions
app/Notifications          # Email and System notifications
app/Swagger/               # Swagger annotations and definitions
database/factories         # Model factories for testing
database/migrations        # Database structure changes
database/seeders           # Initial data population
routes/api.php             # API Routes definitions
docker/                    # Docker configuration files
tests/                     # Feature and Unit tests (Pest)
```

---

## 🔑 Default Credentials

Running migrations with seeders creates a default **Admin** user (not available via registration):

| Role  | Email                                             | Password |
| ----- | ------------------------------------------------- | -------- |
| admin | [admin@coursehub.com](mailto:admin@coursehub.com) | secret   |

> ⚠ Registration is available only for **STUDENT** and **TEACHER** roles.

---

## 📊 Database Design

### Entities

* **User:** Registered user with role (`STUDENT`, `TEACHER`, `ADMIN`) and mandatory email verification.
* **Course:** Educational course created by a Teacher (author).
* **Lesson:** Course lesson with ordering (`position`) and polymorphic content.
* **OnlineLesson:** Scheduled online lesson with meeting link.
* **OfflineLesson:** Scheduled physical lesson with address and room number.
* **VideoLesson:** Pre-recorded video lesson with provider information.

---

### Relations

* **User (TEACHER)** `hasMany` **Course** (as author)
* **Course** `belongsTo` **User** (author)
* **Course** `hasMany` **Lesson**
* **Lesson** `belongsTo` **Course**
* **Lesson** `morphTo` **Lessonable**
* **OnlineLesson** `morphOne` **Lesson**
* **OfflineLesson** `morphOne` **Lesson**
* **VideoLesson** `morphOne` **Lesson**

---

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/license/MIT).
