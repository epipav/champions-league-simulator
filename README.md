# Champions League Simulator

A football league simulation application with Laravel backend and Vue.js frontend.

## Quick Start with Docker

### Prerequisites

- Docker
- Docker Compose

### Installation

1. **Clone the repository**
```bash
git clone <repository-url>
cd champions-league
```

2. **Build and start the containers**
```bash
docker compose up -d --build
```

This will:
- Build the Laravel backend and Vue.js frontend images
- Start PostgreSQL database
- Run database migrations
- Start nginx web server
- Start Vue.js development server

App will be available at [http://localhost:3000](http://localhost:3000)

3. **Verify the setup**
```bash
# Check containers are running
docker compose ps

# View logs
docker compose logs -f backend
```

4. **Access the API**
- API URL: http://localhost:8000

## Docker Commands

### Development

```bash
# Start containers
docker compose up -d

# Stop containers
docker compose down

# Rebuild containers
docker compose up -d --build

# Fresh start
docker compose down -v
docker compose up -d --build
```

### Artisan Commands

```bash
# Run migrations
docker compose exec backend php artisan migrate

# Run seeders
docker compose exec backend php artisan db:seed

# Run tests
docker compose exec backend php artisan test

# Clear cache
docker compose exec backend php artisan cache:clear

# List routes
docker compose exec backend php artisan route:list
```

## Project Structure

```
.
├── docker compose.yml          # Docker orchestration
├── backend/                    # Laravel API
│   ├── Dockerfile             # Backend container definition
│   ├── app/
│   │   ├── Models/            # Eloquent models
│   │   ├── Services/          # Business logic
│   │   └── Http/
│   │       └── Controllers/   # API controllers
│   ├── config/
│   │   └── league.php         # League configuration (teams, rules)
│   ├── database/
│   │   ├── migrations/        # Database schema
│   │   ├── seeders/           # Data seeders
│   │   └── factories/         # Model factories
│   ├── tests/                 # PHPUnit tests
│   └── docker/
│       ├── nginx/             # Nginx configuration
│       ├── entrypoint.sh      # Container startup script
│       └── scripts/           # Helper scripts
└── frontend/                   # Vue.js SPA (coming soon)
```

## Testing

```bash
# Run all tests
docker compose exec backend php artisan test

# Run specific test
docker compose exec backend php artisan test --filter=FixtureGeneratorServiceTest

# Run with coverage
docker compose exec backend php artisan test --coverage
```

## Configuration

### Environment Variables

The backend uses [.env.docker](backend/.env.docker) for Docker environment

### League Configuration

Teams and simulation settings are defined in [backend/config/league.php](backend/config/league.php).

## API Documentation

See [API_DOCUMENTATION.md](API_DOCUMENTATION.md) for complete API reference.

