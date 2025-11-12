#!/usr/bin/php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use CardMaster\CardManager;
use CardMaster\DatabaseManager;
use CardMaster\DeckManager;
use CardMaster\Input;

readline_callback_handler_install('', function () {
});

// Define the database configuration file path and CSV file path
$configFilePath = __DIR__ . '/../config.ini';

// Create a new database connection using the DatabaseManager
$dbManager = new DatabaseManager($configFilePath);
$pdo = $dbManager->getConnection();

// Create new instances of flashcard classes
$deckManager = new DeckManager($pdo);
$cardManager = new CardManager($pdo);

do {
    $nextCard = $cardManager->getNext();

    if ($nextCard === null) {
        echo "No more flashcards available.\n";
    } else {
        // Print the card question
        $deck = $deckManager->readDeck($nextCard['deck_id']);
        echo "\nDeck: " . $deck['deck_name'] . "\n";
        echo "Question: " . $nextCard['card_question'] . "\n";
        echo "Press any key to see the answer...\n";

        // Wait for any key to be pressed
        $ignore = Input::getChar();

        // Print the card answer
        echo "Answer: " . $nextCard['card_answer'] . "\n";

        // Wait for user to input Y or N
        do {
            echo "\nWas your answer correct? (Y/N/X=exit): ";
            $response = strtoupper(Input::getChar());
        } while ($response !== 'Y' && $response !== 'N' && $response !== 'X');

        // Update card attempt status
        if ($response !== 'X') {
            echo $response . "\n";
            $last_attempt = new DateTime();
            $correct_attempts = $nextCard['correct_attempts'] ?? 0;
            $correct_attempts += $response === 'Y' ? 1 : -1;
            $cardManager->updateAttempts($nextCard['card_id'], $last_attempt->format('Y-m-d H:i:s'), $correct_attempts);
        }
    }
} while ($nextCard !== null && $response !== 'X');

echo "Study session has ended.\n";
