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

### Étape 4 — Données Initiales (Seed)

1. Quelle différence existe entre migration et seeder ?
   Une migration définit et fait évoluer la structure (schéma DDL : tables, colonnes, index, contraintes). Un seeder peuple la base avec des données (salles par défaut, données de test, comptes initiaux) cest comme les fixtures.

2. Pourquoi les données initiales doivent-elles être reproductibles ?
   Pour que chaque collaborateur ou environnement de test/déploiement puisse repartir d'un jeu de données fiable, cohérent et identique en une seule commande.

3. Comment empêcher les doublons ?
   En utilisant une condition d'unicité basée sur un critère distinctif (ici `nom`), par exemple via `Salle::updateOrCreate(['nom' => $data['nom']], $data)` ou en vérifiant l'existence avant insertion.


### Étape 5 — Validation

1. Pourquoi séparer la validation syntaxique des règles métier ?
   La validation syntaxique vérifie les données brutes isolées email chaines vides etc. 
   Les règles métier vérifient la légalité de l'opération dans le domaine salle inactive, conflit de créneau horaire avec d'autres réservations. le melange des deux viole le principe de responsabilité unique (SRP).

2. Pourquoi créer une interface de validation ?
   Elle établit un contrat uniforme (`validate(array $data): ValidationResult`), et si jamais on change de dependances
   , les besoins évoluent ou si l'on change de bibliothèque de validation, on peut remplacer l'implémentation sans modifier tout le reste de l'application.

3. Pourquoi le validateur ne doit-il pas enregistrer les données ?
   Un validateur n'a pour rôle que d'inspecter et d'émettre un diagnostic. S'il effectuait des écritures en base, il couplerait la validation à la persistance et empêcherait la pré-validation sans effet de bord.

4. Comment retourner plusieurs erreurs en une seule fois ?
   En accumulant toutes les erreurs détectées dans une collection clé-valeur (tableau associatif `champ => message`) encapsulée dans l'objet `ValidationResult`. L'utilisateur reçoit ainsi un retour complet pour tous les champs erronés dès la première soumission.

### Étape 6 — DTO

1. Quelle différence existe entre DTO et modèle Eloquent ?
   Un DTO est un simple conteneur de données en lecture seule (`readonly`), typé et sans comportement, conçu pour transiter entre les couches. Un modèle Eloquent est une entité Active , couplée à la base de données, gérant les relations, événements et états de persistance.

2. Pourquoi le DTO ne doit-il pas appeler `save()` ?
   Le DTO n'a aucune responsabilité de persistance ni d'accès aux données.

3. À quel moment transforme-t-on les chaînes en dates ?
   Lors de la fabrique du DTO (méthode `fromArray()`), juste après que la validation syntaxique a garanti que la chaîne représentait une date valide. Le service reçoit ainsi un objet `DateTimeImmutable`.

4. Le DTO doit-il contenir la règle de chevauchement ?
   Non. La règle de chevauchement exige de consulter les réservations existantes en base de données via un repository. Le DTO est passif et ne doit jamais dépendre de la base de données.

### Étape 7 — Repositories

1. Eloquent constitue-t-il déjà un accès aux données ?
   Oui, Eloquent est un ORM Active Record et fournit déjà un Query Builder.

2. Pourquoi ajouter un Repository au-dessus d’Eloquent ?
   On ajoute un Repository au-dessus d’Eloquent pour créer une barrière d’abstraction entre le domaine métier et la technologie de persistance. Les Controllers et Services ne dépendent pas directement d’Eloquent. 
   Ainsi, l’implémentation du Repository peut varier (Eloquent, PDO, API, etc.) sans modifier la logique métier.

3. Cette abstraction est-elle toujours nécessaire ?
   Dans un projet minimaliste sans logique complexe (CRUD basique), elle peut être superflue. Mais dès que des règles métier complexes existent et requièrent des tests unitaires rapides et isolés, elle devient indispensable.

4. Quel avantage apporte-t-elle ?
   le decouplage , si demain je dois utiliser pdo je suis abliger de modifier le services.

### Étape 8 — Services Métier

1. Pourquoi ces règles ne sont-elles pas dans le contrôleur ?
   Le contrôleur a pour unique rôle la gestion du protocole HTTP (lire la requête, appeler les composants, retourner une réponse ou redirection). Y placer les règles métier provoquerait le syndrome du "Fat Controller" et interdirait la réutilisation de cette logique via un autre canal (CLI, API REST, tâches cron).

2. Pourquoi le service dépend-il d’une interface de Repository ?
   Pour respecter le principe d'injection des dépendances (DIP) : la logique métier ne doit pas dépendre des détails d'implémentation de la base SQL .

3. Quelle exception doit être levée en cas de conflit ?
   Une exception de domaine explicite : `SalleIndisponibleException`.

4. Comment tester le service sans MySQL ?
   En lui injectant une implémentation en mémoire de `ReservationRepositoryInterface` (`InMemoryReservationRepository`), simulant le stockage dans un simple tableau PHP.

### Étape 10 — FastRoute

1. Pourquoi FastRoute ne construit-il pas lui-même le contrôleur ?
   FastRoute est un routeur pur : son périmètre est strictement délimité à l'analyse de l'URI et de la méthode HTTP. La construction des contrôleurs relève de la responsabilité du conteneur d'injection de dépendances.

2. Quelle différence existe entre 404 et 405 ?
   Une 404 (Not Found) signifie qu'aucun handler n'existe pour cette URL, quelle que soit la méthode. Une 405 (Method Not Allowed) signifie que l'URL existe mais ne prend pas en charge le verbe HTTP employé.

3. Pourquoi contraindre `{id}` avec `\d+` ?
   Pour rejeter immédiatement toute requête contenant un identifiant non numérique au niveau du routeur, avant même d'instancier un contrôleur ou d'exécuter une requête inutile.

4. Quel composant doit interpréter le handler retourné ?
   La classe principale `App\Application` (Front Controller), qui résout la classe contrôleur via le conteneur d'injection et invoque la méthode cible avec les paramètres d'URL.
