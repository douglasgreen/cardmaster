<?php

require_once __DIR__ . '/../vendor/autoload.php';

use CardMaster\Application;
use CardMaster\CardManager;

header('Content-Type: application/json');

try {
    $app = new Application();
    $pdo = $app->getPdo();

    $cardManager = new CardManager($pdo);
    $cardManager->delete($_POST['cardId']);
    echo json_encode(['status' => 'ok']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(
        [
            'status' => 'error',
            'message' => $e->getMessage(),
        ]
    );
}
