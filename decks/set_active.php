<?php

require_once __DIR__ . '/../vendor/autoload.php';

use CardMaster\Application;
use CardMaster\DeckManager;

header('Content-Type: application/json');

try {
    $app = new Application();
    $pdo = $app->getPdo();

    $deckManager = new DeckManager($pdo);
    $active = isset($_POST['active']) ? filter_var(
        $_POST['active'],
        FILTER_VALIDATE_BOOLEAN
    ) : false;
    $updatedDeck = $deckManager->setAllActive($active);

    echo json_encode($updatedDeck);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(
        [
            'status' => 'error',
            'message' => $e->getMessage(),
        ]
    );
}
