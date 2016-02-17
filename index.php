<?php
require("application.php");

function loggingMiddleware($req) {
    echo "<!--\n";
    var_dump($req);
    echo "\n-->\n";
    
    return false;
}

$starttime = microtime(true);

$app = new Application();

$app->get("/:param", 'loggingMiddleware', function($req) {
    echo "<h1>Not Index</h1>"; 
});

$app->run();
$elapsed = round(microtime(true) - $starttime, 4);

echo "<!-- Page generated in {$elapsed} seconds -->";