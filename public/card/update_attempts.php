<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use CardMaster\Application;
use CardMaster\CardManager;
use CardMaster\DeckManager;
use CardMaster\LanguageManager;

header('Content-Type: application/json');

try {
    $app = new Application();
    $app->checkAuthentication();
    $pdo = $app->getPdo();
    $userId = $app->getUserId();

    // Create new instances of flashcard classes.
    $cardManager = new CardManager($pdo);

    $cardId = $_POST['cardId'] ?? null;
    $lastAttempt = date('Y-m-d H:i:s');
    $correctAttempts = $_POST['correctAttempts'] ?? 0;
    $score = $_POST['score'] ?? 0;
    $timeTaken = $_POST['timeTaken'] ?? 10;
    if ($cardId !== null) {
        $cardManager->updateAttempts($userId, $cardId, $lastAttempt, $correctAttempts, $score, $timeTaken);
        echo json_encode(['status' => 'success']);
    } else {
        throw new Exception('Missing parameters');
    }
} catch (Exception $e) {
    http_response_code(400); // return a custom status code
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
