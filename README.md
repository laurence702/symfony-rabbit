# Symfony Order Processing API

This is a demo api showcasing Symfony best practices with Docker, RabbitMQ, and Event-driven architecture.

## Features

- RESTful API for order processing
- Event-driven architecture using Symfony Messenger
- Asynchronous processing with RabbitMQ
- Doctrine ORM for database operations
- Dockerized development environment
- Email notifications for orders (using Symfony Mailer)

## Prerequisites

- Docker
- Docker Compose
- Git

## Installation

1. Clone the repository:
```bash
git clone <your-repository-url>
cd <project-directory>
```

2. Create necessary environment files:
```bash
# Copy the environment files
cp .env .env.local
cp .env .env.test

# Update the following variables in .env.local:
# - APP_SECRET (generate a new one)
# - DATABASE_URL (if needed)
# - MESSENGER_TRANSPORT_DSN (RabbitMQ connection)
# - MAILER_DSN (for production, update with real SMTP details)
```

3. Build and start the Docker containers:
```bash
docker-compose up -d --build
```

4. Install dependencies:
```bash
docker-compose exec php composer install
```

5. Create database schema:
```bash
docker-compose exec php php bin/console doctrine:schema:create
```

## Environment Configuration

### Security-Critical Variables
The following environment variables should be properly configured in production:

- `APP_SECRET`: Generate a new one using `openssl rand -hex 32`
- `DATABASE_URL`: Use strong credentials in production
- `MESSENGER_TRANSPORT_DSN`: Use strong RabbitMQ credentials
- `MAILER_DSN`: Configure with your SMTP server details

### Development Environment
- The null mailer is used by default (`MAILER_DSN=null://null`)
- RabbitMQ uses default credentials (change in production)
- MySQL uses default test credentials (change in production)

## Services

- **API**: http://localhost:8080
- **RabbitMQ Management**: http://localhost:15672
  - Username: guest
  - Password: guest (change in production)
- **MySQL**:
  - Host: localhost
  - Port: 3306
  - Database: symfony_demo
  - Username: symfony
  - Password: symfony (change in production)

## Usage

1. The API will be available at `http://localhost:8080`
2. RabbitMQ management interface is available at `http://localhost:15672`
3. Monitor logs:
```bash
docker-compose logs -f
```

### API Endpoints

1. Create Order:
```bash
curl -X POST http://localhost:8080/api/orders \
  -H "Content-Type: application/json" \
  -d '{"email":"customer@example.com","amount":99.99}'
```

2. Get Order Status:
```bash
curl http://localhost:8080/api/orders/{id}
```

## Development

1. Access the PHP container:
```bash
docker-compose exec php bash
```

2. Run tests:
```bash
docker-compose exec php php bin/phpunit
```

3. Clear cache:
```bash
docker-compose exec php php bin/console cache:clear
```

4. Watch message queue:
```bash
# Start the messenger consumer
docker-compose exec php php bin/console messenger:consume async -vv
```

5. Run sim
```bash
docker compose exec php php bin/console cache:clear && docker compose exec php php bin/console messenger:consume async -vv & curl -X POST http://localhost:8080/api/orders -H "Content-Type: application/json" -d '{"email":"test@example.com","amount":99.99}'
```

## Project Structure

```
.
├── config/                 # Symfony configuration
├── docker/                # Docker configuration files
├── public/                # Web root directory
├── src/                   # Application source code
│   ├── Controller/       # API Controllers
│   ├── Entity/           # Doctrine entities
│   ├── Event/            # Event classes
│   ├── Message/          # Message classes for async processing
│   ├── MessageHandler/   # Message handlers
│   ├── Email/           # Email templates
│   └── Service/         # Application services
└── tests/                # Test files
```

## Security Considerations

1. Environment Files:
   - Never commit `.env`, `.env.local`, or any other environment files
   - Keep sensitive credentials out of Docker Compose files
   - Use secrets management in production

2. Production Setup:
   - Change all default credentials
   - Use HTTPS for API endpoints
   - Configure proper CORS settings if needed
   - Use proper SMTP configuration for emails
   - Set up proper RabbitMQ access control

3. Monitoring:
   - Monitor RabbitMQ queue size
   - Set up proper logging for failed messages
   - Monitor email sending status

## Troubleshooting

1. If you encounter permission issues:
```bash
docker compose exec php chown -R www-data:www-data /var/www/html
```

2. If the database connection fails:
```bash
docker compose exec php php bin/console doctrine:database:create
```

3. To restart all services:
```bash
docker compose restart
```

4. If RabbitMQ connection fails:
```bash
# Check RabbitMQ status
docker compose exec rabbitmq rabbitmqctl status

# Restart RabbitMQ
docker compose restart rabbitmq
```

## Contributing

1. Create a new branch
2. Make your changes
3. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details 