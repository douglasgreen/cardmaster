<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use CardMaster\Application;
use CardMaster\CardManager;
use CardMaster\DeckManager;

try {
    $app = new Application();
    $pdo = $app->getPdo();

    if (!empty($_POST['deckId'])) {
        $deckId = $_POST['deckId'];

        $cardManager = new CardManager($pdo);
        $cards = $cardManager->readDeck($deckId);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=deck.csv');

        $output = fopen('php://output', 'w');

        // insert header row
        fputcsv($output, ['Card Answer', 'Card Question', 'Card Note']);

        // insert data rows
        foreach ($cards as $card) {
            fputcsv($output, [$card['cardAnswer'], $card['cardQuestion'], $card['cardNote']]);
        }

        fclose($output);
    } else {
        throw new Exception('Deck ID not found');
    }
} catch (Exception $e) {
    header('Content-Type: application/json');

    http_response_code(400); // return a custom status code
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
