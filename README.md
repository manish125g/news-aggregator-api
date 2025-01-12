# News Aggregator API

A Laravel application that fetches the news feeds from different Data Sources like NewsAPI,
The Guardian, and New York Times. It is also scalable and can be customized to support various News Aggregators.
This API provides features such as user authentication, article fetching, user personalized feeds,
and more.
---

## **Table of Contents**

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Prerequisites](#prerequisites)
- [Project Structure](#project-structure)
- [Setup Instructions](#setup-instructions)
- [Swagger Documentation](#swagger-documentation)
- [Postman Collection](#postman-collection)

---

## **Features**

The API consists of five different modules with built-in validation and error handling,
rate limiting for endpoints to ensure API protection and adhere with scalable architecture

### Authentication

This application includes user authentication functionalities such as registration, login,
password reset, and logout. It leverages [Laravel Sanctum](https://laravel.com/docs/11.x/sanctum)
for secure, token-based authentication.

Upon successful login, the API generates and returns an authentication token, which can be
used to access protected endpoints. This ensures a seamless and secure experience for users
while interacting with the system.

### News Aggregation

The application aggregates news and feeds from multiple sources, including NewsAPI,
The Guardian, and The New York Times. It fetches updates at hourly intervals, storing
the data in the database to enable efficient retrieval and user interactions.

### Article

Endpoints have been implemented to retrieve articles with pagination support.
This ensures efficient data handling and improves the user experience by allowing users to
browse articles page by page. This is also enriched with advanced filtering that includes
filtering the data based on keywords, title, description, category, publication date, source.
It also includes sorting based on publication date and title.

### Personalized News Feeds

#### User Preference

Users can retrieve and update their news feed preferences, such as preferred categories,
sources, or keywords.

#### Personalized Feeds

Based on the user's preferences, the platform curates and delivers personalized news feeds.

### API Documentation

Swagger documentation for easy integration.

---

## **Tech Stack**

- **Backend**: Laravel 11 (PHP 8.2)
- **Database**: MySQL 8
- **Caching**: Laravel Cache tables (Redis can be used)
- **Task Scheduling**: Laravel Scheduler
- **API Documentation**: Swagger (via `zircote/swagger-php` and `l5-swagger`)
- **Containerization**: Docker with Docker Compose
- **Testing**: PHPUnit for unit and feature tests, XDebug for code coverage

---

## **Prerequisites**

Before setting up the project, ensure you have the following installed:

- **PHP**: 8.2 or higher
- **Composer**: Latest version
- **Docker**: Latest version

---

## **Project structure**

This application follows a default directory structure of Laravel.
Below are the directories that contains the main logic,

`app/Contracts`

Contains interface for fetching articles so that new providers implement.

`app/Services`

Contains News aggregator Service and different News Providers

`app/Traits`

Contains API Responder so that the responses are sent with similar format

`app/Docs`

Contains the details of documentation of Swagger

`app/Models`

Contains models for user, user preference and articles.

`app/Http/Controllers`

Contains all controllers to handle routes functionalities.

`database/factories`

Contains factories that can be used to generate mock data for models.

`database/migrations`

Contains migrations for database schema.

`database/seeders`

Contains database seeder for models that can be used to generate mock data to init the api.

`app/Http/Resources`

Contains JSON resources specifically for models.

`app/Http/Requests`

Contains rules to validate route requests that can be injected in controller methods.

`routes/api.php`

Contains all the API routes and controller methods mapping.

`routes/console.php`

Contains the scheduler call for hourly fetching of the news feeds from different sources.

`tests/Unit`

Contains all the unit tests.

`tests/Feature`

Contains all the feature tests.

--

## **Setup Instructions**

1. **Clone the Repository**:
   ```bash
   git clone git@github.com:manish125g/news-aggregator-api.git
   cd news-aggregator-api
   ```
2. **Set up using docker**

- For MAC OS

```bash
   # Build Docker Image and Run Docker
   docker compose build && docker compose up -d
   # Copy .env file
   docker compose exec app cp .env.example .env
   # Install dependencies
   docker compose exec app composer install
   # Generate Application Key
   docker compose exec app php artisan key:generate
   # Run Migrations and Seeders
   docker compose exec app php artisan migrate --seed
   # Run Tests and check code coverage
   docker compose exec app php artisan test --coverage
```

- For Linux based OS

```bash
   # Build Docker Image and Run Docker
   docker-compose build && docker-compose up -d
   # Copy .env file
   docker-compose exec app cp .env.example .env
   # Install dependencies
   docker-compose exec app composer install
   # Generate Application Key
   docker-compose exec app php artisan key:generate
   # Run Migrations and Seeders
   docker-compose exec app php artisan migrate --seed
   # Run Tests and check code coverage
   docker-compose exec app php artisan test --coverage
```

3. **Setting up API Keys and Mail Keys**

- Add Mail Keys in .env

```cmd
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
```

- Add API Keys in .env

```cmd
NEWS_API_SECRET_KEY=
GUARDIAN_API_SECRET_KEY=
NY_TIMES_API_SECRET_KEY=
```

- ```NEWS_API_SECRET_KEY``` can be found on https://newsapi.org/register
- ```GUARDIAN_API_SECRET_KEY``` can be found on https://open-platform.theguardian.com/access/
- ```NY_TIMES_API_SECRET_KEY``` cane be found on https://developer.nytimes.com/accounts/create

---

## **Swagger Documentation**

Access the full documentation:

```cmd
    http://localhost:8000/api/documentation
```

---

## **Postman Collection**

[News API.postman_collection.json](https://github.com/manish125g/news-aggregator-api/blob/master/News%20API.postman_collection.json)
I have included a postman collection that has all the endpoints, you can import it in postman and check the APIs
