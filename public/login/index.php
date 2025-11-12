<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use CardMaster\Application;
use CardMaster\UserManager;

$app = new Application();
$pdo = $app->getPdo();
$userManager = $app->getUserManager();

$username = $_POST['username'];
$password = $_POST['password'];

$userId = $userManager->login($username, $password);
if ($userId) {
    $_SESSION['userId'] = $userId;
    header('Location: ../help/');
    exit();
} else {
    echo "Login failed. Please <a href='../index.php'>try again</a>.";
}
