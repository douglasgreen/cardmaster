<?php

require_once __DIR__ . '/../vendor/autoload.php';

use CardMaster\Application;
use CardMaster\LanguageManager;

header('Content-Type: application/json');

try {
    $app = new Application();
    $pdo = $app->getPdo();

    $languageManager = new LanguageManager($pdo);
    $allLanguages = $languageManager->readAll();

    echo json_encode($allLanguages);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(
        [
            'status' => 'error',
            'message' => $e->getMessage(),
        ]
    );
}
