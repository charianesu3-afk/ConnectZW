# ConnectZW

**Discover. Connect. Grow. Zimbabwean Businesses.**

A business directory for Zimbabwe built with PHP and MySQL.

## Features
- **Customers:** search and filter (category, province, city, open now, verified), grid/list views, business pages with Call / WhatsApp / Email / Website buttons, reviews, favourites, customer dashboard
- **Business owners:** listing editor (details, opening hours, services, photos), performance dashboard with views, contact clicks, calls and reviews
- **Admins:** dashboard with charts, manage businesses (verify/delete), categories, users, reviews, blog, reports, newsletter subscribers, password settings
- **Site:** blog, FAQ/help, terms, privacy, pricing, newsletter sign-up
- **Security:** prepared statements, CSRF protection, escaped output, hashed passwords, safe image uploads

## Setup (XAMPP)
1. Copy this folder to `C:\xampp\htdocs\connectzw`
2. Start Apache and MySQL. In phpMyAdmin create a database named `connectzw` and import `database.sql` (existing installs upgrade themselves automatically)
3. Check `includes/config.php`
4. Open `http://localhost/connectzw`
5. Sample data: visit `/seed_demo.php` once (remove later with `/seed_demo.php?remove=1`), then delete the file
6. Make an admin: register, then run `UPDATE users SET role='admin' WHERE email='you@example.com';`

## Photos
- Hero photo: save a wide night-city photo as `assets/images/hero.jpg` (optional, a drawn skyline is used otherwise)
- Category photos: see `assets/images/categories/README.txt`
