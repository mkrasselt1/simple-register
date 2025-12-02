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

The main register interface allows you to:
- Click product buttons to add items to the current bill
- View the running total
- Adjust button size using the layout controls (saved to localStorage)
- Process payments via Cash or Card buttons
- Clear the current bill

## Configuration

Edit `config.php` to change:
- Authentication credentials (`AUTH_USER`, `AUTH_PASS`)
- Data directory location

## Data Storage

All data is stored in JSON files in the `data/` directory:
- `articles.json` - Product/article definitions
- `transactions.json` - Payment transaction logs

## Security Notes

- Change the default credentials in `config.php` before deploying
- For production use, consider using HTTPS
- The `data/` directory should not be publicly accessible in production

## Requirements

- PHP 7.0 or higher
- No database required (uses JSON file storage)
