# Router @MinasRouter

![a](https://user-images.githubusercontent.com/10711363/121407187-6b904a00-c935-11eb-84a0-5fae3d7a9deb.PNG)

[![Maintainer](http://img.shields.io/badge/maintainer-@nicollassilva-blue.svg?style=flat-square)](https://github.com/nicollassilva)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nicollassilva/minasrouter/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/nicollassilva/minasrouter/?branch=master)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Latest Version](https://img.shields.io/github/release/nicollassilva/minasrouter.svg?style=flat-square)](https://github.com/nicollassilva/minasrouter/releases)
[![Build Status](https://scrutinizer-ci.com/g/nicollassilva/minasrouter/badges/build.png?b=master)](https://scrutinizer-ci.com/g/nicollassilva/minasrouter/build-status/master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/nicollassilva/minasrouter/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)

#### MinasRouter is simple, fast and extremely readable for routes. Create and manage your routes in just a few steps.

> Simples, rápido e MUITO funcional. **MinasRouter** é um componente de rotas PHP para projetos MVC! Foi feito para abstrair os verbos RESTfull `(GET, POST, PUT, PATCH, DELETE)` e renderizar de forma simples e fácil no controller da aplicação.

> `MinasRouter` trabalha e processa todas as informações de forma isolada, facilitando o processo para o desenvolvedor e acelerando o desenvolvimento/andamento do projeto.

Simple, fast and VERY functional. **MinasRouter** is a PHP routes component for MVC projects! It is designed to abstract the RESTfull (GET, POST, PUT, PATCH, DELETE) verbs and render them simply and easily in the application controller.

**MinasRouter** works and processes all information in isolation, facilitating the process for the developer and accelerating the development/progress of the project.

# Highlights @MinasRouter

- In a few minutes you can create routes for your application or api `(Yes, it really is a few minutes)`
- Fast and Easy middleware system
- Respect the RESTfull verbs and has great functions to deal with them
- Route customization, regex in dynamic parameters and optional parameters
- Spoofing for verbalization and data control (FormSpoofing)
- Carries dynamic parameters to controller arguments 
- Easy routing groups and fast create
- It has a **Request Class** to control and work with route data

> With **two lines** you start using routes!

# Application example

Maybe you are a person who learns by watching, you can access the [example](https://github.com/nicollassilva/minasrouter/tree/master/example) folder, which contains an folders architecture example of how we use MinasRouter.

# Tests

> You can check all tests done [here](https://github.com/nicollassilva/minasrouter/tree/master/tests).
> Enjoy!

# Installation

MinasRouter is available via `Composer require`:

```json
"require" {
    "nicollassilva/minasrouter": "^1.0"
}
```
or run in **terminal**:

```sh
composer require nicollassilva/minasrouter
```

# Documentation

### 1. Configuration
- [Redirect to Public Folder](https://github.com/nicollassilva/minasrouter#redirect-to-public-folder)
* **Public Folder Configuration**
- [Apache](https://github.com/nicollassilva/minasrouter#apache)
- [Nginx](https://github.com/nicollassilva/minasrouter#apache)
### 2. Routes
- [My first Route](https://github.com/nicollassilva/minasrouter#the-first-route)
- [RESTfull Verbs](https://github.com/nicollassilva/minasrouter#restfull-verbs)
* **Customization**
- [Named Routes](https://github.com/nicollassilva/minasrouter#named-routes)
- [Dynamic Parameters (Required and Optional)](https://github.com/nicollassilva/minasrouter#dynamic-parameters-required-and-optional)
- [Validating a Dynamic Parameter](https://github.com/nicollassilva/minasrouter#validating-a-dynamic-parameter)
* **Middlewares**
- [Set Global Middlewares](https://github.com/nicollassilva/minasrouter#set-global-middlewares)
- [Attach in a route](https://github.com/nicollassilva/minasrouter#attach-in-a-route)
* **Route Groups**
- [All Methods](https://github.com/nicollassilva/minasrouter#route-groups)
- [Named Group](https://github.com/nicollassilva/minasrouter#named-group)
- [Prefixed Group](https://github.com/nicollassilva/minasrouter#prefixed-group)
- [Default Namespace Group](https://github.com/nicollassilva/minasrouter#default-namespace-group)
- [Default Middleware Group](https://github.com/nicollassilva/minasrouter#default-middleware-group)
- [Nested Group Methods](https://github.com/nicollassilva/minasrouter#nested-group-methods)
* **Others**
- [Route Redirect](https://github.com/nicollassilva/minasrouter#route-redirect)
- [Route with Individual Middleware in Group](https://github.com/nicollassilva/minasrouter#route-with-individual-middleware-in-group)
- [Route with Different Name in Group](https://github.com/nicollassilva/minasrouter#route-with-different-name-in-group)
- [Fallback Routes](https://github.com/nicollassilva/minasrouter#fallback-routes)

### 3. Request Route
- [Introduction](https://github.com/nicollassilva/minasrouter#request-route)
* **Methods**
- [Request Path](https://github.com/nicollassilva/minasrouter#retrieving-the-request-path)
- [Request URL](https://github.com/nicollassilva/minasrouter#retrieving-the-request-url)
- [Request Headers](https://github.com/nicollassilva/minasrouter#request-headers)
- [Request IP Address](https://github.com/nicollassilva/minasrouter#request-ip-address)
- [Retrieving Data](https://github.com/nicollassilva/minasrouter#retrieving-data)
- [Request Method](https://github.com/nicollassilva/minasrouter#retrieving-the-request-method)

# Introduction

> Para começar a usar o MinasRouter, todo o gerenciamento da navegação deverá ser redirecionado para o arquivo padrão de rotas do seu sistema, que fará todo o processo de tratamento das rotas e retornará o que foi por padrão configurado. Configure conforme os exemplos abaixo e de acordo com seu servidor.

To start using MinasRouter, all navigation management must be redirected to your system's default route file, which will do the entire route handling process and return what was configured by default.
Configure according to the examples below and according to your server.

## Redirect to Public Folder

```apacheconf
RewriteEngine on
Options All -Indexes

RewriteCond %{HTTPS} on
RewriteCond %{HTTP_HOST} (www\.)?localhost
RewriteRule (.*) https://%{HTTP_HOST} [L,R=301]

RewriteCond %{REQUEST_URI} !public/
RewriteRule (.*) /public/$1 [L]
```

### apache

```apacheconf
RewriteEngine On
#Options All -Indexes

# Handle Authorization Header
RewriteCond %{HTTP:Authorization} .
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

# URL Rewrite
RewriteCond %{SCRIPT_FILENAME} !-f
RewriteCond %{SCRIPT_FILENAME} !-d
RewriteRule ^(.*)$ index.php?route=/$1 [L,QSA]

### Do not use the settings below if you are using developing in a local environment, use only in production.

## WWW Redirect
#RewriteCond %{HTTP_HOST} !^www\. [NC]
#RewriteRule ^ https://www.%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

## HTTPS Redirect
#RewriteCond %{HTTP:X-Forwarded-Proto} !https
#RewriteCond %{HTTPS} off
#RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

```

### nginx

```nginx
location / {
  if ($script_filename !~ "-f"){
    rewrite ^(.*)$ /index.php?route=/$1 break;
  }
}
```

# The first Route

To start the components:

```php
<?php
    require __DIR__ . "/../vendor/autoload.php";
    
use MinasRouter\Router\Route;

// The second argument is optional. It separates the Controller and Method from the string
// Example: "Controller@method"
Route::start("http://yourdomain.com", "@");

Route::get("/", function() {
    // ...
});

// ... all routes here

// You will put all your routes before this function
Route::execute();
```

### RESTfull Verbs

Methods:

| Function |     Parameter    | Parameter   | Parameter |
|:--------:|:----------------:|-------------|-----------|
|    get   |    String $uri   |  $callback  |           |
|   post   |    String $uri   |  $callback  |           |
|    put   |    String $uri   |  $callback  |           |
|   patch  |    String $uri   |  $callback  |           |
|  delete  |    String $uri   |  $callback  |           |
|   match  | Array $httpVerbs | String $uri | $callback |
|    any   |    String $uri   |  $callback  |           |

Example:

```php
Route::get('/users', [\App\Controllers\User::class, 'index']);
Route::post('/users', [\App\Controllers\User::class, 'store']);
Route::put('/users/{id}', [\App\Controllers\User::class, 'update']);
Route::patch('/users/{id}', [\App\Controllers\User::class, 'update']);
Route::delete('/users/{id}', [\App\Controllers\User::class, 'delete']);

// The router allows you to register routes that respond to any HTTP verb:
Route::any('/', function() {
    // ...
});

// Sometimes you may need to register a route that responds to multiple HTTP verbs:
Route::match(["GET", "POST"], "/", function() {
    // ...
});
```

### Named routes

Methods:

| Function |   Parameter  |
|:--------:|:------------:|
|   name   | String $name |
|    as    | String $name |

Example:

```php
Route::get("/users/create", function() {
    // ...
})->name("user.create");

Route::get("/users/2", function() {
    // ...
})->as("user.show");
```

### Dynamic parameters (required and optional)

```php
Route::get("/", function() {
    // ...
})->name("web.index");

Route::get("/user/{id}", function($id) {
    echo $id;
})->name("user.show");

Route::get("/post/{id?}", function($id) {
    if(!$id) {
        // ...
    }
    
    // ...
})->name("post.show");
```

### Validating a dynamic parameter

Methods:

|      Function     |   Parameter   | Parameter     |
|:-----------------:|:-------------:|---------------|
|       where       | Array $params |               |
|     whereParam    | String $param | String $regex |
|    whereNumber    | String $param |               |
|     whereAlpha    | String $param |               |
| whereAlphaNumeric | String $param |               |
|     whereUuid     | String $param |               |

Example:

```php
Route::get("/user/{id}", [\App\Controllers\UserController::class, "show"])
    ->name("user.show")
    ->where(["id" => "[0-9]+"]);

// whereParam is alias of where method
Route::get("/profile/{slug}", [\App\Controllers\UserController::class, "profile"])
    ->name("user.profile")
    ->whereParam("id", "[0-9]+");

Route::get("/book/{id}", [\App\Controllers\BookController::class, "show"])
    ->name("book.show")
    ->whereNumber("id");
```

# Middlewares

Working with middlewares around here is pretty easy, we just need to pass the full names of the classes or set them as global, and from there, we use their alias.

### Set Global Middlewares

**OBS: It is important that you place all routes below. Routes above this class will not have these middlewares as global.**

```php
Route::globalMiddlewares([
    'isLogged' => \App\Middlewares\isLogged::class,
    'isAdmin' => \App\Middlewares\isAdmin::class
]);

// ... all routes
```

### Attach in a route

You may use the middleware method to assign middleware to a route.

```php
Route::get("/musics", function() {
    // ...
})->middleware("isLogged");

Route::get("/musics", function() {
    // ...
})->middleware(["isLogged"]);
```

You may assign multiple middleware to the route by passing an array of middleware names to the middleware method.

```php
Route::get("/musics", function() {
    // ...
})->middleware("first, second");

Route::get("/movies", function() {
    // ...
})->middleware(["first", "second"]);
```

When assigning middleware, you may also pass the fully qualified class name:

```php
use App\Middlewares\VerifyCsrfToken;

Route::get("/series", function() {
    // ...
})->middleware(VerifyCsrfToken::class);

Route::get("/series", function() {
    // ...
})->middleware(App\Middlewares\VerifyCsrfToken::class);
```

# Route Groups

All methods:

|  Function  |      Parameter      | ::function | ->function |
|:----------:|:-------------------:|:----------:|:----------:|
|  namespace |  String $namespace  |     Yes    |     Yes    |
|   prefix   |    String $prefix   |     Yes    |     Yes    |
|    name    |     String $name    |     Yes    |     Yes    |
| middleware | String $middlewares |     Yes    |     Yes    |

> Group methods can be called static way or normal, **don't forget to call a function group** to insert as routes inside the closure.

Examples:

### Named group

```php
Route::name("admin.")->group(function() {
    Route::get("/", function() {
        // admin.index
    })->name("index");
});
```

### Prefixed group

```php
Route::prefix("admin/")->group(function() {
    Route::get("/index", function() {
        // http://localhost/admin/index
    })->name("index");
});
```

### Default namespace group

```php
Route::namespace("App\Controllers")->group(function() {
    Route::get("/user/{id}", ["User", "show"])->name("show");
    // \App\Controllers\User
});
```

### Default Middleware group

```php
Route::middleware(\App\Middlewares\isLogged::class)->group(function() {
    Route::get("/user/{id}", ["User", "show"])->name("show");
});
```

### Nested group methods

```php
Route::namespace("App\Controllers\Admin")
    ->middleware(["isLogged", "isAdmin"])
    ->name("admin.")
    ->prefix("admin")
    ->group(function() {
    // ...
});
```

# Others

### Route with Individual Middleware in group

You can use routes with individual middlewares within a route group.

```php
Route::namespace("isLogged")->group(function() {
    Route::get("/posts", function() {
        // ...
    })->middleware("isAdmin");
    
    // ...
});
```

### Route with Different name in group

Maybe you are wanting a route where you ignore the group name, you can use the second parameter of the name method for that.

```php
Route::name("admin.")->group(function() {
    Route::get("/posts", function() {
        // name: app.posts
    })->name("app.posts", true);
});
```

### Route redirect

Methods:

|      Function     |  Parameter  | Parameter        | Parameter             |
|:-----------------:|:-----------:|------------------|-----------------------|
|      redirect     | String $uri | String $redirect | Int $statusCode = 302 |
| permanentRedirect | String $uri | String $redirect |                       |

Example:

```php
// Returns 302 status code by default.
Route::redirect("/here", "/there");

Route::redirect("/here", "/there", 301);

// permanentRedirect always returns 301
Route::permanentRedirect("/here", "/there");

// You can return an existing route
Route::redirect("/index", "web.index");
```

### Fallback Routes

The fallback route is responsible when there is no route registered with that url address. Whenever there is no route that was requested by the user, the fallback route will be called.

```php
Route::fallback(function() {
    echo 'Route error!';
    // ...
});
```

> OBS: Tenha cuidado caso queira redirecionar para uma rota existente, se nela conter argumentos dinâmicos, ela retornará todo o regex e irá causar erro.

Be careful you redirect to an existing route, because if it has dynamic arguments, it will return the entire regex and error returned.

# Request Route

Each time the route is called and the Closure or controller method is called, you will have as a parameter an instance of \MinasRouter\Http\Request. If the route has dynamic parameters (mandatory or optional), they need to be passed before receiving the Request instance.

|  Function |        Parameter       |     Parameter    |
|:---------:|:----------------------:|:----------------:|
| getParams |                        |                  |
|    path   |                        |                  |
|    url    |                        |                  |
|  fullUrl  |                        |                  |
|   header  |     String $header     |  String $default |
| hasHeader |     String $header     |                  |
|     ip    |                        |                  |
|   query   |     ?String $query     | ?String $default |
|    all    |     ?String $except    |                  |
| getMethod |                        |                  |
|  isMethod | String $expectedMethod |                  |

The dynamic parameters of the route are directly passed in the method together with a instance of Request.

Example:

```php
use \MinasRouter\Http\Request;

Route::get("/", function(Request $request)) {
   // ... 
});

Route::get("/user/{id}", function($id, Request $request)) {
   // ... 
});

Route::get("/posts/{slug?}", function($slug, Request $request)) {
   // ... 
});

Route::get("/book/{slug}", function($slug, Request $request) {
    // Retrieving all dynamic parameters
    print_r($request->getParams());
});

```

The Request method is the method that has all your form data, query parameters, dynamic route parameters, and the entire request header.

# Request Methods

### Retrieving The Request Path

The **path** method returns the request's path information. So, if the incoming request is targeted at **http://localhost/foo/bar**, the path method will return **foo/bar**:

```php
$uri = $request->path();
```

### Retrieving The Request URL

To retrieve the full URL for the incoming request you may use the **url** or **fullUrl** methods. The **url** method will return the URL without the query string, while the **fullUrl** method includes the query string:

```php
$url = $request->url();

$urlWithQueryString = $request->fullUrl();
```

### Request Headers

You may retrieve a request header from the **\MinasRouter\Http\Request** instance using the header method. If the header is not present on the request, null will be returned. However, the header method accepts an optional second argument that will be returned if the header is not present on the request:

```php
$value = $request->header("Header-Name");

$value = $request->header("Header-Name", "default");
```

The **hasHeader** method may be used to determine if the request contains a given header:

```php
if ($request->hasHeader("Header-Name")) {
    // ...
}
```

The **bearerToken** method may be used to retrieve a bearer token from the Authorization header. If no such header is present, null will be returned.

```php
$token = $request->bearerToken();
```

### Request IP Address

The **ip** method may be used to retrieve the IP address of the client that made the request to your website:

```php
$ipAddress = $request->ip();
```

### Retrieving Data

The **query** method will only retrieve values from the query string:

```php
$id = $request->query("id");
```

If the requested query string value data is not present, the second argument to this method will be returned:

```php
$developer = $request->query("developer", "Nicollas");
```

You may call the query method without any arguments in order to retrieve all of the query string values.

```php
$query = $request->query();
```

You can access queryString's and input data directly, how properties of the Request class.

```php
// http://localhost/?foo=bar

$foo = $request->foo;

// <input type="text" name="title" value="MinasRouter">

$title = $request->title;
```

You may retrieve all of the incoming request's input data as an array using the all method. This method may be used regardless of whether the incoming request is from an HTML form or is an XHR request. If you want to nullify some data, you can pass it as a second parameter.

```php
$data = $request->all();

// all, except csrf_token, page
$data = $request->all("csrf_token, page");
```

### Retrieving The Request Method

The **getMethod** method will return the HTTP verb for the request. You may use the **isMethod** method to verify that the HTTP verb matches a given string:

```php
$httpMethod = $request->getMethod();

if ($request->isMethod('POST')) {
    // ...
}
```