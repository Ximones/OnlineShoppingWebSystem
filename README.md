# Online Shopping System Template

This template follows the course conventions (custom PHP + jQuery, reusable helpers, no external frameworks). It ships with ready-to-use modules so you can focus on enhancing features instead of wiring the basics.

## Features

- Security: login, logout, registration, hashed passwords, password reset tokens.
- User profile: update info, change password, upload avatar.
- Member maintenance: admin search/list/detail/register.
- Product maintenance: admin listing, filters, CRUD with photo upload.
- Shopping experience: catalog, search, product details, cart, checkout.
- Orders: member history & detail, admin listing.
- Shared helpers for HTML, validation, database, authentication and uploads.

## Project Structure

```
app/
  bootstrap.php         # Loads config, libs, autoloader, router
  core/                 # Base controller + router
  controllers/          # Feature controllers
  models/               # PDO models per table
  lib/                  # Helpers (html, validation, auth, upload)
  views/                # Layout + module views
config/
  app.php               # App constants, roles
  database.php          # PDO connection helper
database/
  schema.sql            # Tables & relationships
  sample_data.sql       # Demo records
public/
  css/main.css          # Lightweight styling
  js/app.js             # jQuery interactions
  uploads/              # User/product photos
```

## Getting Started

1. Create (or reuse) the `test1` database described in `1.txt`:
   ```sql
   CREATE DATABASE IF NOT EXISTS test1 CHARACTER SET utf8mb4;
   ```
2. Update `config/database.php` only if your credentials differ from `root`/empty password.
3. Import schema and sample data (now synced with the structure and mock data from `1.txt`):
   ```bash
   mysql -u root -p test1 < database/schema.sql
   mysql -u root -p test1 < database/sample_data.sql
   ```
4. Serve the project (built-in server shown here):
   ```bash
   php -S localhost:8000
   ```
5. Login credentials:
   - Admin: `john.smith@email.com` / `password`
   - Member: `sarah.lee@email.com` / `password`

## Next Steps

- Extend validation rules inside `app/lib/validation.php`.
- Enforce CSRF using `csrf_token()` and `csrf_verify()` in forms.
- Add more modules (reports, promotions, etc.) by copying the pattern used here.
- Replace placeholder product photos with uploads saved under `public/uploads`.

Happy building!

