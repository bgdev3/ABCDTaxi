# ABCDTaxi

![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php)

Application web de réservation de taxi en ligne. Sélectionnez un horaire et une destination, obtenez un devis instantané avec toutes les informations relatives à votre trajet.

## Fonctionnalités

- Calcul de devis instantané (distance + tarif estimé)
- Sélection d'horaire et de destination via l'API Google Maps
- Confirmation de réservation par email (PHPMailer)
- Espace administrateur pour la gestion des courses

## Prérequis

- PHP ≥ 8.4
- Composer ≥ 2.x
- MySQL ≥ 8.0
- Clés API Google (Maps JavaScript + Routes)

## Installation

```bash
git clone https://github.com/votre-user/ABCDTaxi.git
cd ABCDTaxi
composer install
cp .env.example .env   # puis renseigner les variables
mysql -u root -p abcdtaxi < database/schema.sql
php -S localhost:8000 -t public/
```

## Configuration

Créez un fichier `.env` à partir de `.env.example` :

```env
GOOGLE_API_KEY=your_key_here
SMTP_HOST=smtp.example.com
SMTP_USER=your_user
SMTP_PASS=your_password
DB_HOST=localhost
DB_NAME=abcdtaxi
```

Obtenez vos clés sur [Google Cloud Console](https://console.cloud.google.com) en activant les APIs **Maps JavaScript** et **Routes**.

## Tests

```bash
./vendor/bin/phpunit
```

Les tests s'exécutent automatiquement via GitHub Actions à chaque push sur `main`.

## Contribuer

1. Forker le dépôt et créer une branche : `git checkout -b feature/ma-fonctionnalite`
2. Pour tout changement majeur, ouvrir une issue d'abord
3. S'assurer que les tests passent et en ajouter si nécessaire
4. Soumettre une pull request avec une description claire

## Licence

Distribué sous licence [MIT](LICENSE).
