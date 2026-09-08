# Changelog

Toutes les modifications notables apportées à ce projet seront documentées dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/),
et ce projet adhère à [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.0] 
### Ajouté
- Initialisation du fichier `composer.json` avec autoloading PSR-4 (`App\\` et `Tests\\`)
- Installation des dépendances imposées (`nikic/fast-route`, `respect/validation`, `illuminate/database`, `php-di/php-di`, `vlucas/phpdotenv`, `phpunit/phpunit`)
- Création de l'arborescence complète du projet
- Classe initiale `App\Application`

## [0.0.0] 
### Ajouté
- Initialisation du dépôt Git
- Fichier `.gitignore`
- Fichier `README.md`
- Fichier `CHANGELOG.md`

## [0.1.0] 
### Ajouté
- Initialisation du fichier `composer.json` avec autoloading PSR-4 (`App\\` et `Tests\\`)
- Installation des dépendances imposées (`nikic/fast-route`, `respect/validation`, `illuminate/database`, `php-di/php-di`, `vlucas/phpdotenv`, `phpunit/phpunit`)
- Création de l'arborescence complète du projet
- Classe initiale `App\Application`

## [0.2.0] 
### Ajouté
- Configuration de l'environnement avec `.env.example`
- Configuration et initialisation de l'ORM Eloquent via `Capsule\Manager` (`config/database.php`)
- Gestion robuste des erreurs de connexion et support multi-driver (MySQL, PostgreSQL, SQLite)
- Script de migration et création des tables `salles` et `reservations` (`database/migrations/create_tables.php` et `database/migrations/schema.sql`)

## [0.3.0] 
### Ajouté
- Modèle Eloquent `App\Model\Salle` avec casts de types et relation HasMany vers les réservations
- Modèle Eloquent `App\Model\Reservation` avec casts de types et relation BelongsTo vers la salle
- Configuration de `$fillable`, des types scalaires et des objets dates Carbon

## [0.4.0] 
### Ajouté
- Script de données initiales (`database/seed.php`)
- Insertion de 5 salles (Amphithéâtre A, Salle B12, Laboratoire Chimie, Salle Informatique 1, Salle de réunion)

## [0.5.0] 
### Ajouté
- Contrat de validation `App\Validation\ValidatorInterface`
- Classe de résultat de validation `App\Validation\ValidationResult`
- Validateur de salle `App\Validation\SalleValidator` utilisant `Respect\Validation`
- Validateur de réservation `App\Validation\ReservationValidator` utilisant `Respect\Validation`

## [0.6.0] 
### Ajouté
- Objet de transport typé et immuable `App\DTO\CreerSalleDTO`
- Objet de transport typé et immuable `App\DTO\CreerReservationDTO` 

## [0.7.0] 
### Ajouté
- Contrats de persistance `App\Repository\SalleRepositoryInterface` et `App\Repository\ReservationRepositoryInterface`
- Implémentation `App\Repository\EloquentSalleRepository`
- Implémentation `App\Repository\EloquentReservationRepository`

## [0.8.0] 
### Ajouté
- Exceptions métier dédiées `SalleIndisponibleException`, `ReservationIntrouvableException` et `RegleMetierException`
- Service métier `App\Service\CreerReservationService` implémentant les 9 règles d'acceptation et de non-chevauchement
- Service métier `App\Service\AnnulerReservationService` pour l'annulation de réservation et la libération de créneau

## [0.9.0] 
### Ajouté
- Contrôleur `App\Controller\SalleController` (actions index, show, create, store, edit, update, toggle)
- Contrôleur `App\Controller\ReservationController` (actions index, show, create, store, cancel)
- Moteur de vues sécurisé `App\View\ViewRenderer` avec layout principal et support des messages flash
- Templates pour les salles (`templates/salle/index.php`, `show.php`, `form.php`)
- Templates pour les réservations (`templates/reservation/index.php`, `show.php`, `form.php`)
- Templates d'erreur HTTP (`templates/error/404.php`, `templates/error/405.php`)

## [0.10.0]
### Ajouté
- Déclaration de l'ensemble des routes dans `routes/web.php` avec FastRoute
- Contrôle des paramètres dynamiques entiers `{id:\d+}`
- Intégration du routeur dans `App\Application` avec suppression de la query string
- Gestion des statuts 404 (NOT_FOUND) et 405 (METHOD_NOT_ALLOWED avec en-tête Allow)

## [0.11.0]
### Ajouté
- Configuration du conteneur d'injection de dépendances PHP-DI (`config/container.php`)
- Utilisation de l'autowiring pour les classes concrètes et de factories pour les objets 
- Point d'entrée unique et épuré `public/index.php`

## [0.12.0]
### Ajouté
- Configuration de la suite de tests PHPUnit (`phpunit.xml` et `tests/bootstrap.php`)
- Doublures de persistance en mémoire `InMemorySalleRepository` et `InMemoryReservationRepository`
- Tests unitaires complets de `CreerReservationService` (8 scénarios obligatoires) sans dépendance MySQL
- Tests unitaires de validation avec `Respect\Validation` (5 scénarios obligatoires)
- Tests d'intégration Eloquent (création, relation HasMany/BelongsTo, recherche de conflit, annulation)

## [1.0.0]
### Ajouté
- Finalisation complète de l'application web de gestion des réservations de salles universitaires
- Documentation exhaustive dans `README.md` (guide de démarrage, migrations, seeder, exécution des tests et réponses aux questions de toutes les étapes)
- Analyse architecturale détaillée des 14 concepts et des 5 principes SOLID dans `ARCHITECTURE.md`
- Validation des 8 scénarios de recette
- correction des anomalies

## [1.1.0]
### Ajouté
- Modèle Eloquent `App\Model\Utilisateur` avec sécurisation des mots de passe (`password_hash`, `password_verify`)
- Contrat de persistance `App\Repository\UtilisateurRepositoryInterface` et implémentation `App\Repository\EloquentUtilisateurRepository`
- DTO de transport `App\DTO\LoginDTO` et `App\DTO\InscriptionDTO`
- Validateurs d'authentification `App\Validation\LoginValidator` et `App\Validation\InscriptionValidator` basés sur `Respect\Validation`
- Service d'authentification `App\Service\AuthService` et contrat `App\Service\InterfaceAuthService` (gestion de session, régénération d'ID, déconnexion)
- Contrôleur d'authentification `App\Controller\AuthController` (actions login, logout, register)
- Vues d'authentification (`templates/auth/login.php` et `templates/auth/register.php`) et intégration dans la barre de navigation
- Seeding d'utilisateurs de test (`adja@univ.sn` et `admin@univ.sn`) dans `database/Seed.php`
- Tests unitaires et d'intégration complets avec doublure mémoire `InMemoryUtilisateurRepository` (59 tests réussis à 100%)
