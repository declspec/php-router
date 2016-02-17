# php-router:

#### a featherweight PHP router

**php-router** is designed to get out of your way. It provides the bare-essentials in terms of functionality 
and makes no assumptions about how you want to write your application. 

It is **not** an application development framework. If you like the concept behind **php-router** and 
are interested in a similarly bare-bones development framework, take a look at the
[**php-wireframe**](https://github.com/declspec/php-wireframe) project!

### Route handlers

Each route requires a **handler**, which can be any `callable` PHP expression 
(see the [callable](http://php.net/manual/en/language.types.callable.php) documentation for what values can be used as a handler). 

The handler function is invoked with two arguments, `$req` and `$res`, holding the current `Request` and `Response` respectively.
A route handler **does not** need to completely handle the request at all times. Returning `FALSE` (**FALSE**, not a 'falsey' expression) from the route handler allows another 
route handler to attempt to fulfill the request. More information about partial handlers can be found in the **middleware** section.

### Creating a router
Before setting up your routes, you must first construct an instance of the `Router` class. 
The class has an optional constructor parameter which allows you to specify a `baseUrl` for the router, 
which is useful if your application isn't running in the domain root directory.

```php
require("php-router/router.php");

// Example 1: No base URL:
$router = new Router();

// Example 2: Static base URL:
$router = new Router("/base-directory");

// Example 3: Dynamic base URL depending on where the current file resides
$baseUrl = substr(__DIR__, strlen($_SERVER["DOCUMENT_ROOT"]));
$router = new Router($baseUrl);
```

### Setting up routes

The router exposes convenience methods for the following HTTP methods => `GET`, `POST`, `PUT`, `DELETE`
as well as `all` if you want to match all methods, or `route` if you want to specify your own HTTP method.

When creating a route you need to supply *at least* a path and a **route handler**. 
You may also supply **middleware** that runs prior to the main route handler as the second argument, but this is not required (see the **middleware** section for details)

```php
// Most basic route, simple path match with a closure as the route handler.
$router->get('/', function($req, $res) {
	// $req and $res are 'Request' and 'Response' instances, respectively.
    // They are made available for your convenience but you're under no obligation to
    // use them. You can use 'echo' and friends if you prefer.
	$res->send('<h1>Hello, world</h1>');
});

// You can also specify URL parameters to capture variables from the request:
$router->get('/user/:id', function($req, $res) {
	// Response::send(...) automatically sets the Content-Type (unless already set)
    // and deduces what to do with the provided argument. In this case it will json-encode
    // the parameters and set the content-type to "application/json"

    // "/user/123" => { "id" = "123" }
	$res->send($req->params);
});

// Further to the last example, you can also restrict the parameters with simple regular expressions:
$router->get('/user/:id(\d+)', function($req, $res) {
	// Route will run for "/user/123", but not "/user/foobar"
	$res->send($req->params);
});
```

### Handling the current request

Once your routes have been configured, handle the current request by calling `Router::run()`:

```php
// That was difficult
$router->run();
```
