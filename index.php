<?php
require __DIR__ . '/app/core/bootstrap.php';
$controller = new \App\Controllers\AuthController();
$controller->login();