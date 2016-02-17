<?php
require("src/application.php");

function loggingMiddleware($req) {
    echo "<!--\n";
    var_dump($req);
    echo "\n-->\n";
    
    return false;
}

$baseUrl = substr(__DIR__, strlen($_SERVER["DOCUMENT_ROOT"]));

$starttime = microtime(true);

$app = new Application($baseUrl);

$app->get("/:param", 'loggingMiddleware', function($req) {
    echo "<h1>Not Index</h1>"; 
});

$app->run();
$elapsed = round(microtime(true) - $starttime, 4);

echo "<!-- Page generated in {$elapsed} seconds -->";