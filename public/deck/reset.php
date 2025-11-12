<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use CardMaster\Application;
use CardMaster\DeckManager;

header('Content-Type: application/json');

try {
    $app = new Application();
    $pdo = $app->getPdo();

    $deckManager = new DeckManager($pdo);
    $deckManager->resetCards($_POST['deckId']);
    echo json_encode(['status' => 'ok']);
} catch (Exception $e) {
    http_response_code(400); // return a custom status code
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
