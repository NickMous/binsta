# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Environment

This project uses **DDEV** for local development environment management. All PHP commands should be run through DDEV.

## Development Commands

### Frontend Development
```bash
# Start development server with hot reload (Vue + Vite)
bun run dev

# Build frontend for production
bun run build

# Preview production build
bun run preview
```

### Backend Development (using DDEV)
```bash
# Run PHP tests
ddev composer test

# Run linting and static analysis
ddev composer lint

# Fix code style issues
ddev composer fix

# Run tests with coverage (requires 100% coverage)
ddev composer coverage

# Run mutation testing
ddev composer mutation

# Access PHP container shell
ddev ssh

# Run arbitrary PHP commands
ddev php [command]
```

### Package Management
- Use `bun` for JavaScript dependencies
- Use `ddev composer` for PHP dependencies

## Architecture Overview

This is a **hybrid PHP/Vue.js Single Page Application (SPA)** with a custom routing framework. The architecture combines traditional PHP backend handling with modern Vue.js frontend for a seamless user experience.

### Core Architecture Components

**1. Custom PHP Framework (`src/Internals/`)**
- **ControllerService**: Advanced routing engine with regex pattern matching
- **ViteService**: Integrates Vue.js components with PHP backend
- **Route System**: Type-safe route definitions with parameter extraction
- **Response System**: Handles both Vue component rendering and API responses

**2. Dual Routing System**
- **API Routes** (`routes/api.php`): PHP handles API endpoints with parameter extraction
- **Web Routes** (`routes/web.php`): SPA catch-all serves Vue app for frontend routing
- **Priority**: API routes processed first, then SPA catch-all

**3. Vue.js SPA Frontend (`src/Resources/`)**
- **Vue Router**: Client-side routing with smooth page transitions
- **Component Architecture**: Modular components with UI library integration
- **Tailwind CSS**: Utility-first styling with custom design system

### Request Flow

1. **All requests** hit `public/index.php`
2. **Kernel** initializes framework (database, session, error handling)
3. **ControllerService** loads both API and web routes
4. **Route Matching**: Exact match first, then regex pattern matching
5. **Response Handling**:
   - API routes → JSON/direct responses
   - Web routes → Vue SPA (client-side routing takes over)

### Route Pattern System

The custom routing supports advanced patterns with regex-based parameter matching:

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

**Parameter Access**: Route parameters available in `$GLOBALS['route_parameters']`

## Route Pattern Documentation

### Pattern Syntax

Route patterns use curly braces to define parameters with optional regex constraints:

```php
{name}           // Simple parameter (matches anything except /)
{name:pattern}   // Parameter with custom regex pattern
```

### Common Pattern Examples

```php
// Numeric patterns
'{id:\d+}'              // One or more digits: 123, 456
'{page:\d*}'            // Zero or more digits: "", 123
'{year:[0-9]{4}}'       // Exactly 4 digits: 2024

// String patterns  
'{slug:[a-z-]+}'        // Lowercase letters and hyphens: blog-post
'{username:[a-zA-Z0-9_]+}' // Alphanumeric with underscore: user_123
'{category:[a-z]+}'     // Lowercase letters only: technology

// Mixed patterns
'{file:[^/]+\\.pdf}'    // Filename ending in .pdf: document.pdf
'{uuid:[0-9a-f-]{36}}'  // UUID format: 550e8400-e29b-41d4-a716-446655440000

// Catch-all patterns
'{path:.*}'             // Match everything: any/deep/path
'{rest:.*?}'            // Non-greedy match: stops at first opportunity
```

### Route Priority and Matching

Routes are processed in **definition order**:

1. **API routes** (`routes/api.php`) - processed first
2. **Web routes** (`routes/web.php`) - processed second

Within each file, routes are matched:
1. **Exact string match** (fastest)
2. **Regex pattern match** (in definition order)

```php
// routes/api.php - These match first
Route::get('/api/posts', ...),           // Exact match
Route::get('/api/posts/{id:\d+}', ...),  // Pattern match
Route::get('/api/{resource}', ...),      // Broader pattern

// routes/web.php - These match after API routes  
Route::get('/{path:.*}', ...),           // Catch-all (SPA)
```

### Pattern Conversion Process

The system converts route patterns to regex:

```php
// Input pattern
'/api/users/{id:\d+}/posts/{slug:[a-z-]+}'

// Converted to regex
#^/api/users/(?P<id>\d+)/posts/(?P<slug>[a-z-]+)$#

// Matches examples
'/api/users/123/posts/my-blog-post'  ✅
'/api/users/abc/posts/my-blog-post'  ❌ (id not digits)
'/api/users/123/posts/My-Blog-Post'  ❌ (slug has uppercase)
```

### Parameter Extraction

When a route matches, parameters are extracted and made available:

```php
Route::get('/api/users/{id:\d+}/posts/{slug:[a-z-]+}', function() {
    $params = $GLOBALS['route_parameters'];
    
    $userId = $params['id'];     // "123"
    $postSlug = $params['slug']; // "my-blog-post"
    
    // Use parameters in your logic
    return new JsonResponse([
        'user_id' => $userId,
        'post_slug' => $postSlug
    ]);
});
```

### Advanced Regex Patterns

For complex requirements, use full regex syntax:

```php
// Optional parameters (with ?)
'{lang:en|nl|fr}'                    // Specific options: en, nl, or fr
'{version:v[0-9]+}'                  // Version format: v1, v2, v10
'{date:[0-9]{4}-[0-9]{2}-[0-9]{2}}'  // Date format: 2024-12-25
'{optional:[a-z]*}'                  // Optional letters: "", "test"

// Complex patterns
'{coords:\d+,\d+}'                   // Coordinates: 123,456
'{range:\d+-\d+}'                    // Number range: 1-100
'{extension:jpg|jpeg|png|gif}'       // File extensions
```

### Debugging Routes

To debug route matching, check the converted regex patterns:

```php
// In ControllerService, the convertToRegex() method shows:
'/api/users/{id:\d+}' → '#^/api/users/(?P<id>\d+)$#'

// Test regex manually in PHP:
preg_match('#^/api/users/(?P<id>\d+)$#', '/api/users/123', $matches);
// $matches = ['0' => '/api/users/123', 'id' => '123', '1' => '123']
```

### Frontend Architecture

**Component Structure**:
- `pages/` - Route-level components (HomePage, ProfilePage, etc.)
- `layouts/` - Layout wrappers (AppLayout with navbar)
- `components/` - Reusable components (Navbar, SearchBar, UserActions)
- `components/ui/` - UI library components (shadcn/vue style)

**State Management**: Vue 3 Composition API with reactive state

**Styling**: Tailwind CSS with custom design tokens and dark mode support

### Development Integration

**Vite Integration**:
- Development: Hot reload with dev manifest
- Production: Optimized builds with code splitting
- PHP Integration: ViteService bridges Vite assets with PHP

**Asset Handling**:
- Development: `http://localhost:5173` (Vite dev server)
- Production: `/dist/` (compiled assets)

### Database & Services

- **RedBeanPHP**: ORM for database operations
- **Twig**: Template engine (legacy, mostly replaced by Vue)
- **Whoops**: Error handling and debugging
- **Sentry**: Error tracking and monitoring

### Testing Framework

- **Pest**: PHP testing framework with 100% coverage requirement
- **Datasets**: Test data organization in `tests/Datasets/`
- **Feature Tests**: End-to-end testing
- **Unit Tests**: Component isolation testing

### Key Files for Understanding

- `public/index.php` - Application entry point
- `src/Kernel.php` - Framework initialization
- `src/Internals/Services/ControllerService.php` - Core routing logic
- `src/Resources/main.ts` - Vue app entry point
- `src/Resources/router/index.ts` - Client-side routing
- `vite.config.ts` - Build configuration and PHP integration

### Development Notes

**Route Parameter Patterns**:
- `\d+` - One or more digits
- `.*` - Match everything (catch-all)
- `[a-zA-Z0-9-]+` - Alphanumeric with hyphens
- Custom regex patterns supported

**Component Naming**: Use PascalCase for Vue components, kebab-case for files

**State Access**: Route parameters accessible via `$GLOBALS['route_parameters']` in PHP route closures

**Asset Management**: The ViteService automatically handles development vs production asset loading