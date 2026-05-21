# WINCH — Order Assignment System

A real-time order-to-driver assignment system built with Laravel 12 and Vue.js, following a strict Domain-Driven Design (DDD) layout.

## Tech Stack & Why

| **Backend** | Laravel 12 | Latest stable version of laravel
| **PHP** | 8.2.12 
| **Database** | MySQL 8 (target) / MariaDB 10.4 (local dev)
| **Frontend** | Vue.js (Composition API) 


## Requirements

- PHP 8.2+
- Composer 2.x
- Node.js 18+
- MySQL 8 (or MariaDB 10.4+)

## Setup

```bash
git clone https://github.com/omarMohammedbenzo/winch.git
cd winch

composer install
cp .env.example .env
php artisan key:generate

php artisan migrate --seed

npm install
npm run dev

php artisan serve
```

