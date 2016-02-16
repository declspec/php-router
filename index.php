<?php
require("application.php");

$starttime = microtime(true);

$app = new Application();

$app->all("/", function($req) {
    echo "<h1>Index Page</h1>"; 
    echo "<!--\n";
    var_dump($req);
    echo "\n-->\n";
});

$app->run();

$elapsed = round(microtime(true) - $starttime, 4);

echo "<!-- Page generated in {$elapsed} seconds -->";