#!/usr/bin/php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use CardMaster\CardManager;
use CardMaster\DatabaseManager;
use CardMaster\DeckManager;

if ($argc !== 2 || !is_dir($argv[1])) {
    die('Usage: ' . basename($argv[0]) . " DIRNAME\n");
}
$dirname = $argv[1];

// Define the database configuration file path and CSV file path
$configFilePath = __DIR__ . '/../config.ini';

// Create a new database connection using the DatabaseManager
$dbManager = new DatabaseManager($configFilePath);
$pdo = $dbManager->getConnection();

// Initialize the flashcard classes
$cardManager = new CardManager($pdo);
$deckManager = new DeckManager($pdo);

// Get all flashcards from the database
$allFlashcards = $cardManager->readAll();

// Create a file to export flashcards to
$fp = fopen('flashcards.csv', 'w');

// Write the header row
fputcsv($fp, ['deck_name', 'card_answer', 'card_question']);

// Write the data rows
foreach ($allFlashcards as $flashcard) {
    $deck = $deckManager->read($flashcard['deck_id']);
    fputcsv($fp, [$deck['deck_name'], $flashcard['card_answer'], $flashcard['card_question']]);
}

// Close the file
fclose($fp);
