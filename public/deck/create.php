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

    $response = [];

    if (!empty($_FILES['deckFile'])) {
        // Move the uploaded file to a temporary location
        $tmpFilePath = '/tmp/' . $_FILES['deckFile']['name'];
        move_uploaded_file($_FILES['deckFile']['tmp_name'], $tmpFilePath);

        // Upload the cards using the DeckManager
        $deckManager = new DeckManager($pdo);
        $cards = $deckManager->uploadCards($tmpFilePath);

        $deckNote = trim($_POST['deckNote']);
        if (!$deckNote) {
            $deckNote = null;
        }

        $cardQuestionLangId = $_POST['cardQuestionLangId'] ? (int) $_POST['cardQuestionLangId'] : null;
        $cardAnswerLangId = $_POST['cardAnswerLangId'] ? (int) $_POST['cardAnswerLangId'] : null;
        $deckId = $deckManager->create($userId, $_POST['deckName'], $deckNote, $cardQuestionLangId, $cardAnswerLangId);

        $cardManager = new CardManager($pdo);

        foreach ($cards as $cardQuestion => $cardAnswers) {
            foreach ($cardAnswers as $cardAnswer => $cardNote) {
                $cardManager->create($userId, $deckId, $cardAnswer, $cardQuestion, $cardNote);
            }
        }

        $response['deckId'] = $deckId;

        // Delete the temporary file
        unlink($tmpFilePath);

        echo json_encode($response);
    } else {
        throw new Exception('Deck file not found');
    }
} catch (Exception $e) {
    http_response_code(400); // return a custom status code
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
