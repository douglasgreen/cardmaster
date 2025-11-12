#!/usr/bin/php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use CardMaster\DatabaseManager;

// Define the database configuration file path and CSV file path
$configFilePath = __DIR__ . '/../config.ini';

// Create a new database connection using the DatabaseManager
$dbManager = new DatabaseManager($configFilePath);
$pdo = $dbManager->getConnection()

// Get all cards
$sql = 'SELECT * FROM `Cards` ORDER BY `deck_id`, `card_question`';
$result = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$cards = [];
foreach ($result as $row) {
    $key = $row['deck_id'] . '-' . $row['card_question'];
    if (isset($cards[$key])) {
        // Concatenate card_answer with existing one, separated by a semicolon
        $cards[$key]['card_answer'] .= '; ' . $row['card_answer'];
        // Store card_id to be deleted later
        $cards[$key]['delete_ids'][] = $row['card_id'];
    } else {
        $cards[$key] = [
            'card_answer' => $row['card_answer'],
            'update_id' => $row['card_id'],
            'delete_ids' => [],
        ];
    }
}

// Update the rows with combined card_answers and delete duplicates
$pdo->beginTransaction();
try {
    foreach ($cards as $key => $card) {
        if (!empty($card['delete_ids'])) {
            // Update the row with combined card_answer
            $sql = 'UPDATE `Cards` SET `card_answer` = ? WHERE `card_id` = ?';
            $pdo->prepare($sql)->execute([$card['card_answer'], $card['update_id']]);
            echo $card['card_answer'] . "\n";
            // Delete duplicate rows
            $sql = 'DELETE FROM `Cards` WHERE `card_id` IN (' . implode(',', $card['delete_ids']) . ')';
            $pdo->exec($sql);
        }
    }
    // All changes are correct, commit them
    $pdo->commit();
} catch (Exception $e) {
    // An error occurred, rollback changes
    $pdo->rollBack();
    throw $e;
}

echo "Data fixed and unique index added successfully.\n";
