# Database Seed Data

This folder contains deterministic seed data for local/testing usage.

## Files

- `data.php`: Seed records for `categories`, `products`, `users`, and sample `orders`.
- `scripts/seed.php`: CLI seeding runner.

## Run

```bash
composer db:seed
```

If `APP_ENV` is not local/testing, run with explicit override:

```bash
composer db:seed:force
```

## Seeded credentials

- Admin: `admin@company.com` / `Admin@123`
- Customer: `employee@company.com` / `Employee@123`
- Customer: `mariam@company.com` / `Employee@123`
- Customer: `omar@company.com` / `Employee@123`
