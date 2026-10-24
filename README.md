# Semprong Shop

An online shop for semprong, the traditional Indonesian rolled wafer. Customers browse the flavours, order and check out; an admin manages products and confirms orders.

![The shop's home page](docs/screenshot.webp)

I first built it in 2021 as SemprongLoves on Laravel 9. In 2026 I brought it up to date: Laravel 13 and Livewire 4, a new interface in Tailwind CSS 4, admin pages properly protected, tests for the main flows, and a container setup for Podman.

## Features

**For customers**

- Browse and search products, with each product's flavour, price and stock
- Add a product to an order in the quantity they want, and remove items
- Check out with a delivery address and phone number
- See past orders
- Register and sign in

**For the admin**

- A dashboard with the number of orders and customers, and the customer list
- Incoming orders, confirmed one by one
- Add products with a photo, and change their stock and price
- Order history

The admin pages sit behind their own sign-in and middleware, which also covers Livewire's actions on those pages, so an action can't run after the admin signs out.

## Stack

Laravel 13 and Livewire 4 on PHP 8.4, MariaDB 10.11, Tailwind CSS 4 built with Vite 8. The container image is built in three stages: Node 24 builds the assets, Composer installs the dependencies, and PHP 8.4 with Apache serves the app.

## Running it with Podman

```sh
cp .env.podman.example .env.podman
```

Fill in `APP_KEY` in `.env.podman`; the file shows the command that generates one. Then:

```sh
podman compose up -d
```

The shop runs at http://localhost:8090 (set `APP_PORT` to change it). On start, the container waits for the database, runs the migrations and seeds the admin account from `database/seeders/AdminSeeder.php`. The admin signs in at `/admin/login`.

`podman compose --profile tools up -d` also starts phpMyAdmin at http://localhost:8091.

## Tests

With PHP 8.4 (including the `pdo_sqlite` extension) and Composer:

```sh
composer install
cp .env.example .env
php artisan key:generate
php artisan test
```

The tests run against an in-memory SQLite database. They cover the public pages, registration and sign-in, product search, the order flow through to checkout (including phone number validation), the admin's product and order screens, keeping customers and signed-out visitors away from the admin pages, and the rupiah and date formatting.
