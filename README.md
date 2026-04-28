ABCDTaxi
PHP 8.4 CI passing MIT License Contributions welcome

Application web de réservation de taxi en ligne. Sélectionnez un horaire et une destination, obtenez un devis instantané avec toutes les informations relatives à votre trajet.
PHP MVC PHPMailer Google Maps API PHPUnit 13 GitHub Actions
Fonctionnalités
→
Calcul de devis instantané avec la distance et le tarif estimé
→
Sélection d'horaire et de destination via l'API Google Maps
→
Confirmation de réservation par email (PHPMailer)
→
Espace administrateur pour la gestion des courses
Prérequis
PHP
≥ 8.4
Composer
≥ 2.x
MySQL
≥ 8.0
Clé Google API
Maps + Routes
Installation
1
Cloner le dépôt
git clone https://github.com/votre-user/ABCDTaxi.git && cd ABCDTaxi
2
Installer les dépendances
composer install
3
Copier et configurer l'environnement
cp .env.example .env — puis renseigner vos clés API Google et SMTP
4
Importer la base de données
mysql -u root -p abcdtaxi < database/schema.sql
5
Lancer un serveur local
php -S localhost:8000 -t public/
Tests
./vendor/bin/phpunit

Les tests tournent automatiquement via GitHub Actions sur chaque push vers main.
Variables d'environnement
Obtenez vos clés sur Google Cloud Console en activant les APIs Maps JavaScript et Routes. Créez un compte SMTP (ex: Mailtrap en dev, Brevo en prod) pour PHPMailer.
GOOGLE_API_KEY=your_key_here
SMTP_HOST=smtp.example.com
SMTP_USER=your_user
SMTP_PASS=your_password
DB_HOST=localhost
DB_NAME=abcdtaxi
Contribuer
1
Forker le dépôt et créer une branche : git checkout -b feature/ma-fonctionnalite
2
Pour tout changement majeur, ouvrir une issue d'abord pour en discuter
3
S'assurer que les tests passent et en ajouter si nécessaire
4
Soumettre une pull request avec une description claire des changements
