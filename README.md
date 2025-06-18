# 🚀 Installation locale sans Docker

## Prérequis
- PHP 8.3+
- MySQL 8.0+ ou WAMP/XAMPP
- Composer
- Git

## Installation

### 1. Cloner et installer
```powershell
git clone https://github.com/votre-username/awen.git
cd awen
composer install
```

### 2. Configuration
```powershell
copy .env .env.local
```

Éditer `.env.local` :
```ini
APP_SECRET=votre-cle-secrete-32-caracteres
DATABASE_URL="mysql://root:password@127.0.0.1:3306/awen_dev?serverVersion=8.0"
MAILER_DSN="smtp://username:password@sandbox.smtp.mailtrap.io:2525"
```

### 3. Base de données
```powershell
# Créer la BDD (MySQL ou phpMyAdmin)
mysql -u root -p
CREATE DATABASE awen_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Migrations
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

### 4. Lancement
```powershell
# Option 1 : Symfony CLI (recommandé)
symfony server:start

# Option 2 : Serveur PHP
php -S localhost:8000 -t public/
```

## Accès
- **Application** : http://localhost:8000
- **Admin** : http://localhost:8000/admin
