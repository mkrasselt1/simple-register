# Simple Register

A simple web-based cash register system that can be hosted using PHP's built-in server.

## Features

- **HTTP Basic Authentication** - Secure access to the register
- **Admin Panel** - Create, edit, and delete articles/products
- **Cash Register Interface** - User-friendly register view with:
  - Configurable product buttons with custom colors
  - Current bill display with item list
  - Running total calculation
  - Cash and Card payment options with logging
  - Layout saved to browser's localStorage
- **Transaction Logging** - All payments are logged with timestamp, items, and payment method
- **MVC Architecture** - Clean separation of concerns with controllers and views

## Project Structure

```
simple-register/
├── index.php              # Main entry point
├── admin.php              # Admin panel entry
├── register.php           # Cash register entry
├── config.php             # Configuration settings
├── auth.php               # Authentication handler
├── articles.php           # Article model/data functions
├── transactions.php       # Transaction model/data functions
├── api/
│   └── payment.php        # Payment API endpoint
├── controllers/
│   ├── HomeController.php
│   ├── AdminController.php
│   ├── RegisterController.php
│   └── PaymentController.php
├── views/
│   ├── home.php           # Home page template
│   ├── admin.php          # Admin panel template
│   └── register.php       # Cash register template
├── core/
│   ├── Controller.php     # Base controller class
│   └── View.php           # View/template engine
└── data/                  # JSON data storage (gitignored)
  ├── articles.json
  └── transactions/      # Each transaction as a single file (timestamp_id.json)
```

## Quick Start

1. Clone or download this repository
2. Navigate to the project directory
3. Start the PHP built-in server:

```bash
php -S localhost:8000
```

4. Open your browser and go to `http://localhost:8000`
5. Log in with the default credentials:
   - Username: `admin`
   - Password: `register123`

## Usage

### Admin Panel

Access the admin panel to manage your articles/products:

- Add new articles with name, price, and button color
- Edit existing articles
- Delete articles

### Register View

The main register interface is fully touch-optimized and offers:

- Large, finger-friendly product buttons (responsive grid)
- Drag-and-drop and resize of product buttons (auch per Touch)
- Layout-Editor mit serverseitiger Speicherung (Layouts bleiben auf allen Geräten erhalten)
- Click or tap product buttons to add items to the current bill
- View the running total and all items in the bill
- Process payments via Cash or Card buttons (each payment is logged as a separate file)
- Clear the current bill with one tap
- Pull-to-Refresh is blocked on most mobile browsers for uninterrupted operation

### Report View

The report view provides:

- Filtering of transactions by date range
- Overview of total transactions and total revenue
- Revenue breakdown by payment method (cash, card, ...)
- Article statistics: quantity sold and revenue per product
- List of all recent transactions with details (date, items, amount, payment method, layout)
- All data is based on the new single-file-per-transaction storage for maximum reliability

## Architecture

The application follows an MVC-like pattern:

- **Controllers** (`controllers/`) - Handle request logic and coordinate between models and views
- **Views** (`views/`) - Template files for rendering HTML
- **Models** (`articles.php`, `transactions.php`) - Data access and business logic
- **Core** (`core/`) - Base classes for the framework (View engine, base Controller)

### Data Storage

All data is stored in JSON files in the `data/` directory:

- `articles.json` - Product/article definitions
- `transactions/` - Each payment transaction as a separate file (format: `timestamp_id.json`)

## Configuration

Edit `config.php` to change:

- Authentication credentials (`AUTH_USER`, `AUTH_PASS`)
- Data directory location

**Note:** `config.php` is excluded from git commits via `.gitignore`.

## Security & Mobile Notes

- Change the default credentials in `config.php` before deploying
- For production use, consider using HTTPS
- The `data/` directory should not be publicly accessible in production
- `config.php` is excluded from git (see `.gitignore`)
- The register UI is touch-optimized for tablets/kiosks (large buttons, responsive layout)
- Pull-to-Refresh is disabled on most mobile browsers for uninterrupted operation

## Requirements

- PHP 7.0 or higher
- No database required (uses JSON file storage)
