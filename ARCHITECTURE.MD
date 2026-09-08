# Architecture du projet

Notre projet suit une architecture organisée autour de plusieurs composants permettant de séparer les responsabilités et de rendre le code plus facile à maintenir.

## 1. MVC

**Classes concernées :** `Controller`, `Model/Entity`, `View`

Le modèle MVC sépare l'application en trois parties :

* **Model** : représente les données et la logique liée aux données.
* **View** : affiche les informations à l'utilisateur.
* **Controller** : reçoit les requêtes et coordonne les différentes parties.

**Avantage :** le code est mieux organisé et plus facile à maintenir.

**Limite :** l'architecture peut devenir complexe si le projet contient beaucoup de classes.

**Extrait :**

```php
public function store(): void
{
    $dto = $this->builderSalle->fromArray($_POST)->build();
    $salle = $this->salleService->save($dto);

    header('Location: /salles');
    exit;
}
```

---

## 2. Front Controller

**Classe concernée :** `public/index.php`

Le Front Controller est le point d'entrée unique de l'application. Toutes les requêtes passent par ce fichier avant d'être envoyées vers le bon contrôleur.

**Avantage :** permet de centraliser le démarrage de l'application.

**Limite :** une erreur dans ce fichier peut empêcher toute l'application de fonctionner.

**Extrait :**

```php
require_once '../vendor/autoload.php';

$app = new Application();
$app->run();
```

---

## 3. Router

**Classe concernée :** `Router` / configuration FastRoute

Le Router détermine quel contrôleur doit être exécuté selon l'URL demandée.

**Avantage :** facilite la gestion des différentes URLs.

**Limite :** beaucoup de routes peuvent rendre sa configuration difficile à gérer.

**Extrait :**

```php
$dispatcher = FastRoute\simpleDispatcher(function(RouteCollector $r) {
    $r->addRoute('GET', '/salles', [SalleController::class, 'index']);
});
```

---

## 4. Validator

**Classes concernées :** `SalleValidator`, `ReservationValidator`

Le Validator vérifie que les données reçues sont correctes avant leur utilisation.

**Avantage :** évite d'enregistrer des données incorrectes.

**Limite :** les règles de validation doivent être maintenues lorsque les besoins changent.

**Extrait :**

```php
$result = $this->reservationValidator->validate($data);
```

---

## 5. DTO

**Classes concernées :** `CreerSalleDTO`, `CreerReservationDTO`

Un DTO sert à transporter les données entre les différentes parties de l'application.

**Avantage :** permet de contrôler clairement les données utilisées par les services.

**Limite :** ajoute des classes supplémentaires au projet.

**Extrait :**

```php
$dto = CreerReservationDTO::fromArray($data, $validator);
```

---

## 6. ORM

**Technologie concernée :** Eloquent

L'ORM permet de manipuler les données de la base de données avec des objets PHP plutôt qu'avec uniquement du SQL.

**Avantage :** simplifie les opérations avec la base de données.

**Limite :** peut cacher la complexité des requêtes SQL et produire des requêtes moins optimisées.

**Extrait :**

```php
Salle::where('active', true)->get();
```

---

## 7. Active Record

**Classe concernée :** `Salle` / modèles Eloquent

Avec Active Record, l'objet représente à la fois une donnée et permet d'effectuer des opérations sur cette donnée dans la base.

**Avantage :** permet de travailler simplement avec les données.

**Limite :** mélange parfois la représentation des données et l'accès à la base.

**Extrait :**

```php
$salle = Salle::find($id);
```

---

## 8. Repository

**Classes concernées :** `SalleRepository`, `ReservationRepository`

Le Repository centralise les opérations d'accès aux données.

**Avantage :** sépare l'accès à la base du reste de l'application.

**Limite :** peut ajouter du code supplémentaire pour des opérations simples.

**Extrait :**

```php
$salle = $this->salleRepository->findById($id);
```

---

## 9. Service

**Classes concernées :** `CreerSalleService`, `CreerReservationService`

Le Service contient la logique métier de l'application.

**Avantage :** évite de mettre toute la logique dans les contrôleurs.

**Limite :** trop de services peuvent rendre le projet difficile à comprendre.

**Extrait :**

```php
$reservation = $this->reservationService->executer($dto);
```

---

## 10. Injection par constructeur

**Classes concernées :** Services, Controllers, Repositories

Les dépendances nécessaires à une classe sont fournies directement dans son constructeur.

**Avantage :** rend les dépendances claires et facilite les tests.

**Limite :** les constructeurs peuvent avoir beaucoup de paramètres.

**Extrait :**

```php
public function __construct(
    private SalleRepository $salleRepository
) {}
```

---

## 11. Conteneur d'injection

**Classe concernée :** `Container`

Le conteneur est chargé de créer les objets et de fournir leurs dépendances.

**Avantage :** évite de créer manuellement toutes les dépendances.

**Limite :** peut être difficile à comprendre au début.

**Extrait :**

```php
$service = $container->get(CreerSalleService::class);
```

---

## 12. Autowiring

**Classe concernée :** `Container`

L'autowiring permet au conteneur de détecter automatiquement les dépendances d'une classe grâce aux types indiqués dans le constructeur.

**Avantage :** réduit la configuration manuelle.

**Limite :** fonctionne moins facilement avec certaines dépendances qui nécessitent une configuration particulière.

**Extrait :**

```php
public function __construct(
    SalleRepository $repository
) {}
```

Le conteneur comprend qu'il doit fournir un `SalleRepository`.

---

## 13. Inversion de contrôle (IoC)

**Classes concernées :** `Container`, Services, Controllers

L'inversion de contrôle signifie que les objets ne sont pas obligés de créer eux-mêmes leurs dépendances. C'est le conteneur qui s'en charge.

**Avantage :** les classes sont moins dépendantes les unes des autres.

**Limite :** le fonctionnement peut être moins évident lorsqu'on débute.

**Extrait :**

```php
$controller = $container->get(SalleController::class);
```

---

# 14. Les cinq principes SOLID

## S — Single Responsibility Principle

Une classe doit avoir une seule responsabilité principale.

**Exemple :** le `SalleRepository` s'occupe de l'accès aux données, tandis que `SalleService` s'occupe de la logique métier.

**Avantage :** code plus facile à comprendre et à modifier.

**Limite :** peut créer beaucoup de petites classes.

---

## O — Open/Closed Principle

Une classe doit pouvoir être étendue sans devoir modifier son fonctionnement existant.

**Exemple :** ajouter un nouveau Validator sans modifier les autres Validators.

**Avantage :** réduit les risques de casser le code existant.

**Limite :** demande une bonne organisation des classes.

---

## L — Liskov Substitution Principle

Une classe fille doit pouvoir remplacer sa classe mère sans provoquer de comportement incorrect.

**Exemple :** les différents repositories peuvent respecter la même interface.

**Avantage :** facilite le remplacement d'une implémentation.

**Limite :** nécessite de bien définir les interfaces.

---

## I — Interface Segregation Principle

Une classe ne doit pas être obligée d'utiliser des méthodes dont elle n'a pas besoin.

**Exemple :** préférer plusieurs petites interfaces à une seule grosse interface.

**Avantage :** les dépendances sont plus simples.

**Limite :** peut créer plusieurs interfaces.

---

## D — Dependency Inversion Principle

Les classes doivent dépendre d'abstractions plutôt que de classes concrètes.

**Exemple :**

```php
public function __construct(
    SalleRepositoryInterface $repository
) {}
```

Le service dépend de l'interface et non directement d'une implémentation particulière.

**Avantage :** facilite les tests et le remplacement des composants.

**Limite :** ajoute des interfaces et donc davantage de code.
