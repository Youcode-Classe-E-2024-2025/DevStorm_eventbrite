# DevStorm_eventbrite
# DevStorm_eventbrite- Eventbrite Clone

A robust event management platform built with PHP MVC & PostgreSQL, featuring real-time ticket management, secure payments, and advanced analytics.

## 📋 Table of Contents
- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Database Structure](#-database-structure)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Project Structure](#-project-structure)
- [API Endpoints](#-api-endpoints)
- [Security](#-security)
- [Testing](#-testing)
- [Deployment](#-deployment)
- [Contributing](#-contributing)
- [License](#-license)

## 🚀 Features

### User Management
- Secure authentication (email, hashed passwords)
- Role-based access (Organizer, Participant, Admin)
- Profile management with avatars
- Notification system

### Event Management
- Create and modify events
- Category and tag system
- Media upload (images/videos)
- Admin validation workflow
- Featured events system

### Ticketing & Payments
- Multiple ticket types (Free, Paid, VIP, Early Bird)
- Secure payment integration
- QR Code generation
- PDF ticket generation
- Refund management

### Organizer Dashboard
- Event management
- Real-time sales statistics
- Participant export (CSV/PDF)
- Promotion code management

### Admin Features
- User management
- Event moderation
- Global statistics
- Content moderation

## 🛠 Tech Stack

### Backend
- PHP 8.x
- PostgreSQL
- PDO
- Twig Template Engine
- Composer

### Frontend
- HTML5/CSS3
- JavaScript (ES6+)
- TailwindCSS
- AJAX/Fetch API

### Security
- Session Based Authentication
- CSRF Protection
- XSS Prevention
- SQL Injection Protection

## 💾 Database Structure

- users
- events
- tickets
- categories
- promotions
- notifications

📦 Installation

# Clone repository
git clone https://github.com/yourusername/DevEvents.git

# Install dependencies
composer install

# Configure database
cp .env.example .env
# Edit .env with your database credentials

# Run migrations
php migrations/migrate.php

🔧 Configuration

1.Database Configuration
DB_HOST=localhost
DB_NAME=eventbrite
DB_USER=your_username
DB_PASS=your_password

📁 Project Structure

DEVSTORM_EVENTBRITE/
├── app/
│   ├── controllers/
│   ├── models/
│   ├── views/
│   └── core/
├── config/
├── public/
├── routes/
└── tests/

🔌 API Endpoints

Events

GET /api/events - List events
POST /api/events - Create event
GET /api/events/{id} - Get event
PUT /api/events/{id} - Update event
DELETE /api/events/{id} - Delete event

Users

POST /api/auth/register
POST /api/auth/login
GET /api/users/profile

🔒 Security Features

* Password Hashing (Bcrypt)
* CSRF Token Validation
* XSS Protection
* SQL Injection Prevention
* Rate Limiting
* Session Securit

🧪 Testing

# Run unit tests
./vendor/bin/phpunit tests/

# Run feature tests
./vendor/bin/phpunit tests/Feature

🚀 Deployment

1.Server Requirements
* PHP 8.x
* PostgreSQL
* Composer
* Web Server (Apache/Nginx)

2.Deployment Steps

git pull origin main
composer install --no-dev
php migrations/migrate.php

👥 Contributing

1.Fork the repository
2.Create feature branch
3.Commit changes
4.Push to branch
5.Create Pull Request

📝 License

MIT License

🌟 Contributors

* oumaima ait said 
* eljassimi
* tawdi 
* nada