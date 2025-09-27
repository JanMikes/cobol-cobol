# COBOLCOBOL - SaaS Subscription Platform

## Project Overview
COBOLCOBOL is a demonstration SaaS application that showcases subscription-based content access using modern web technologies. The platform allows users to subscribe to premium content, manage their subscriptions, and access exclusive articles.

## Technology Stack
- **Framework**: Symfony 7 (PHP 8.4)
- **Database**: PostgreSQL 16
- **Payment Processing**: Stripe API
- **Containerization**: Docker & Docker Compose
- **Email Testing**: Mailpit
- **Static Analysis**: PHPStan (Level 8)
- **Web Server**: FrankenPHP (Production Dockerfile available)

## Architecture
The application follows Symfony best practices with a clean architecture:
- Service layer for business logic
- Repository pattern for data access
- Security voters for authorization
- Event subscribers for Stripe webhooks
- FrankenPHP for high-performance serving
- Docker containers for all services

## Key Features
1. **Authentication System**
   - User registration with email verification
   - Secure login/logout
   - Password reset functionality
   - Session management

2. **Subscription Management**
   - Multiple subscription tiers (Basic/Premium)
   - Stripe integration for payments
   - Customer portal for self-service
   - Automatic subscription status updates via webhooks

3. **Content Access Control**
   - Public content available to all visitors
   - Premium content restricted to subscribers
   - Role-based access control (ROLE_USER, ROLE_SUBSCRIBER)

4. **Pages**
   - Landing page with marketing content
   - Pricing page with plan comparison
   - Private content area for subscribers
   - User dashboard for account management
   - Authentication pages (sign in/up)

## Development Commands

### Initial Setup
```bash
# Start all services
docker compose up -d

# Install dependencies
docker compose exec web composer install

# Run database migrations
docker compose exec web bin/console doctrine:migrations:migrate

# Load sample data
docker compose exec web bin/console doctrine:fixtures:load
```

### Daily Development
```bash
# Run PHPStan analysis
docker compose exec web vendor/bin/phpstan analyse

# Clear cache
docker compose exec web bin/console cache:clear

# Watch logs
docker compose logs -f web

# Access Symfony console
docker compose exec web bin/console
```

### Testing
```bash
# Run all tests
docker compose exec web bin/phpunit

# Run specific test suite
docker compose exec web bin/phpunit --testsuite=unit
```

## Environment Variables
Key environment variables to configure:
- `DATABASE_URL`: PostgreSQL connection string
- `STRIPE_SECRET_KEY`: Stripe API secret key
- `STRIPE_PUBLIC_KEY`: Stripe publishable key
- `STRIPE_WEBHOOK_SECRET`: Webhook endpoint secret
- `MAILER_DSN`: Mailpit SMTP configuration
- `APP_SECRET`: Symfony application secret
- `SERVER_NAME`: FrankenPHP server configuration (default: :80)

## Stripe Configuration
1. Create products and prices in Stripe Dashboard
2. Configure webhook endpoint: `/stripe/webhook`
3. Listen for events: `customer.subscription.created`, `customer.subscription.updated`, `customer.subscription.deleted`

## Security Considerations
- All sensitive data stored in environment variables
- CSRF protection enabled on forms
- Password hashing using Symfony's auto hasher (bcrypt)
- HTTPS enforced in production
- Session cookies with secure flags
- FrankenPHP provides built-in security features

## Directory Structure
```
/
├── src/
│   ├── Controller/     # HTTP request handlers
│   ├── Entity/         # Doctrine entities
│   ├── Repository/     # Data access layer
│   ├── Service/        # Business logic
│   ├── Security/       # Voters and authenticators
│   └── EventListener/  # Stripe webhook handlers
├── templates/          # Twig templates
├── config/            # Symfony configuration
├── public/            # Web root
└── migrations/        # Database migrations
```

## Maintenance Tasks
- Monitor Stripe webhook failures in dashboard
- Regular security updates via `composer update`
- Database backups (automated via Docker volumes)
- Log rotation configured in FrankenPHP
- Monitor application performance via FrankenPHP metrics

## Testing Credentials
For local development with Stripe test mode:
- Test card: 4242 4242 4242 4242
- Any future expiry date
- Any 3-digit CVC
- Any billing ZIP code

## Support & Documentation
- Symfony Documentation: https://symfony.com/doc/current/
- Stripe PHP SDK: https://stripe.com/docs/api/php
- Docker Compose: https://docs.docker.com/compose/
- PHPStan: https://phpstan.org/user-guide/getting-started
- FrankenPHP Documentation: https://frankenphp.dev/