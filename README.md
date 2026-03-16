# Cafetria System

<p align="center">
  <strong>Role-based cafeteria ordering system for admins and employees.</strong><br />
  Built with PHP, MySQL, Tailwind CSS, and deterministic seed data for quick local setup.
</p>

---

## Overview

The Cafetria System helps cafeteria teams manage products, users, orders, and spending checks from one workspace.  
Employees can browse the menu, place orders, and track their requests, while admins can manage the operational flow through dedicated dashboard screens.

## Core Features

- Admin authentication and customer authentication
- User CRUD for employee accounts
- Product CRUD with availability control
- Admin order pipeline management
- Manual order creation for employees
- Customer order creation and order tracking
- Checks page with filters and spending summaries
- Database seeding for a ready-to-run demo

## Tech Stack

- PHP 8.1+
- MySQL
- Composer
- Tailwind CSS via CDN
- Plain JavaScript for active cart interactions

## Project Structure

```text
.
├── admin/                # Admin dashboard pages
├── customer/             # Customer-facing pages
├── controllers/          # Business logic and request handlers
├── database/seeds/       # Deterministic seed data
├── includes/             # Shared bootstrap and layout includes
├── scripts/              # CLI helpers such as the seeder
├── src/Support/          # Environment and database helpers
└── assets/               # CSS, images, and active frontend scripts
```

## Seeder Credentials

Use these accounts after running the database seeder:

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@company.com` | `Admin@123` |
| Customer | `employee@company.com` | `Employee@123` |
| Customer | `mariam@company.com` | `Employee@123` |
| Customer | `omar@company.com` | `Employee@123` |

## How To Run

### 1. Install dependencies

```bash
composer install
```

### 2. Create your `.env` file

Add a root `.env` file with your local database settings:

```env
APP_ENV=local
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cafetria_system
DB_USERNAME=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
```

### 3. Create the database

Create a MySQL database that matches `DB_DATABASE` in your `.env`.

### 4. Import the project schema

Import ``` Cafetria.sql ``` SQL schema into the database before seeding.  

### 5. Seed the demo data

```bash
composer db:seed
```

If you are running outside a local/testing environment:

```bash
composer db:seed:force
```

### 6. Start the PHP server

```bash
php -S localhost:8000
```

Then open:

```text
http://localhost:8000
```

## Demo Flow

1. Log in with one of the seeded accounts.
2. Use the admin account to manage users, products, checks, and order states.
3. Use a customer account to browse products, place orders, and track order history.

## Team Members

| Team Member | Contribution |
| --- | --- |
| Nooreldeen Ayman Elmobashar | Extracting system requirement, User CRUD, and user frontend files in the admin folder |
| Abdelfatah Shakal | Product CRUD and products frontend files in CRUD |
| Mohamed Abdelshakor | Authentication, login frontend file, and pagination across the project |
| Mohamed Ibrahim Mostafa | Database ERD, admin dashboard orders CRUD, and its frontend files |
| Mohamed Abdelmonem | Customer dashboard orders CRUD and its frontend files |

## Notes

- The project reads environment variables from the root `.env` file.
- Seed data lives in [`database/seeds/data.php`](database/seeds/data.php).
- The seeder command is defined in [`composer.json`](composer.json).

## Status

This repository is ready for local demo usage after the database schema is imported, environment variables are configured, and the seed command is executed.
