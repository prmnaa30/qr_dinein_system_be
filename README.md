# QR Dine-in System

A Laravel-based restaurant management system that allows customers to scan QR codes to view menus, place orders, and pay directly from their mobile devices. This system includes real-time order tracking and payment integration.

## Features

- QR code table identification
- Real-time order status updates
- Online payment integration (Midtrans)
- Admin dashboard for order management
- Customer-facing interface
- Real-time notifications using WebSockets

## Requirements

- PHP >= 8.1
- Composer
- Node.js and npm
- MySQL or PostgreSQL
- Redis (for caching and queues)

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/prmnaa30/qr_dinein_system_be.git
   cd qr_dinein_system_be
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install Node.js dependencies:
   ```bash
   npm install
   ```

4. Copy the environment file and configure your settings:
   ```bash
   cp .env.example .env
   ```

5. Generate application key:
   ```bash
   php artisan key:generate
   ```

6. Configure your database settings in the `.env` file, then run migrations:
   ```bash
   php artisan migrate
   ```

7. Seed the database with initial data (optional):
   ```bash
   php artisan db:seed
   ```

## Running the Application

1. Start the Laravel development server:
   ```bash
   php artisan serve
   ```

2. Compile frontend assets:
   ```bash
   npm run dev
   ```

3. **Running Reverb (WebSocket Server):**
   To enable real-time features like order status updates, you need to run the Reverb WebSocket server:
   
   ```bash
   php artisan reverb:start
   ```
   
   Or in development mode:
   ```bash
   php artisan reverb:start --debug
   ```

## Environment Configuration

Make sure to set these variables in your `.env` file:

- `DB_CONNECTION` - Database connection type
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` - Database credentials
- `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY` - Payment gateway credentials
- `BROADCAST_DRIVER` - Set to `reverb` for real-time updates
- `CACHE_DRIVER` - Set to `redis` for optimal performance
- `QUEUE_CONNECTION` - Set to `redis` for background jobs

## API Documentation

API documentation is available via Scribe. Access it at `/docs` endpoint after running the application.

## Contributing

Thank you for considering contributing to the QR Dine-in System! Feel free to submit issues and enhancement requests.

## License

This project is open-sourced software licensed under the MIT license.
