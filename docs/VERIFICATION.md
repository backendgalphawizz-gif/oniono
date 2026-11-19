# Oniono — Plan verification checklist

Reference plan: [oniono_food_delivery_47b9b56f.plan.md](./oniono_food_delivery_47b9b56f.plan.md)

## Build and tests (run locally)

```bash
composer install
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan test
```

## Verified working

| Area | Status |
|------|--------|
| MySQL migrations (core + Sanctum) | OK |
| Admin guard + `/admin/login` | OK |
| Restaurant guard + `/restaurant/login` | OK |
| Bootstrap 5 panel UI (CDN assets, no Node/Vite) + Oniono theme | OK |
| Domain services (order, commission, settlement, delivery assign) | OK |
| Razorpay package + webhook route + COD handler | OK |
| API v1 auth + restaurants/menu + Sanctum | OK |
| PHPUnit (commission, settlement, isolation) | OK |
## Default logins (after seed)

| Panel | Email | Password |
|-------|--------|----------|
| Admin | `admin@oniono.test` | `password` |
| Restaurant | `demo@restaurant.test` | `password` |

## URLs

- **Artisan serve:** `http://127.0.0.1:8000/admin/login`, `/restaurant/login`
- **XAMPP:** `http://localhost/oniono/public/admin/login`, `/restaurant/login`

## Plan gaps (non-blocking / future)

- Forgot-password flows (routes listed in plan, not wired yet)
- Laravel Policy classes (authorization uses controller `abort_if` + middleware)
- Full API cart/checkout/delivery-partner endpoints (stubs only)
- Excel export (`maatwebsite/excel` optional in plan)
- Admin dashboard revenue **area** chart (line + doughnut charts implemented)

## Smoke scripts

```bash
php scripts/check-demo.php
php scripts/test-login.php
```
