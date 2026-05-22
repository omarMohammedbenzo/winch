# WINCH — Order Assignment System

A real-time order-to-driver assignment system built with **Laravel 12** and **Vue.js**, following a strict **Domain-Driven Design (DDD)** layout.

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

### Stage 1 — Scaffold
Laravel 12 (12.60.2) on PHP 8.2.12. Git initialized and pushed.

### Stage 2 — DDD structure
Enforced the mandated `src/` layout with two bounded contexts and one presentation cpanel:

```
src/
├── Domain/
│   ├── Orders/    (Actions, Contracts, Services, DataTransferObjects,
│   └── Drivers/    Enums, Exceptions, Models/{Entities,Scopes,Abilities,
│                   Traits}, Providers, Observers, Traits)
└── Presentation/
    └── Admin/     (Controllers, Requests, Providers, Resources, Routes, Views)
```

### Stage 3 — Database schema
Migrations for `drivers` and `orders`.

- **Coordinates as `DECIMAL(10,7)`** (source of truth) instead of a spatial `POINT`. Nearest-driver search runs a **bounding-box pre-filter** on indexed `latitude`/`longitude`, then an exact `ST_Distance_Sphere()` on the survivors — fast, portable, and works identically on MySQL 8 and MariaDB 10.4 without a spatial extension. (At larger scale: a `POINT` + `SPATIAL INDEX`, or PostGIS.) check https://postgis.net/docs/manual-1.4/ST_Distance_Sphere.html

- `drivers.current_order_id` — denormalized pointer to the active order, so "driver has no active order" is an O(1).

### Stage 4 — Domain core (models, enums, DTOs, contracts)
- **Enums** carry the rules: `OrderStatus` (`canBeAssigned()`, `occupiesDriver()`, `active()`), `DriverStatus` (`canAcceptOrder()`).

- **Models** (`Order`, `Driver`) live under `Domain/{Context}/Models/Entities`, with cast enums and **explicit local query scopes** — `Driver::assignable()`, `Driver::withinBoundingBox()`, `Order::active()`. Local scopes (not global scopes) so rows are never hidden implicitly.

- **`DriverFinder` contract** is the Drivers domain's only public gateway: primitives in, a `NearestDriver` DTO out. The Orders domain depends on this interface, never on the `Driver` model — enforcing the domain boundary.

### Stage 5 — Nearest-driver finder
`NearestAvailableDriverFinder` implements `DriverFinder`, bound to the interface in `DriversServiceProvider`.

- Two-step geo search: indexed **bounding-box** pre-filter → `ST_Distance_Sphere()` ranking → nearest first.
- Only `assignable()` drivers (available + no active order) are considered; busy or already-assigned drivers are excluded even if physically closer.

