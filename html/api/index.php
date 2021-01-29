<?php
header("Cache-Control: no-cache");
header("Expires: 0");
header("Pragma: no-cache");
header("X-UA-Compatible: ie=edge");
require __DIR__ . '/../../lib/bootstrap.php';
// Instantiate the app
$app = new \Slim\App();
// Set up dependencies
require __DIR__ . '/../../lib/dependencies.php';
// Register routes
require __DIR__ . '/../../lib/routes.php';
// Run app
$app->run();
?>
