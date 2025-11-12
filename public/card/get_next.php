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
    $deckManager = new DeckManager($pdo);
    $cardManager = new CardManager($pdo);
    $languageManager = new LanguageManager($pdo);

    $nextCard = $cardManager->getNext($userId);
    if ($nextCard === null) {
        echo json_encode(['card' => null]);
    } else {
        $deck = $deckManager->read($userId, $nextCard['deckId']);
        $questionLanguage = $deck['cardQuestionLangId'] ? $languageManager->read($deck['cardQuestionLangId']) : null;
        $answerLanguage = $deck['cardAnswerLangId'] ? $languageManager->read($deck['cardAnswerLangId']) : null;
        $data = ['card' => $nextCard, 'deck' => $deck, 'questionLanguage' => $questionLanguage, 'answerLanguage' => $answerLanguage];
        if (isset($nextCard['reviewCount'])) {
            $data['reviewCount'] = $nextCard['reviewCount'];
        } elseif (isset($nextCard['newCount'])) {
            $data['newCount'] = $nextCard['newCount'];
        } elseif (isset($nextCard['oldCount'])) {
            $data['oldCount'] = $nextCard['oldCount'];
        }
        echo json_encode($data);
    }
} catch (Exception $e) {
    http_response_code(400); // return a custom status code
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
