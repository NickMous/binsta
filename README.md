# Binsta

A modern Instagram-like social media application built with a custom PHP framework and Vue.js frontend. Binsta combines traditional PHP backend handling with modern Vue.js SPA architecture for a seamless user experience.

## Features

- **User Management**: Registration, authentication, profile management with profile pictures
- **Social Features**: Follow/unfollow users, personal feed, user discovery
- **Posts**: Create, edit, view posts with image uploads
- **Interactions**: Like posts, comment on posts with real-time updates
- **Search**: Find users and posts across the platform
- **Theme Support**: Dark/light mode theme selection
- **Code Sharing**: Fork functionality for sharing code snippets

## Technology Stack

### Backend (PHP 8.4+)
- **Custom PHP Framework** with advanced routing and dependency injection
- **RedBeanPHP** - Lightweight ORM for database operations
- **Twig** - Template engine (legacy, mostly replaced by Vue)
- **Sentry** - Error tracking and monitoring
- **Whoops** - Development error handling

### Frontend (Vue 3 + TypeScript)
- **Vue 3** with Composition API
- **Vue Router** - Client-side routing
- **Pinia** - State management
- **TypeScript** - Type safety
- **Tailwind CSS** - Utility-first styling
- **Reka UI** - Modern UI components
- **Vite** - Build tool with HMR

### Development & Testing
- **DDEV** - Local development environment
- **Pest** - PHP testing framework (100% coverage required)
- **ESLint & Prettier** - Code quality for frontend
- **PHPStan & PHP CodeSniffer** - Static analysis and code standards
- **Rector** - Automated code refactoring

## Architecture Overview

Binsta uses a **hybrid PHP/Vue.js Single Page Application (SPA)** architecture with a custom routing framework that combines traditional PHP backend handling with modern Vue.js frontend.

### Core Components

**1. Custom PHP Framework (`src/Internals/`)**
- **ControllerService**: Advanced routing engine with regex pattern matching
- **ViteService**: Integrates Vue.js components with PHP backend
- **Route System**: Type-safe route definitions with parameter extraction
- **Dependency Injection**: Container-based dependency management

**2. Dual Routing System**
- **API Routes** (`routes/api.php`): PHP handles API endpoints with parameter extraction
- **Web Routes** (`routes/web.php`): SPA catch-all serves Vue app for frontend routing
- **Priority**: API routes processed first, then SPA catch-all

**3. Request Flow**
1. All requests hit `public/index.php`
2. Kernel initializes framework (database, session, error handling)
3. ControllerService loads both API and web routes
4. Route matching: Exact match first, then regex pattern matching
5. Response handling: API routes → JSON, Web routes → Vue SPA

## Quick Start

### Prerequisites

- **DDEV** - Local development environment manager
- **Bun** - JavaScript package manager and runtime
- **PHP 8.4+** - Backend runtime
- **Composer** - PHP dependency manager

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd binsta
   ```

2. **Start DDEV environment**
   ```bash
   ddev start
   ```

3. **Install PHP dependencies**
   ```bash
   ddev composer install
   ```

4. **Install JavaScript dependencies**
   ```bash
   bun install
   ```

5. **Set up environment**
   ```bash
   # Copy appropriate environment file
   cp .env.ddev .env
   ```

6. **Run database migrations/seeder**
   ```bash
   ddev php seeder.php
   ```

7. **Start development servers**
   ```bash
   # Frontend development server (with hot reload)
   bun run dev
   
   # Backend is served by DDEV
   # Visit: https://binsta.ddev.site
   ```

## Development Workflow

### Frontend Development
```bash
# Start development server with hot reload
bun run dev

# Build for production
bun run build

# Preview production build
bun run preview
```

### Backend Development
```bash
# Run PHP tests (100% coverage required)
ddev composer test

# Run tests with coverage report
ddev composer coverage

# Run mutation testing
ddev composer mutation

# Lint and static analysis
ddev composer lint

# Fix code style issues
ddev composer fix

# Access PHP container shell
ddev ssh
```

### Environment Management

The application automatically loads environment files in this priority:

1. `$ENV_FILE` environment variable (explicit override)
2. `.env.ci` (GitHub CI/CD environment)
3. `.env.ddev` (DDEV environment)
4. `.env.testing` (Test environment)
5. `.env.local` (Local development)
6. `.env` (Default fallback)

**Force specific environment:**
```bash
ENV_FILE=.env.custom ddev composer test
```

## Route System

The custom routing system supports advanced patterns with regex-based parameter matching:

### Basic Patterns
```php
// Exact routes
'/api/posts'                    // Literal match

// Parameter extraction  
'/api/users/{id:\d+}'          // Digits only: 123, 456
'/api/posts/{slug:[a-z-]+}'    // Custom pattern: blog-post
'/blog/{category}/{post}'      // Multiple parameters

// SPA catch-all
'/{path:.*}'                   // Captures everything for SPA
```

### Common Pattern Examples
```php
// Numeric patterns
'{id:\d+}'              // One or more digits: 123, 456
'{year:[0-9]{4}}'       // Exactly 4 digits: 2024

// String patterns  
'{slug:[a-z-]+}'        // Lowercase letters and hyphens: blog-post
'{username:[a-zA-Z0-9_]+}' // Alphanumeric with underscore

// File patterns
'{file:[^/]+\\.pdf}'    // Filename ending in .pdf
'{uuid:[0-9a-f-]{36}}'  // UUID format
```

## Project Structure

```
binsta/
├── public/                 # Web server document root
│   ├── index.php          # Application entry point
│   └── dist/              # Built frontend assets
├── src/
│   ├── Controllers/       # API and web controllers
│   ├── Entities/          # Data models (User, Post, Comment, etc.)
│   ├── Internals/         # Custom framework code
│   │   ├── Services/      # Core services (routing, Vite integration)
│   │   ├── Routes/        # Route definition classes
│   │   └── Validation/    # Form validation system
│   ├── Repositories/      # Data access layer
│   ├── Requests/          # Form request validation
│   └── Resources/         # Vue.js frontend application
│       ├── components/    # Vue components
│       ├── pages/         # Route-level components
│       ├── stores/        # Pinia state management
│       └── router/        # Client-side routing
├── routes/
│   ├── api.php           # API route definitions
│   └── web.php           # SPA route definitions
├── tests/                # Pest testing suite
└── CLAUDE.md            # Development guidelines
```

## API Endpoints

### Authentication
- `POST /api/auth/register` - User registration
- `POST /api/auth/login` - User login
- `POST /api/auth/logout` - User logout

### Posts
- `GET /api/posts` - List all posts
- `POST /api/posts` - Create new post
- `GET /api/posts/{id}` - Get specific post
- `PUT /api/posts/{id}` - Update post
- `DELETE /api/posts/{id}` - Delete post

### Users
- `GET /api/users/{id}` - Get user profile
- `GET /api/users/{id}/posts` - Get user's posts
- `POST /api/users/{id}/follow` - Follow user
- `DELETE /api/users/{id}/follow` - Unfollow user

### Interactions
- `POST /api/posts/{id}/like` - Like/unlike post
- `POST /api/posts/{id}/comments` - Add comment
- `GET /api/posts/{id}/comments` - Get post comments

## Testing

The project maintains **100% test coverage** using Pest PHP testing framework.

```bash
# Run all tests
ddev composer test

# Run with coverage report
ddev composer coverage

# Run mutation testing (100% required)
ddev composer mutation
```

### Test Structure
- **Feature Tests**: End-to-end API testing
- **Unit Tests**: Individual component testing
- **Datasets**: Reusable test data in `tests/Datasets/`

## Deployment

The project includes deployment configuration:

```bash
# Deploy to production (using Deployer)
vendor/bin/dep deploy
```

## Contributing

1. **Code Standards**: Follow PSR-12 for PHP, ESLint config for TypeScript
2. **Testing**: Maintain 100% test coverage
3. **Validation**: All forms must use custom validation system
4. **Frontend**: Use existing UI components from `src/Resources/components/ui/`
5. **Backend**: Follow existing patterns in controllers and repositories

### Before Submitting
```bash
# Run full validation suite
ddev composer lint
ddev composer test
ddev composer coverage
bun run build
```

## License

MIT License

## Author

Sander den Hollander (sanderdenhollander12@gmail.com)