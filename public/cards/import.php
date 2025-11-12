<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use CardMaster\Application;
use CardMaster\CardManager;
use CardMaster\DeckManager;

header('Content-Type: application/json');

try {
    $app = new Application();
    $app->checkAuthentication();
    $pdo = $app->getPdo();
    $userId = $app->getUserId();

    $response = ['post' => $_POST];

    if (!empty($_FILES['deckFile']) && !empty($_POST['deckId'])) {
        // Move the uploaded file to a temporary location
        $tmpFilePath = '/tmp/' . $_FILES['deckFile']['name'];
        move_uploaded_file($_FILES['deckFile']['tmp_name'], $tmpFilePath);

        // Upload the cards using the CardManager
        $cardManager = new CardManager($pdo);
        $deckManager = new DeckManager($pdo);
        $cards = $deckManager->uploadCards($tmpFilePath);

        $response['cards'] = $cards;

        $deckId = $_POST['deckId'];
        $answerOverride = $_POST['answerOverride'];

        // @todo Add a sync feature that deletes non-identical old cards before importing non-identical new cards.
        foreach ($cards as $cardQuestion => $cardAnswers) {
            foreach ($cardAnswers as $cardAnswer => $cardNote) {
                // Check for duplicates and apply overrides
                if ($cardManager->checkForDuplicateAnswer($userId, $deckId, $cardAnswer, $cardQuestion)) {
                    if ($answerOverride === "overwrite") {
                        $cardManager->overwriteCardAnswer($userId, $deckId, $cardAnswer, $cardQuestion, $cardNote);
                    } else if ($answerOverride === "append") {
                        $cardManager->create($userId, $deckId, $cardAnswer, $cardQuestion, $cardNote);
                    }
                } else {
                    $cardManager->create($userId, $deckId, $cardAnswer, $cardQuestion, $cardNote);
                }
            }
        }

        // Delete the temporary file
        unlink($tmpFilePath);

        echo json_encode($response);
    } else {
        throw new Exception('Deck file or ID not found');
    }
} catch (Exception $e) {
    http_response_code(400); // return a custom status code
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
