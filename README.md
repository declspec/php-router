# php-router:

#### a featherweight PHP router

**php-router** is designed to get out of your way. It provides the bare-essentials in terms of functionality 
and makes no assumptions about how you want to write your application. 

It is **not** an application development framework. If you like the concept behind **php-router** and 
are interested in a similarly bare-bones development framework, take a look at the
[**php-wireframe**](https://github.com/declspec/php-wireframe) project!

### Basic example

If you're just interested in seeing a very basic working example and don't want to read any further. Fear not:

```php
require("php-router/router.php");

$router = new Router();

// Define the index
$router->all('/', function($req, $res) {
    $res->send('<h1>Hello world, from php-router</h1>');
});

// Define a 404 handler as the last regular route
$router->all('*', function($req, $res) {
    $res->status(404)->send('<h1>Not Found</h1>');
});

// Define an error handler for any exceptions that occur in the routes
$router->error(function($ex, $req, $res) {
    $html = "<h1>Internal Server Error</h1>\n" .
    	"<strong>" . htmlentities($ex->getMessage(), ENT_QUOTES, "UTF-8") . "</strong>\n" .
        "<pre><code>" . htmlentities($ex->getTraceAsString(), ENT_QUOTES, "UTF-8") . "</code></pre>";
    
    $res->status(500)->send($html);
});

$router->run();
```

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

// A route handler can be any 'callable' expression, including an instance method on a class:
require("controller.php"); // contains a class called 'Controller' with a method 'home'
$controller = new Controller();

$route->get("/home", array($controller, "home")); 
```

### Middleware

Middleware is just a fancy name for partial route handlers. That is, route handlers that may not completely handle the request. 
The function signature is identical to any other route handler. The only difference between the two is that middleware would usually 
contain an explicit `return false` to indicate that it hasn't completely handled the request, allowing other route handlers to run 
after the middleware.

Middleware is typically used to apply re-usable functionality to multiple routes, without cluttering up their individual handlers with
large amounts of duplicated code.

One of the most common applications of middleware is authentication. By defining the authentication logic as a piece of middleware, 
you can avoid bloating your route handlers with authentication checks.

```php
$authMiddleware = function($req, $res) {
    // explicit return false to indicate that the request hasn't been handled (when the user is authenticated)
	if (isset($req->user))
    	return false; 
      
    $res->redirect("/auth/login?returnUrl=" . urlencode($req->url));
};

$router->get("/user", $authMiddleware, function($req, $res) {
    // No need to check $req->user as this route handler won't execute if the middleware failed.
    $res->send($req->user);
});
```

Global middleware can also be created. Think of global middleware as a route that matches any and all paths (regardless of HTTP method). 
It will execute after any previously defined routes and before any routes defined after its definition. For example:

```php
// Middleware won't run for this route handler
$router->get('/', function($req, $res) {});

$router->middleware(function($req, $res) {
    // Log the request details in an HTML comment for debugging.
    // NOTE: Sending data in middleware is not a good idea in the real world.
    // once data has been sent by the server, no routes can change the HTTP headers (i.e. do redirects, set the status code, etc.)
    $data = "<!--\n" . print_r($req, true) . "\n-->\n";
    $res->send($data);
    
    // Let the router know the middleware didn't handle the request completely.
    return false;
});

// The middleware will run for this and all following routes.
$route->get('/test', function($req, $res) { 
    $res->send('<h1>Testing Middleware</h1>');
});
```

### Handling the current request

Once your routes have been configured, handle the current request by calling `Router::run()`:

```php
// That was difficult
$router->run();
```
