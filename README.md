### This document was created using CHAT GPT

# Laravel User Management API

A comprehensive RESTful API for user management built with Laravel 10, featuring authentication, CRUD operations, security middleware, and comprehensive testing.

## 🚀 Features

### Core Functionality
- **User Authentication** - JWT-based authentication using Laravel Sanctum
- **User CRUD Operations** - Complete Create, Read, Update, Delete operations
- **User Roles Management** - Admin, Supervisor, and Agent roles
- **Advanced Search & Filtering** - Filter users by role, email, location, and more
- **Pagination** - Efficient data pagination for large datasets
- **Geolocation Support** - Latitude and longitude coordinates for users
- **Data Validation** - Comprehensive input validation and sanitization
- **Rate Limiting** - Multi-tier rate limiting for API protection
- **Security Headers** - CORS, Content Security Policy, and other security features

### Technical Features
- **Laravel Sanctum Authentication** - Token-based API authentication
- **Form Request Validation** - Dedicated validation classes for data integrity
- **Resource Transformers** - Consistent API response formatting
- **Comprehensive Testing** - Feature and Unit tests with PHPUnit
- **Docker Integration** - Complete containerized development environment
- **Database Optimization** - Indexed database fields for performance
- **Error Handling** - Structured error responses and logging
- **API Documentation** - Postman collection included

## 📋 Requirements

- PHP ^8.1
- Laravel ^10.10
- MySQL 8.0+
- Composer
- Docker & Docker Compose (for containerized setup)

## 🛠️ Installation - Docker Setup

1. **Navigate to docker directory**
   ```bash
   cd test_new/setup/docker
   ```

2. **Start Docker services**
   ```bash
   docker stack deploy -c docker-compose.yml <STACK_NAME>
   ```

3. **Access the application container**
   ```bash
   docker exec  -it <container_id> bash
   ```

4. **Install dependencies inside container**
   ```bash
   composer install
   php artisan key:generate
   php artisan migrate
   ```

### Docker Services

The Docker setup includes the following services:

- **Database (MySQL 8.0.28)**
  - Port: 2018 (mapped to 3306)
  - Database: `testdb`
  - User: `user` / Password: `uspasswrd`
  - Root Password: `rooPasswrd`

- **Application (Laravel)**
  - Port: 3100 (PHP-FPM on port 9000)
  - Working Directory: `/var/www/admin`

- **Web Server (Nginx 1.22.1)**
  - Port: 8270
  - Serves the Laravel application

## 📚 API Documentation

### Base URL
```
Docker: http://localhost:8270/api
```

### Authentication Endpoints

#### Register User
```http
POST /api/auth/register
Content-Type: application/json

{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@gmail.com",
    "password": "SecurePass123!",
    "password_confirmation": "SecurePass123!",
    "role": 3,
    "latitude": 40.7128,
    "longitude": -74.0060
}
```

#### Login
```http
POST /api/auth/login
Content-Type: application/json

{
    "email": "john@gmail.com",
    "password": "SecurePass123!"
}
```

#### Logout
```http
POST /api/auth/logout
Authorization: Bearer {token}
```

#### Refresh Token
```http
POST /api/auth/refresh
Authorization: Bearer {token}
```

#### Get Profile
```http
GET /api/auth/profile
Authorization: Bearer {token}
```

### User Management Endpoints

#### Get All Users (with filtering and pagination)
```http
GET /api/users?page=1&per_page=15&role=3&search=john&sort_by=created_at&sort_direction=desc
Authorization: Bearer {token}
```

#### Get User by ID
```http
GET /api/users/{id}
Authorization: Bearer {token}
```

#### Create User
```http
POST /api/users
Authorization: Bearer {token}
Content-Type: application/json

{
    "first_name": "Jane",
    "last_name": "Smith",
    "email": "jane@gmail.com",
    "password": "SecurePass123!",
    "role": 2,
    "latitude": 34.0522,
    "longitude": -118.2437
}
```

#### Update User
```http
PUT /api/users/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "first_name": "Jane Updated",
    "role": 1
}
```

#### Delete User
```http
DELETE /api/users/{id}
Authorization: Bearer {token}
```

### Utility Endpoints

#### Health Check
```http
GET /api/health
```


## 🎭 User Roles

- **Admin (1)** - Full system access
- **Supervisor (2)** - Management level access
- **Agent (3)** - Basic user access

## 🔒 Security Features

### Rate Limiting
- **Authentication endpoints**: 5 requests per minute
- **General API endpoints**: 60 requests per minute
- **User-specific limits**: 1000 requests per minute per authenticated user

### Security Middleware
- CORS configuration
- Content Security Policy headers
- X-Frame-Options protection
- XSS protection headers
- Content type sniffing prevention

### Data Validation
- **Email**: Valid email format, unique in database
- **Password**: Minimum 8 characters, must contain uppercase, lowercase, number, and special character
- **Names**: Alphabetic characters only, 2-50 characters
- **Coordinates**: Valid latitude (-90 to 90) and longitude (-180 to 180)
- **Role**: Must be valid role constant (1, 2, or 3)

## 🧪 Testing

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Suites
```bash
# Feature Tests
php artisan test tests/Feature

# Unit Tests
php artisan test tests/Unit

# Specific Test Class
php artisan test tests/Feature/AuthControllerTest.php
```

### Test Coverage
- **Authentication Tests**: Login, register, logout, token refresh
- **User CRUD Tests**: Create, read, update, delete operations
- **Validation Tests**: Input validation and error handling
- **Model Tests**: User model functionality and relationships

## 🗂️ Project Structure

```
app/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── AuthController.php      # Authentication endpoints
│   │   │   └── UserController.php      # User CRUD endpoints
│   │   ├── Requests/
│   │   │   ├── StoreUserRequest.php    # User creation validation
│   │   │   └── UpdateUserRequest.php   # User update validation
│   │   ├── Resources/
│   │   │   ├── UserResource.php        # Single user response transformer
│   │   │   └── UserCollection.php      # User collection transformer
│   │   └── Middleware/
│   │       └── SecurityMiddleware.php   # Security headers middleware
│   ├── Models/
│   │   └── User.php                    # User model with roles and validation
│   └── Providers/
├── database/
│   ├── factories/
│   │   └── UserFactory.php            # User factory for testing
│   ├── migrations/
│   └── seeders/
├── routes/
│   └── api.php                        # API routes configuration
├── tests/
│   ├── Feature/
│   │   ├── AuthControllerTest.php     # Authentication endpoint tests
│   │   └── UserControllerTest.php     # User CRUD endpoint tests
│   └── Unit/
│       ├── UserModelTest.php          # User model tests
│       └── UserValidationTest.php     # Validation tests
└── storage/
    └── logs/                          # Application logs
```

## 📮 Postman Collection

A complete Postman collection is included in the project root:
- **File**: `Laravel_User_Management_API.postman_collection.json`
- **Includes**: All API endpoints with sample requests
- **Environment**: Pre-configured variables for easy testing

### Import Instructions
1. Open Postman
2. Click "Import"
3. Select the `Laravel_User_Management_API.postman_collection.json` file
4. Set the base URL variable to your application URL

## 🐳 Docker Configuration

### Services Overview

#### MySQL Database
- **Image**: mysql:8.0.28
- **Port**: 2018 (external) → 3306 (internal)
- **Volume**: Persistent data storage in `../database`
- **Authentication**: Native password plugin enabled

#### PHP Application
- **Image**: marvel:1 (custom Laravel image)
- **Port**: 3100 (external) → 9000 (internal)
- **Volume Mounts**:
  - Application code: `../../app:/var/www/admin`
  - PHP config: `../php/local.ini:/usr/local/etc/php/conf.d/local.ini`
  - SSL certificates: `../php/certs/cacert.pem:/var/etc/cert/cacert.pem`

#### Nginx Web Server
- **Image**: nginx:1.22.1
- **Port**: 8270
- **Configuration**: Custom nginx config in `../nginx/app.conf`
- **Logs**: Stored in `../nginx/log`

### Docker Commands

```bash

# Initialize a new swarm on this node
docker swarm init

# Deploy a stack from docker-compose.yml
docker stack deploy -c docker-compose.yml <STACK-NAME>

# List all stacks
docker stack ls

# List services in a stack
docker stack services <STACK-NAME>

# List tasks in a stack
docker stack ps <STACK-NAME>

# Remove a stack
docker stack rm <STACK-NAME>

# List running containers
docker ps

# Access a container named "my-app"
docker exec -it my-app bash

```

## 🔧 Configuration

### Environment Variables

Key environment variables for configuration:

```env
APP_NAME="Laravel User Management API"
APP_ENV=local
APP_KEY=base64:generated_key
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=testdb
DB_USERNAME=user
DB_PASSWORD=uspasswrd

SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
SESSION_DRIVER=file
```

### Database Configuration

The application uses MySQL with optimized settings:
- **Connection pooling** for better performance
- **Indexed fields** on frequently queried columns
- **Foreign key constraints** for data integrity
- **UTF-8 character set** for international support

## 🚨 Error Handling

### HTTP Status Codes
- **200**: Success
- **201**: Created successfully
- **400**: Bad Request
- **401**: Unauthorized
- **403**: Forbidden
- **404**: Not Found
- **422**: Validation Error
- **429**: Rate Limit Exceeded
- **500**: Internal Server Error

### Error Response Format
```json
{
    "status": "error",
    "message": "Error description",
    "errors": {
        "field_name": ["Validation error message"]
    },
    "timestamp": "2025-09-25T10:30:00.000000Z"
}
```

## 📊 Performance Features

- **Database Indexing**: Optimized queries with proper indexes
- **Resource Pagination**: Efficient data loading with configurable page sizes
- **Rate Limiting**: Prevents API abuse and ensures fair usage
- **Caching**: Session and query caching for improved performance
- **Lazy Loading**: Efficient data loading strategies

## 🔍 Monitoring & Logging

- **Application Logs**: Stored in `storage/logs/`
- **Database Status**: Real-time database health monitoring
- **API Health Check**: Endpoint for monitoring API availability
- **Error Tracking**: Comprehensive error logging and reporting

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Ensure all tests pass
6. Submit a pull request

## 📄 License

This project is licensed under the MIT License.

## 📞 Support

For support or questions:
1. Check the API documentation
2. Review the test files for usage examples
3. Check application logs for debugging
4. Use the health check endpoints for system status

---

**Built with ❤️ using Laravel 10 & Docker**