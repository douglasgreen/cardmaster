<?php

require_once __DIR__ . '/../vendor/autoload.php';

use CardMaster\Application;
use CardMaster\DeckManager;

header('Content-Type: application/json');

try {
    $app = new Application();
    $pdo = $app->getPdo();

    $deckManager = new DeckManager($pdo);
    $allDecks = $deckManager->readAll();

    echo json_encode($allDecks);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(
        [
            'status' => 'error',
            'message' => $e->getMessage(),
        ]
    );
}
