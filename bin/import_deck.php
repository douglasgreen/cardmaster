#!/usr/bin/php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use CardMaster\CardManager;
use CardMaster\DatabaseManager;
use CardMaster\DeckManager;

die("Doesn't work without user_id\n");

if ($argc != 2) {
    die("Usage: " . basename($argv[0]) . " FILENAME\n");
}
$csvFileName = $argv[1];

// Define the database configuration file path and CSV file path
$configFilePath = __DIR__ . '/../config.ini';

// Create a new database connection using the DatabaseManager
$dbManager = new DatabaseManager($configFilePath);
$pdo = $dbManager->getConnection();

// Create new instances of flashcard classes
$deckManager = new DeckManager($pdo);
$cardManager = new CardManager($pdo);

// Open the CSV file
if (($handle = fopen($csvFileName, 'r')) === false) {
    die("Could not open file '$csvFileName'.\n");
}

// Assume that a flashcard deck has been created for this CSV file
$deck_name = str_replace('_', ' ', basename($csvFileName, '.csv'));
$deckManager->create($deck_name);
$deck_id = $pdo->lastInsertId();


// Read each line from the CSV file
while (($data = fgetcsv($handle)) !== false) {
    // Skip if the line does not have two fields
    if (count($data) < 2) {
        echo "Skipping line in $deck_name: " . var_export($data, true);
        continue;
    }

    // Get the back (answer) and front (question) fields from the line
    $card_answer = $data[0];
    $card_question = $data[1];

    // Create a new flashcard using the CardManager class
    $cardManager->create($deck_id, $card_answer, $card_question);
}

// Close the CSV file
fclose($handle);

echo "Successfully imported flashcards from '$csvFileName' into deck with ID $deck_id.\n";
