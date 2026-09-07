# Changelog

Toutes les modifications notables apportées à ce projet seront documentées dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/),
et ce projet adhère à [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.0] - 2026-09-07
### Ajouté
- Initialisation du fichier `composer.json` avec autoloading PSR-4 (`App\\` et `Tests\\`)
- Installation des dépendances imposées (`nikic/fast-route`, `respect/validation`, `illuminate/database`, `php-di/php-di`, `vlucas/phpdotenv`, `phpunit/phpunit`)
- Création de l'arborescence complète du projet
- Classe initiale `App\Application`

## [0.0.0] - 2026-09-07
### Ajouté
- Initialisation du dépôt Git
- Fichier `.gitignore`
- Fichier `README.md`
- Fichier `CHANGELOG.md`

## [0.1.0] - 2026-09-07
### Ajouté
- Initialisation du fichier `composer.json` avec autoloading PSR-4 (`App\\` et `Tests\\`)
- Installation des dépendances imposées (`nikic/fast-route`, `respect/validation`, `illuminate/database`, `php-di/php-di`, `vlucas/phpdotenv`, `phpunit/phpunit`)
- Création de l'arborescence complète du projet
- Classe initiale `App\Application`

## [0.2.0] - 2026-09-07
### Ajouté
- Configuration de l'environnement avec `.env.example`
- Configuration et initialisation de l'ORM Eloquent via `Capsule\Manager` (`config/database.php`)
- Gestion robuste des erreurs de connexion et support multi-driver (MySQL, PostgreSQL, SQLite)
- Script de migration et création des tables `salles` et `reservations` (`database/migrations/create_tables.php` et `database/migrations/schema.sql`)

## [0.3.0] - 2026-09-07
### Ajouté
- Modèle Eloquent `App\Model\Salle` avec casts de types et relation HasMany vers les réservations
- Modèle Eloquent `App\Model\Reservation` avec casts de types et relation BelongsTo vers la salle
- Configuration de `$fillable`, des types scalaires et des objets dates Carbon

## [0.4.0] - 2026-09-07
### Ajouté
- Script de données initiales (`database/seed.php`)
- Insertion de 5 salles (Amphithéâtre A, Salle B12, Laboratoire Chimie, Salle Informatique 1, Salle de réunion)

## [0.5.0] - 2026-09-06
### Ajouté
- Contrat de validation `App\Validation\ValidatorInterface`
- Classe de résultat de validation `App\Validation\ValidationResult`
- Validateur de salle `App\Validation\SalleValidator` utilisant `Respect\Validation`
- Validateur de réservation `App\Validation\ReservationValidator` utilisant `Respect\Validation`

## [0.6.0] - 2026-09-06
### Ajouté
- Objet de transport typé et immuable `App\DTO\CreerSalleDTO`
- Objet de transport typé et immuable `App\DTO\CreerReservationDTO` 
