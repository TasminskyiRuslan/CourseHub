# 🎓 CourseHub API

A production-style **Learning Management System (LMS)** RESTful API built with **Laravel 12**.

The project demonstrates modern backend development practices, including role-based authorization, Stripe payment integration, DTO-driven validation, clean architecture, Dockerized development, and comprehensive API testing.

---

## ✨ Highlights

- RESTful API following Laravel best practices
- Token-based authentication with Laravel Sanctum
- Email verification & password recovery
- Role & permission based authorization (Student, Teacher, Admin)
- Stripe Checkout integration with webhook processing
- Polymorphic lesson system (Video, Online, Offline)
- Course publishing workflow
- DTO-based validation using Spatie Laravel Data
- Dynamic filtering & sorting with Spatie Query Builder
- Swagger / OpenAPI documentation
- Redis queues & caching
- Dockerized development environment
- Feature & Unit tests with Pest

---

## 📚 API Modules

```text
🌐 Public
• Browse published courses
• View course details
• Browse teachers
• View teacher profiles

🔐 Authentication
• User registration
• User authentication
• Email verification
• Password recovery
• Session management

👤 Account
• Profile management
• Avatar management

🎓 Student
• Course enrollment
• Access enrolled courses
• Lesson access
• Stripe payment processing

👨‍🏫 Teacher
• Course management
• Lesson management
• Course publication
• Course image management

🛡 Administrator
• User management
• Role management
• Course moderation
• User moderation
```

---

## 💳 Payments

Paid courses are purchased through **Stripe Checkout**.

The payment flow is fully automated:

```text
Paid Course
      │
      ▼
Stripe Checkout
      │
      ▼
Stripe Webhook
      │
      ▼
Enrollment
      │
      ▼
Student gains access to lessons
```

Free courses are enrolled instantly without payment.

---

## 📖 Lesson Types

The platform supports multiple lesson types through Laravel's polymorphic relationships.

- 🎥 Video lessons
- 💻 Online lessons
- 🏫 Offline lessons

---

## 🏗 Architecture

The project follows a layered architecture that separates HTTP handling, business logic, and infrastructure concerns.

- Controllers handle HTTP requests
- Actions encapsulate single business operations
- Services coordinate complex workflows
- DTOs (Spatie Laravel Data) validate and transform input
- Policies & Permissions provide authorization
- API Resources format responses
- Jobs process background tasks
- Events & Listeners handle asynchronous workflows
- Query Builder provides filtering and sorting

---

## 🛠 Tech Stack

| Category | Technology |
|----------|------------|
| Framework | Laravel 12 |
| Language | PHP 8.4 |
| Database | MySQL |
| Cache & Queue | Redis |
| Authentication | Laravel Sanctum |
| Payments | Stripe Checkout |
| API Documentation | Swagger / OpenAPI |
| Testing | Pest |
| Containerization | Docker |
| Web Server | Nginx |
| DTOs | Spatie Laravel Data |
| Filtering | Spatie Query Builder |
| Authorization | Spatie Permission |

---

## 🚀 Getting Started

### Clone the repository

```bash
git clone https://github.com/TasminskyiRuslan/CourseHub.git

cd CourseHub
```

### Configure environment

```bash
cp .env.example .env
```

### Build containers

```bash
docker compose up -d --build
```

### Install dependencies

```bash
docker compose exec app composer install
```

### Generate application key

```bash
docker compose exec app php artisan key:generate
```

### Run migrations & seeders

```bash
docker compose exec app php artisan migrate --seed
```

### Generate Swagger documentation

```bash
docker compose exec app php artisan l5-swagger:generate
```

---

## 📄 API Documentation

Swagger documentation is available after starting the application.

```
http://localhost/api/documentation
```

---

## 🧪 Running Tests

Run the complete test suite:

```bash
docker compose exec app php artisan test
```

---

## 📜 License

This project is licensed under the MIT License.
