<?php
require("src/router.php");

$starttime = microtime(true);

$router = new Router("/php-router");

// Define the index
$router->all('/', function($req, $res) {
    throw new Exception("Bye, world");
    $res->send('<h1>Hello world, from php-router</h1>');
});

// Define a 404 handler as the last regular route
$router->all('*', function($req, $res) {
    $res->status(404)->send('<h1>Not Found</h1>');
});

// Define an error handler for any exceptions that occur in the rotues
$router->error(function($ex, $req, $res) {
    $html = "<h1>Internal Server Error</h1>\n" .
    	"<strong>" . htmlentities($ex->getMessage(), ENT_QUOTES, "UTF-8") . "</strong>\n" .
        "<pre><code>" . htmlentities($ex->getTraceAsString(), ENT_QUOTES, "UTF-8") . "</code></pre>";
    
    $res->status(500)->send($html);
});

$router->run();

$elapsed = round(microtime(true) - $starttime, 4);

echo "<!-- Page generated in {$elapsed} seconds -->";