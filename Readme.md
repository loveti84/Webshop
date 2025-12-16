# Webshop Project

## Table of Contents

- [Over het Project](#over-het-project)
  - [Eigenschappen](#eigenschappen)
  - [Wat doet deze applicatie?](#wat-doet-deze-applicatie)
- [Project Structuur](#project-structuur)
- [Architectuur & Design Patterns](#architectuur--design-patterns)
  - [Waarom MVC?](#waarom-mvc)
  - [Trade-offs](#trade-offs)
  - [MVC Pattern + Repository](#mvc-pattern-model-view-controller--repository)
- [Database](#database)
  - [Initialisatie](#initialisatie)
  - [Index](#index)
  - [Schema](#schema)
- [Repositories](#repositories)
  - [Implementatie in code](#implementatie-in-code)
  - [Query-uitvoering met PDO](#query-uitvoering-met-pdo)
- [Controllers](#controllers)
  - [Code Implementatie](#code-implementatie)
- [Flow](#flow)
- [Afhandeling Request](#afhandeling-request)
- [Routing](#routing)
  - [Router initialisatie](#1-router-initialisatie)
  - [Routes registreren](#2-routes-registreren-routesphp)
  - [Dispatch](#3-dispatch-route-matching)
  - [Handler uitvoeren](#4-handler-uitvoeren-controllermethod)
- [Flow met routing voorbeeld uit logs](#flow-met-routing-voorbeeld-uit-logs)
- [Frontend Functionaliteit](#frontend-functionaliteit)
  - [Custom Styling](#custom-styling)
- [User Authentication](#user-authentication)
  - [Backend: Session-based Authentication](#backend-session-based-authentication)
  - [Frontend met cookies](#frontend-met-cookies)
- [Security Implementatie](#security-implementatie)
  - [SQL Injection Preventie](#1-sql-injection-preventie)
  - [XSS Preventie](#2-xss-cross-site-scripting-preventie)
  - [Error Handling](#5-error-handling)
- [Validatie](#validatie)
  - [Backend Validatie](#backend-validatie)
  - [Frontend Validatie](#frontend-validatie)

---

## Over het Project

### Eigenschappen

- XAMPP 8.2.12
    - PHP-versie: 8.2.12
    - 10.4.32-MariaDB
- voor installatie: [Installatie.md](/Installatie.md)

### Wat doet deze applicatie?
- **Users kunnen reviews plaatsen over producten**
- Inloggen van users
    - Zonder wachtwoord, enkel met een username (uniek)
    - Opgeslagen in de sessie, frontend houdt een cookie bij
- Productenoverzicht
    - Filtering (zoektermen en gemiddelde rating)
        - hoeveelheid matches
    - Sorteren
    - Paginatie
    - → Dit alles op de databank

- Responsive, perfect voor schermen van 1920x1080 of kleiner (groter werkt, maar moet nog gefinetuned worden)
- Controllers geven 2 soorten responses (succes en error)
- Errors:
    - Validatie, databank connectie-errors, en andere opgevangen
    - er wordt een passende boodschap getoond
- Product(detail)pagina
    - Review toevoegen
        - Validatie op frontend en backend, beide tonen duidelijke errors aan users
        - Per product slechts één review per user (geeft anders een validatie-error)
        - User wordt automatisch aangemaakt
    - Increment count
        - Anti-spam → maar één increment per product per sessie 
        - Bekeken producten worden opgeslagen in de sessie
        - behoudt als user uitloged ()
---


## Project Structuur

```
Webshop/
│
├── public/                      # Publiek toegankelijke bestanden (Entry point)
│   └── index.php               # Front Controller - Single entry point voor alle requests
│
├── src/                        # Applicatie broncode
│   ├── routes.php              # Route definities (URL → Controller mapping)
│   │
│   ├── config/                 # Configuratie bestanden
│   │   └── config.php          # Database credentials (.env loader)
│   │
│   ├── Core/                   # Framework kern componenten
│   │   ├── Router.php          # URL routing en request dispatching
│   │   ├── Database.php        # Singleton PDO database connectie
│   │   ├── Controller.php      # Base controller met helper methods
│   │   ├── Model.php           # Base model met serialisatie
│   │   ├── Validator.php       # Helper Validator classe voor batch validatie of enkele validate
│   │   └── ErrorHandler.php    # Centrale error/404 afhandeling
│   │
│   ├── Controllers/            # Request handlers (Business logic)
│   │   ├── ProductController.php    # Product API endpoints
│   │   ├── ReviewController.php     # Review API endpoints
│   │   ├── UserController.php       # User API endpoints
│   │   └── ViewController.php       # HTML pagina rendering
│   │
│   ├── Repositories/           # Data Access Layer (Database queries)
│   │   ├── ProductRepository.php       # Product CRUD operaties
│   │   ├── ProductReviewRepository.php # Review CRUD operaties
│   │   └── UserRepository.php          # User CRUD operaties
│   │
│   ├── Models/                 # Domain entities (Data structures)
│   │   ├── Product.php
│   │   ├── ProductReview.php
│   │   └── User.php
│   │
│   └── assets/                 # Frontend resources
│       ├── html/               # View templates
│       ├── css/                # Stylesheets (Bootstrap + custom)
│       └── js/                 # JavaScript (jQuery + custom)
│
├── database/                   # Database setup
│   ├── schema.sql             # Tabel definities
│   └── seeds.sql              # Test data
│
├── .env                       # Environment variabelen(staan nu wel nog op git)
└── .htacces                   # Apache configuratie (handles routes)
```

## Architectuur & Design Patterns

### MVC Repository Pattern vs Active Record

Aan de start van het project moest ik een architecturale keuze maken.
Uiteindelijk heb ik gekozen voor het **MVC Repository Pattern**.

---

### Waarom MVC?

* **Separation of Concerns**
    - Elke laag heeft een duidelijke verantwoordelijkheid.
     - Ook in eenzelfde laag kunnen een verantwoordelijkheden logisch afgesplist worden  

* **herbruikbaarheid** 
    - Queries of modelfuncties kunnen meerdere malen gebruikt worden (DRY principle)
* **Code-organisatie**
     - Een logische indeling maakt de code makkelijk te vinden en uit te breiden.
    - Samen werken is makkelijker, door logica af te scheiden is takenverdeling en verantwoordelijkheden makkelijker en wordt er sneller in aparte files gewerkt 
    - Alle SQL op een plek

* **Testbaarheid**
  Goed testbaar; business logic en data access zijn losgekoppeld en daardoor eenvoudig te mocken.


---

### Trade-offs

* MVC is complexer
* Eloquent wordt vaker gebruikt in PHP
* Het Repository Pattern vereist meer bestanden, logica en dependencies 

---

## MVC Pattern (Model–View–Controller) + Repository

Het project is gebaseerd op de MVC-architectuur. Hieronder volgt de beschrijving van de verschillende klassen en hun verantwoordelijkheden.

---

### Model

Niet functioneel in dit project, wegens niet echt nuttige toepassing binnen de scope,toch, vind ik persoonlijk het principe wel belangrijk, de verantwoordelijkheden binnen het MVC-patroon.

* **Data object**
  Bevat geen database-logica.

* **Methoden**

  * `toString()` voor logging en debugging
  * Formatting-functies (bijv. `formatDate()`)
  * Berekeningsmethoden (puur op basis van eigen data)
  * Getter- en setter-methoden (voor validatie)
  * Serialisatie

* **Waarom**
 
  * Model specifieke functies (zie methoden) → separation of concerns.
  * Toepassen overerving
---

### View

* **Front-end**
* Bevat HTML, CSS en JavaScript
* **Data**
  * Ontvangt JSON-data van de controller

---

### Controller

* **Taak**

  * Toepassen van logica
  * Afhandelen van requests
  * Toepassen van validatie
  * Aanspreken repository om queries uit te voeren 
* **Input**
    * GET: `$_GET[parameternaam]` → variabele via URL
    * POST: JSON 
        `$data = json_decode(file_get_contents(filename: 'php://input'), true);`
* **Output**
  * JSON-response (apiControllers)
  * html (viewController)
* **Dependencies**

  * Repositories
  * Uitzondering: `ViewController` stuurt enkel de view door

* Controllers staan tussen het model, de view en de databank.



### Repository

* **Wat**

  * Tussenlaag die alle database-interactie afhandelt voor de controller

* **Taak**

  * Alle database-operaties (CRUD, queries)

* **Dependencies**

* PDO → singleton, elke Repository heeft dezelfde PDO instantie
* Repositories staan tussen de controllers en de databank.

### Andere Architecturale keuzes:

 * Routing: Routes koppelen URL's aan controller-methodes,in plaats van files 
    * Flexibiliteit
        * URLS aanpassen zonder code of bestandstructuut te wijzigen
        * Een controller kan meerde routes afhandelen → logische afscheiding
    * [Routing.php](/src/Core/Router.php) Bevat routing logica
    * [routes.php](/src/Core/Database.php)
    * RESTfull      
    * propere URLS

 * Single entry point [index.php](/public/index.php)
   * Lazy loading van dependencies
   * Beveiliging, voorkom directe toegang tot bestanden
   * Overzichtelijk, duidelijke controle 

 * config [config.php](/src/config/config.php)
    wordt bij start van de entry point ingeladen, hier worden de .env ingeladen, globale constanten,sessie veriabele initiatie of andere configuratie instellingen verzameld worden (zo blijft index.php overzichtelijk)

---

## Database 

### Initialisatie

- **Class Database** ([Database.php](/src/Core/Database.php))
    - **Doel:** Connectie met database

    - **Singleton Pattern:**
        - `private static ?PDO $instance = null`;
        - Alleen `connect()` kan aangeroepen worden
        - Checkt of connectie bestaat zoja geeft die terug anders initialiseerd die deze

    - **connect() methode:**
        - Querystring configuratie
            - Variabelen uit .env
        - Parameters worden ingesteld




```php
self::$instance = 
new PDO(
     "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
     $user,$pass,
     [
 PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, //Gooit exceptions database errors
 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,//Resultaten komen in array van Key( kolomnaam)=> value (waardes) paren
 PDO::ATTR_EMULATE_PREPARES => false //Gebruikt prepared statements
     ]
                );
```


### Index
- Automatisch op primary keys
- Index op product_id in product_reviews
    - **Waarom**
    - `product_id` wordt vaak gebruikt in queries op `product_reviews` voor overzichten en berekeningen per product  
    - Uitgaande van dat er na verloop van tijd veel verschillende producten worden gereviewd:  
        - Weinig variatie in `product_id` is nadelig voor de performance, omdat:  
        - veel rijen dezelfde `product_id` hebben, waardoor er nog steeds veel data moeten doorlopen  
        - bij inserts de index steeds moet worden bijgewerkt, wat extra overhead veroorzaakt  

- **Bewijs werking**
```sql
EXPLAIN 
--uit functie getAverageScore van ProductReviewRepository
SELECT COALESCE(AVG(score), 0) as avg_score FROM product_reviews WHERE product_id = 1; 
--kolom Key : idx_product_id
```

### Schema
zie [schema.sql](\database\schema.sql)

**normalizatie**
- 3NF

**Relaties:**

* Product_reviews bevat een foreign key naar users en products
* Een product review behoort altijd tot een product, maar behoort met 0 of 1 user (user kan verwijderd worden zonder dat review verwijderd wordt)
```
Products(1)---(N)product_reviews(N)---(0-1)Users
```

**Keuze type Attributen en constraints**

-  `created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP` Niet per se nodig maar persoonlijke gewoonte als laatste attribuut

- `username VARCHAR(50) NOT NULL UNIQUE` voor unieke gebruikersnaam (wordt ook gevalideerd), username ook beperkt

- Strings 
    - `TEXT` beschrijving kort of zeer lang
         - Gebruikt bij description en text (bij review), er is geen zekerheid over de lengte en zowel korte als lange teksten moeten displayed worden 
         - Nadeel: aanzienlijk meer opslag en overhead
    - `VARCHAR(N)` voor max lengte
        - Bij namen: varchar(n) hierbij heb je garantie over hoe de styling is (vast max grootte) 
        - Efficiënter



- `FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE`, CASCADE: als er een product verwijderd wordt, is er geen nut in bijhorende reviews, deze worden verwijderd 
- `FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL`, reviews blijven als user verwijderd wordt


## Repositories

Een **repository** staat tussen de controller en de database in en verzorgt alle database-operaties.

---

### Implementatie in code

#### **Initialisatie**

* Het PDO-object wordt geïnjecteerd in de repositories, in `index.php` (`public/index.php`):

```php
// Injecteer PDO-object in repository
$productRepo = new ProductRepository($db); 
$reviewRepo  = new ProductReviewRepository($db);

// Repositories worden geïnjecteerd in de controller
$controller = new ProductController($productRepo, $reviewRepo);
```

**Waarom deze aanpak?**

* **Loose coupling**

  * PDO weet niets van de repository
  * De repository weet niets van de controller
* **Herbruikbaarheid**

  * Repositories (met hun queries) kunnen hergebruikt worden in andere controllers
* **Onderhoudbaarheid**

  * Aanpassingen in bijvoorbeeld database-types, SQL-queries hebben enkel invloed op de repository, niet op de controller
* **Testbaarheid**

  * Controllers kunnen getest worden door en nep-repository te gebruiken (mocken)
    * Geen databank connectie nodig om te testen

---

#### **Query-uitvoering met PDO**

```php


    public function filterByRatingAndKeyWordInTitle(float $minRating = 0, string $keyword = ''): array
    {
        $sql = "SELECT p.*, 
                       COALESCE(AVG(pr.score), 0) as avg_rating,
                       COUNT(pr.id) as review_count
                FROM products p
                LEFT JOIN product_reviews pr ON p.id = pr.product_id";

//De query opbouwen, omdat er moet niet altijd op gefilter moet worden keyword (%% werkt, maar is nutteloze check)
        $params = [];
        if (!empty($keyword)) {
            //plak aan rest van query
            $sql .= " WHERE p.name LIKE ?";
            //? → eerste zoekterm, eerste in lijst
            $searchTerm = "%{$keyword}%";
            $params[] = $searchTerm;
        }
         //group by voor gemiddelde berekening
        $sql .= " GROUP BY p.id";
        if ($minRating > 0) {
            $sql .= " HAVING avg_rating >= ?";
            $params[] = $minRating;
        }
        
        $sql .= " ORDER BY p.created_at DESC";
        // Query voorbereiden: prepare → de databank compileert de structuur
        $stmt = $this->db->prepare($sql);
         
    // De voorbereide query wordt uitgevoerd met parameters
    // Data wordt als data behandeld en niet als SQL → veilig tegen SQL-injection
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
```



---

## Controllers
- Een controller handelt requests af
- Past validatie toe 
- Verwerkt de logica 
- Vangt errors op (validatie en PDO exceptions)
- Geeft een JSON-response terug (of een view bij een ViewController)

### Code Implementatie

#### ViewController
→ Wordt afgehandeld door een **ViewController** die de basis-HTML-structuur teruggeeft doormiddel van `readfile`.
```php
 private function renderView($filename)
    {
        $filePath = BASE_PATH . '/src/assets/html/' . $filename;
        
        
        if (!file_exists($filePath)) {
            http_response_code(404);
            ErrorHandler::show404Page();
            exit;
        }
        readfile($filePath);
        exit;
    }
```


#### API Controllers  
**De `Core\Controller` basisklasse** biedt 2 methoden voor gestandaardiseerde API-responses. Een Controller methode return altijd een van deze

*   **`success()`**: Genereert een gestandaardiseerd JSON-succesantwoord met `data` en een `message`.
*   **`error()`**: Genereert een gestandaardiseerd JSON-foutantwoord met een `message` en een aanpasbare HTTP-statuscode.
*   Beide methoden **gebruiken de onderliggende `json()`-methode** om:
    *   De juiste HTTP-statuscode en `Content-Type: application/json` header in te stellen
    *   De data om te zetten in een JSON-string
    *   De JSON-response naar de client te sturen en het script te beëindigen.
    ```php
        protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);//statuscode
        header('Content-Type: application/json');//header
        echo json_encode($data);//jsonstring
        exit;//script beindigen
    }
     
     protected function error($message = 'Error', $statusCode = 400)
    {
        $this->json([
            'success' => false,
            'message' => $message
        ], $statusCode);
    }
    ```


De `Product,User en ProductReview Controller` erven van Core/Controller over

* Worden aangeroepen door het routeringssysteem op basis van de URL (zie deeltje routing)
* Valideren de parameters uit het verzoek (zoals ID's of zoektermen) (zie deeltje validatie)
* Sturen repositories aan om data op te halen of aan te passen
* Maken een API-response door de parent methode success() of error() aan te roepen


## Flow

Om een pagina weer te geven zijn er **twee requests** nodig: 

1.  **De View weergeven**
    *   Wordt afgehandeld door een **ViewController** die de basis-HTML-structuur teruggeeft.

2.  **De data voor de View inladen**
    *   Wordt afgehandeld door **API-controllers** die JSON-data teruggeven.
    *   Deze API-aanroep kan ook **dynamisch vanuit de geladen view** gebeuren (via AJAX).

**Voordeel:** De data kan dynamisch worden ingeladen en bij een refresh wordt alleen de JSON-data opgevraagd (stap 2), niet de volledige HTML (stap 1).

*   **Minder overhead**
    * Enkel data wordt overgebracht
*   **Betere gebruikerservaring:**
    *   Geen witte schermen tijdens het laden
    *   Mogelijkheid om laadindicatoren te tonen
    *   Alleen de data wordt vervangen, de rest van de pagina blijft intact


## Afhandeling Request

[index.php](public/index.php) is de entry point, **waar het request binnenkomt**.

1.  **Sessie starten** wordt hier gebruikt voor authentication van user

2.  **Globale constanten en environment variabelen inladen** via [config.php](src/config/config.php)
    *   [.env](.env) variabelen worden ingeladen
    *   Andere configuratie-instellingen kunnen hier ook komen (bijv. timezone)

3.  **`spl_autoload_register` instellen**
    *   **Doel:** PHP-bestanden automatisch inladen (autoloading)
    *   **Werking:** Ontvangt de volledige klassenaam (inclusief namespace) → `$class`
    *   **Pad bouwen:** Voegt `APP_PATH` (`/src`, waar de broncode staat) toe aan `$class` en laadt het bestand in
    *   **Waarom?** Geen `require`-statements meer nodig in de rest van het project

    ```php
    spl_autoload_register(function ($class) {
        $file = APP_PATH . '/' . str_replace('\\', '/', $class) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    });
    ```

4.  **Databaseverbinding opzetten** PDO object wordt aangemaakt: `$db = Core\Database::connect();` (zie deel over Database)

5.  **Routing**
Dit omvat het laatste deel van de flow. In [index.php](public/index.php) wordt de **Router Class** ([router.php](src/Core/Router.php)) aangemaakt (zie Routing)
`$router = new Core\Router();`




---

## Routing

Routing is de **laatste en finale stap** in de request-response flow.
Hier wordt de **request URI gekoppeld aan de juiste controller en methode**.

---

### **1. Router initialisatie**

In `index.php`:

```php
// Database connectie
$db = Core\Database::connect();

// Router initialiseren met database connectie
$router = new Core\Router($db);
```

#### Waarom database injecteren in Router?

* **Lazy loading**: Repositories worden **pas aangemaakt wanneer de controller wordt geïnstantieerd**
* **Efficiëntie**: Geen onnodige repository instanties voor routes die niet worden aangeroepen
* **Schaalbaarheid**: Bij 100+ routes worden alleen de benodigde dependencies aangemaakt

#### Lazy Loading vs Eager Loading

**Eager Loading:**
```php
// Alle repositories worden vooraf aangemaakt
$productRepo = new ProductRepository($db);
$reviewRepo = new ProductReviewRepository($db);
$userRepo = new UserRepository($db);

```

**Huidige aanpak (Lazy Loading):**
```php
// Alleen $db wordt doorgegeven
$router = new Router($db);

// Repositories worden pas aangemaakt in Router::createController()
// wanneer de specifieke controller daadwerkelijk wordt opgeroepen
```

---

### **2. Routes registreren (`routes.php`)**

In `routes.php` wordt vastgelegd:

> **Welke URL → welke controller@methode**

Voorbeeld:

```php
$router->get('/products', 'Controllers\ViewController@products');
```

---

#### Wat gebeurt er intern?

In de `Router`-klasse wordt deze route opgeslagen in de lijst routes:

```php
public function get($path, $handler)
{
    $this->routes[] = [
        'method'  => 'GET',
        'path'    => $path,
        'handler' => $handler
    ];
}
```

Resultaat in `$this->routes`:

```php
[
  'method' => 'GET',
  'path' => '/products',
  'handler' => 'Controllers\ViewController@products'
]
```

 **Belangrijk:**
Hier wordt **nog niets uitgevoerd**.
De routes worden **enkel geregistreerd**.

---

### **3. Dispatch (route matching)**

In `index.php`:

```php
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
```

Dit start het **matchen van de request met een route**.

---

#### Wat gebeurt er in `Router::dispatch()`?

#### 1. URI opschonen

```php
$uri = parse_url($requestUri, PHP_URL_PATH);
$uri = str_replace($_ENV['REL_BASE_URL'], '', $uri);
$uri = '/' . trim($uri, '/');
```

* Verwijdert `/webshop` (of `REL_BASE_URL`)
* Zorgt dat `/webshop/api/products` → `/api/products`

---

#### 2. Alle routes doorlopen

```php
foreach ($this->routes as $route)
```

---

#### 3. Controleren op:

* HTTP-methode (`GET`, `POST`, …)
* Exact pad (`/api/products`)

---

#### 4. Match gevonden?

```php
$this->call($route['handler']);
```

---

#### 5. Geen match → 404

```php
ErrorHandler::show404Page();
```

---

### **4. Handler uitvoeren (`controller@method`)**

Voorbeeld handler:

```php
Controllers\ProductController@index
```

---

#### Stap 1: Handler opsplitsen

```php
list($controllerClass, $method) = explode('@', $handler);
```

Resultaat:

* `$controllerClass = Controllers\ProductController`
* `$method = index`

---

#### Stap 2: Controller aanmaken

```php
$controller = $this->createController($controllerClass);
```

---

#### Stap 3: Dependencies injecteren (`createController()`)

De Router gebruikt een **switch statement** voor lazy loading van dependencies:

```php
private function createController($controllerClass)
{
    // Lazy loading: create dependencies only when needed
    switch ($controllerClass) {
        case 'Controllers\\ProductController':
            $productRepo = new \Repositories\ProductRepository($this->db);
            $reviewRepo = new \Repositories\ProductReviewRepository($this->db);
            return new $controllerClass($productRepo, $reviewRepo);
            
        case 'Controllers\\ReviewController':
            $reviewRepo = new \Repositories\ProductReviewRepository($this->db);
            $productRepo = new \Repositories\ProductRepository($this->db);
            $userRepo = new \Repositories\UserRepository($this->db);
            return new $controllerClass($reviewRepo, $productRepo, $userRepo);
            
        case 'Controllers\\UserController':
            $userRepo = new \Repositories\UserRepository($this->db);
            $reviewRepo = new \Repositories\ProductReviewRepository($this->db);
            return new $controllerClass($userRepo, $reviewRepo);
            
        default:
            // ViewController and others have no dependencies
            return new $controllerClass();
    }
}
```

**Hoe werkt dit?**

1. **API-controller wordt opgeroepen**
   * De switch-case matcht de controller naam
   * **Alleen de benodigde repositories** worden geïnstantieerd
   * Controller wordt aangemaakt met dependencies

2. **ViewController wordt opgeroepen**
   * `default` case wordt gebruikt
   * **Geen repositories** worden aangemaakt
   * Lege controller instantie wordt geretourneerd

#### Voordelen

* **Efficiënt**: Geen onnodige object creatie
* **Schaalbaar**: Bij 10 controllers worden alleen de nodige dependencies geladen
* **Overzichtelijk**: Duidelijk welke controller welke repositories nodig heeft

#### Trade-offs

* **Handmatige configuratie**: Bij nieuwe controllers moet switch-case worden uitgebreid
* **Geen auto-wiring**: Frameworks zoals Laravel doen dit automatisch via reflection



### Stap 4: Methode uitvoeren

```php
$controller->$method();
```

Bijvoorbeeld:

```php
ProductController::index();
```

Dit is het **eindpunt van de routing**


## Flow met routing voorbeeld uit logs 

* In onderstaande log wordt er van de productpagina (referer) naar een prodcut pagina gegaan, en een review geplaatst
* De view wordt maar  1 keer geladen 

---A--- VIEW LADEN
routes.php loaded, referer: http://localhost/webshop/products
Dispatch Route founded! GETpath: /product Handler Controllers\\ViewController@product, referer: http://localhost/webshop/products
Calling controller: Controllers\\ViewController->product, referer: http://localhost/webshop/products
ViewController: HTML output completed for product.html, referer: http://localhost/webshop/products

---B---PRODUCT DATA OPHALEN
routes.php loaded, referer: http://localhost/webshop/product?id=1
Dispatch Route founded! GETpath: /api/product Handler Controllers\\ProductController@show, referer: http://localhost/webshop/product?id=1
Calling controller: Controllers\\ProductController->show, referer: http://localhost/webshop/product?id=1

---C---REVIEW INDIENEN EN VALIDEREN (VALIDATIEFOUT)
routes.php loaded, referer: http://localhost/webshop/product?id=1
Dispatch Route founded! POSTpath: /api/reviews Handler Controllers\\ReviewController@create, referer: http://localhost/webshop/product?id=1
Calling controller: Controllers\\ReviewController->create, referer: http://localhost/webshop/product?id=1
 Validatiefouten: Score moet minimaal 1 zijn, referer: http://localhost/webshop/product?id=1

---D---REVIEW OPNIEW INDIENEN
routes.php loaded, referer: http://localhost/webshop/product?id=1
Dispatch Route founded! POSTpath: /api/reviews Handler Controllers\\ReviewController@create, referer: http://localhost/webshop/product?id=1
 Calling controller: Controllers\\ReviewController->create, referer: http://localhost/webshop/product?id=1

---E---PRODUCTDATA HERLADEN
routes.php loaded, referer: http://localhost/webshop/product?id=1
 Dispatch Route founded! GETpath: /api/product Handler Controllers\\ProductController@show, referer: http://localhost/webshop/product?id=1
 Calling controller: Controllers\\ProductController->show, referer: http://localhost/webshop/product?id=1



## Frontend Functionaliteit

### loads
in alle htmls, callback gebruiken om na het laden iets te runnen (in dit geval de gebruiker zijn naam invullen als hij is ingelogd)
```js
        $('#header-placeholder').load('/webshop/src/assets/html/includes/header.html', function() {
            updateHeaderAuth();
        });
        $('#footer-placeholder').load('/webshop/src/assets/html/includes/footer.html');
```

### [AlertModal.js](src/assets/js/alertModal.js)
* Verbeterde versie van build-in alert 
*  Dit script injecteert HTML voor de alert-modal in de pagina zodra het script wordt geladen
* Aparte layout voor types Succes, failed, info
* Javascript laadt pas wanneer 
* `showalert` bewerkt de classes zodat de model zichtbaar volgends type
* `window.showAlert = showAlert;` maakt functie showalert globaal beschikbaar

### Dynamisch genereren HTML elementen
* **Probleem:** bij producten moeten HTML elementen (bijv. card) meerdere malen gekopieerd worden met juiste data
    * Duidelijk en overzichtelijk, styling kan makkelijk gebeuren, niet zoeken in html pagina
    * De eigenlijke variabelen kunnen met selectors aangebracht worden
```js 
 const reviewTemplate = $(`
        <div class="border-bottom pb-3 mb-3">
            <div class="d-flex justify-content-between">
                <h6 class="review-name"></h6>
                <div class="review-stars"></div>
            </div>
            <small class="text-muted review-username"></small>
            <p class="mt-2 mb-1 review-text"></p>
            <small class="text-muted review-date"></small>
        </div>
    `);

    reviews.forEach(review => {
        // Clone template and populate with data
        const $review = reviewTemplate.clone();
        
        $review.find('.review-name').text(review.name || '');
        $review.find('.review-stars').text(renderStars(review.score));
        $review.find('.review-username').text(review.username ? '@' + review.username : 'Onbekend');
        $review.find('.review-text').text(review.text);
        $review.find('.review-date').text(new Date(review.created_at).toLocaleDateString());

        $reviewsList.append($review);
    });
}
```

### Libraries (Alles staat lokaal)
 * bootstrap**
    - bootstrap.min.css
    - bootstrap-icons-min.css
    - bootrstrap-icons.woff

 * jQuery
 
 ### Custom Styling
* probleem scalen grote schermen 
    * apllicatie gemaakt voor 1920-1080 schermen of kleiner, Op grotere schermen (zoals 4K of 8K) worden iconen, knoppen en tekst  "kleiner" en rechter en linker whitespaces groter
    * `@media` gebruikt om elementen te scalen afhankelijk van de grote het scherm
        - ver van ideaal, er is nog veel "tweak"-werk nodig

```css
@media (min-width: 1920px) {
    html {
        font-size: 18px;
    }
    
    .bi {
        font-size: 1.25em;
    }
    
    .btn {
        font-size: 1.1rem;
        padding: 0.625rem 1.375rem;
    }
    
    .btn-sm {
        font-size: 1rem;
        padding: 0.5rem 1rem;
    }
    /*...    
```
    

## User Authentication

### Backend: Session-based Authentication

**Sessie initialisatie:**
* Server-side storage
* Persisteert over verschillende requests
* Wordt gestart in `index.php`:

```php
<?php
// In index.php - Start sessie voor authentication
session_start();
```

---

#### `UserController::register()`

**Flow:**
1. Valideert username (3-50 tekens, alleen letters/cijfers/underscore/streepje)
2. Valideert name (1-100 tekens)
3. Checkt of username uniek is → gooit 409 error bij duplicaat
4. Creërt user in database
5. **Slaat user op in sessie:**
   ```php
   $_SESSION['user'] = [
       'id' => $user['id'],
       'username' => $user['username'],
       'name' => $user['name']
   ];
   ```
6. Returnt user data in JSON response

---

#### `UserController::login()`

**Flow:**
1. Valideert username (3-50 tekens)
2. Zoekt user in database via `UserRepository::findByUsername()`
3. Gooit 404 error als user niet bestaat
4. **Slaat user op in sessie:**
   ```php
   $_SESSION['user'] = [
       'id' => $user['id'],
       'username' => $user['username'],
       'name' => $user['name']
   ];
   ```
5. Returnt user data in JSON response

**Belangrijk:** Geen wachtwoord verificatie - login gebeurt enkel op basis van username

---

#### `UserController::current()`

**Doel:** Check of user ingelogd is

**Flow:**
1. Checkt of `$_SESSION['user']` bestaat
2. Returnt user data als deze bestaat
3. Gooit 401 error als niet ingelogd:
   ```php
   if (!isset($_SESSION['user'])) {
       $this->error('Not authenticated', 401);
   }
   ```

**Gebruik:** Frontend checkt via AJAX of user ingelogd is

---

#### `UserController::logout()`

**Flow:**
1. Verwijdert user uit sessie:
   ```php
   unset($_SESSION['user']);
   ```
2. Returnt success response
3. Session blijft bestaan (enkel user data wordt verwijderd)

**Waarom niet `session_destroy()`?**
* Sessie kan gebruikt worden voor andere data
    * Visited Products voor  anti spam

---

### Frontend met cookies
* **Waarom cookies:** bevat enkel de naam en de username, kan gebruikt worden om bijv. automatisch formulieren op voorhand in te vullen (naam en username)

#### [login.js](/src/assets/js/login.js)
* Als login succesvol verlopen is, set deze de cookie
```javascript
$.ajax({
    url: '/webshop/api/users/login',
    method: 'POST',
    data: JSON.stringify({ username: username }),
    success: function(response) {
        if (response.success && response.data.user) {
            // Session is nu actief op server
               // Save user data to cookie
                    setUserDataCookie(response.data.user);
            window.location.href = '/webshop/products';
        }
    },
    error: function(xhr) {
        if (xhr.status === 404) {
            registerUser(username, name);  // Probeer registratie
        }
    }
});
```

#### [auth.js](/src/assets/js/auth.js)

**Geladen in elke HTML pagina**

**Global variable:**
```javascript
let currentUser = null;  // Cached user data
```

**`getUserData(callback)`** - Haalt user op van dde coockie:
```javascript
function getUserData(callback) {
    // Get from cookie
    const userData = getUserDataFromCookie();
    currentUser = userData;
    
    if (callback) {
        callback(userData);
    }
    
    return userData;
}
```

**`updateHeaderAuth()`** - Update navbar met user info:
```javascript
function updateHeaderAuth() {
    getUserData(function(userData) {
        if (userData) {
            // Toon dropdown met naam en logout button
            $loginLink.parent().html(`
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle">
                        <i class="bi bi-person-circle"></i> ${escapeHtml(userData.name)}
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" id="logoutLink">Uitloggen</a></li>
                    </ul>
                </li>
            `);
        } else {
            // Toon login link
            $loginLink.attr('href', '/webshop/login');
        }
    });
}
```

**`logout()`** - Logout functie:
```javascript
function logout() {
    $.ajax({
        url: '/webshop/api/user/logout',
        method: 'POST',
        success: function() {
            currentUser = null;
            clearUserDataCookie();  // Clear legacy cookie
            window.location.href = '/webshop/products';
        }
    });
}
```

**Cookie Helper Functions:**
* `setUserDataCookie()` - Voor backwards compatibility
* `getUserDataFromCookie()` - Fallback als sessie niet werkt
* `clearUserDataCookie()` - Cleanup bij logout

---



**Login flow:**
1. Valideer username en name (frontend)
2. POST naar `/api/users/login` met username
3. **Als 404 (user bestaat niet):** POST naar `/api/users/register`
4. **Bij succes:** Server slaat user op in sessie
6. **creeert cookie** met userdata
5. Redirect naar `/webshop/products`



### Opmerking
* Authenticatie met session theoretisch toegepast
    * Kan gebruikt worden om controllers af te zonderen
    * Heeft nu eigenlijk geen functionaliteit 
    * Is geen echte authenticatie want er is geen wachtwoord gebruikt


---

## Security Implementatie

### 1. SQL Injection Preventie

**Methode:** PDO Prepared Statements


**Waarom veilig?**
- Parameters worden gescheiden van SQL query
- Database driver escaped special characters
- Aanvaller kan geen SQL code injecteren

---

### 2. XSS (Cross-Site Scripting) Preventie

Cross-Site Scripting (XSS) is een beveiligingsrisico waarbij aanvallers kwaadaardige scripts in webpagina’s injecteren via gebruikersinput, zoals comments of formulieren. Omdat ook legitieme gebruikers speciale tekens kunnen invoeren, wordt escaping toegepast: hierbij worden speciale tekens omgezet naar onschuldige tekst, zodat ze niet als HTML of JavaScript worden uitgevoerd. Tijdens het ontwikkelproces had ik besloten dit beter te bekijken

**3. Frontend: Escaping bij HTML-weergave**

#### Voor HTML Content (tussen tags):
probleem 
```javascript
<p><script>alert('xss')</script></p>
// dus 
element.innerHTML = userInput; // onveilig
```

Oplossing:
```javascript
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;  // textContent escapt automatisch
    return div.innerHTML;    // Geeft ge-escapete HTML terug
}
// voor setting
element.textContent = userInput;
//of
$('#title').text(product.name); 

```

#### Voor HTML Attributen:
Komt minder vaak voor, omdat het om acties gaat (onclick, onerror, onload), maar mag niet over het hoofd gezien worden.

Probleem: 
```javascript
<p onclick="alert('xss')">klik me</p>
```


---

### Error Handling

* Validatie errors 
* Initialisatie errors (voordat controllers geïnitialiseerd zijn)
* Database errors (tijdens het uitvoeren van de query)


**Custom Error Pages:**
Algmene errors, Deze kunnen overal worden aangesproken, bij viewCOntroller, initializatie databank,Routing zodat exit van het script eindigt met een  consistente message voor de gebruiker 
- 404 pagina: `ErrorHandler::show404Page()`
- 500 pagina: `ErrorHandler::showErrorPage()`


**Waarom?**

**De gebruiker krijgt geen witte schermen te zien**


## Validatie

### Waarom validatie?

* **Data-integriteit**
  * Verantwoordelijkheid data Integriteit ligt bij controllers
  * Zorgt dat alleen correcte data in de database terechtkomt
* **Security**
  * Voorkomt SQL injection en XSS aanvallen
  * Limiteert input lengte en type
* **User Experience**
  * Duidelijke, directe foutmeldingen
  * Real-time feedback in frontend
* **Business Logic**
  * Afdwingen van regels (bijv. rating moet 1-5 zijn)

---

### Backend Validatie

#### Locatie
* **Core/Validator.php** - Validator class met statische en instance methodes
* **Core/Controller.php** - Helper methodes voor quick validation
* **Controllers/** - Daadwerkelijke validatie-implementatie

---

#### Validator Class [Validator.php](src/Core/Validator.php)

**1. Statische Validatie**

Gebruikt voor enkelvoudige parameters zoals ID's en filters:

```php
// In ProductController::show()
try {
    $id = Validator::validateInt($_GET['id'] ?? null, 'Product ID', 1);
} catch (ValidationException $e) {
    $this->error($e->getMessage(), 400);
}
```


---

**2. Batch validatie**

Gebruikt voor complexe formulieren met meerdere velden:

```php
// In ReviewController::create()
$validator = $this->validator()
    ->string($data['username'] ?? null, 'Username', 50, 3)
    ->string($data['name'] ?? null, 'Name', 100, 1)
    ->int($data['product_id'] ?? null, 'Product ID', 1)
    ->string($data['text'] ?? null, 'Review text', 1000, 4)
    ->int($data['score'] ?? null, 'Score', 1, 5);


$validator->custom('Username', function($username) {
    return $username && preg_match('/^[a-zA-Z0-9_-]+$/', $username);
}, 'Username must contain only letters, numbers, underscore, and dash');

if ($validator->fails()) {
    $this->error($validator->errorMessage(), 400);
}

$username = $validator->get('Username');
```



---

#### Validator Methodes

**Beschikbare validaties:**
```php
validateInt($value, $fieldName, $min = null, $max = null, $required = true)
validateFloat($value, $fieldName, $min = null, $max = null, $required = true)
validateString($value, $fieldName, $maxLength = 0, $minLength = 0, $required = true)
custom($fieldName, callable $validator, $errorMessage = null)
```

**Custom Validation**
* Gebruik een callable (functie/closure) als validator. Deze functie moet precies één parameter accepteren: de waarde die je wilt testen. De functie moet een bool teruggeven.

```php


 $validator->custom('Username', function($username) {
            return $username && preg_match('/^[a-zA-Z0-9_-]+$/', $username);
        }, 'Username must contain only letters, numbers, underscore, and dash');
```



---

#### Error Responses

**Gestandaardiseerde API responses:**
```json
{
  "success": false,
  "message": "Username moet minimaal 3 tekens bevatten"
}
```

**Waarom dit formaat?**
* Consistent tussen alle endpoints
* Machine-readable (`success` boolean)
* User-friendly (`message` in Nederlands)

---

### Frontend Validatie

#### Locatie
* **assets/js/product.js** - Review formulier validatie
* **assets/js/products.js** - XSS preventie helpers

---

#### Real-time Form Validatie

**Validation Rules Object:**
```javascript
const validationRules = {
    reviewUsername: {
        fieldId: 'reviewUsername',
        errorId: 'usernameError',
        validate: function(value) {
            const errors = [];
            
            if (!value) {
                errors.push('Gebruikersnaam is verplicht');
            } else {
                if (!/^[a-zA-Z0-9_-]+$/.test(value)) {
                    errors.push('Mag alleen letters, cijfers, _ en - bevatten');
                }
                if (value.length < 3) {
                    errors.push('Minimaal 3 tekens');
                }
                if (value.length > 20) {
                    errors.push('Maximaal 20 tekens');
                }
            }
            
            return errors.length > 0 ? errors : null;
        }
    },
    reviewText: {
        validate: function(value) {
            if (!value) return ['Beoordelingstekst is verplicht'];
            if (value.length > 1000) return ['Maximaal 1000 tekens'];
            return null;
        }
    }
    // ... meer velden
};
```

**Event-driven validatie:**
```javascript
function setupFieldValidation() {
    Object.keys(validationRules).forEach(function(fieldId) {
        $('#' + fieldId).on('input change', function() {
            validateField(fieldId);  // Real-time validatie
        });
    });
}
```

**Form submit validatie:**
```javascript
$('#reviewForm').on('submit', function(e) {
    e.preventDefault();
    
    if (!validateAllFields()) {
        return;  // Stop submit als validatie faalt
    }
    
    // Submit via AJAX
    $.ajax({ ... });
});
```

---


---


