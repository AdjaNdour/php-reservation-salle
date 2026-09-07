# Gestion des Réservations de Salles Universitaires

Application web moderne en PHP orienté objet sans framework complet, permettant de consulter les salles universitaires et de gérer leurs réservations en évitant les conflits d'horaires.

## 💡 Réponses aux Questions Pédagogiques

### Étape 1 — Composer

1. Quel est le rôle de Composer ?
   Composer est le gestionnaire de dépendances standard de PHP. Il télécharge les bibliothèques tierces, résout leurs versions compatibles et génère automatiquement la cartographie d'autoloading PSR-4 (`vendor/autoload.php`).

2. Quelle différence existe entre `require` et `require-dev` ?
   `require` contient les packages indispensables au fonctionnement de l'application en production (ex: FastRoute, Eloquent, PHP-DI). `require-dev` regroupe les outils réservés au développement et à l'assurance qualité (ex: PHPUnit), exclus en production via `composer install --no-dev`.

3. Pourquoi faut-il versionner `composer.lock` ?
   `composer.lock` enregistre l'arborescence exacte des versions et condensats SHA installés. Le versionner garantit que tous les développeurs, le serveur d'intégration continue et l'environnement de production partagent rigoureusement le même code au bit près.

4. Pourquoi ne versionne-t-on pas `vendor/` ?
   Le dossier `vendor/` est très volumineux, redondant et régénérable à tout moment via `composer install`. Le versionner encombrerait l'historique Git et introduirait des conflits de fusion insolubles lors des mises à jour de bibliothèques.

### Étape 2 — Eloquent

1. Quel rôle joue `Capsule\Manager` ?
   `Capsule\Manager` agit comme une passerelle d'amorçage autonome qui configure, enregistre le gestionnaire de connexions PDO et initialise le dispatcher d'événements d'Eloquent en dehors d'un framework Laravel.

2. Pourquoi Eloquent peut-il fonctionner sans Laravel ?
   Eloquent est distribué sous forme de composant modulaire découplé (`illuminate/database`). Grâce à l'injection de conteneur interne et à la classe `Capsule`, il n'a aucune dépendance obligatoire au noyau complet de Laravel.

3. Où doit se trouver le démarrage de l’ORM ?
   Au démarrage technique de l'application, dans la couche de configuration d'infrastructure (`config/database.php` ou factory du conteneur `config/container.php`), avant toute utilisation des modèles ou des repositories.

4. Quelle différence existe entre ORM et SQL écrit à la main ?
   Le SQL manuel manipule directement des chaînes de requêtes brutes et des tableaux associatifs sans typage objet. L'ORM mappe les tables et relations sur des objets métier typés, protège automatiquement contre les injections SQL, et abstrait les dialectes spécifiques des différents moteurs de bases de données.

### Étape 3 — Modèles

1. Quel type de relation Eloquent avez-vous utilisé ?
   Une relation `HasMany` (un-à-plusieurs) sur `Salle::reservations()` et son inverse `BelongsTo` (plusieurs-à-un) sur `Reservation::salle()`, liées par la clé étrangère `salle_id`.

2. Pourquoi déclarer `$fillable` ou `$guarded` ?
   Pour sécuriser l'assignation en masse. Cela empêche un utilisateur malveillant d'injecter des champs non sollicités (ex: falsifier l'identifiant `id` ou des colonnes d'administration) lors d'un `Model::create()` ou `$model->fill()`.

3. Pourquoi convertir `active` en booléen ?
   En base relationnelle (MySQL), les booléens sont stockés sous forme d'`INT`. Le cast `'active' => 'boolean'` assure que le modèle PHP manipule toujours un type primitif `bool` (`true`/`false`) strict.

4. Pourquoi convertir les dates en objets ?
   Le cast `'datetime'` convertit automatiquement les chaînes SQL (`2026-09-06 10:00:00`) en instances `Carbon` / `DateTimeInterface`. Cela permet d'effectuer des comparaisons fiables, des calculs d'intervalles et des formatages personnalisés (`$date->format('d/m/Y')`) sans ré-instanciation manuelle.
