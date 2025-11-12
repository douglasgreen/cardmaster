<?php

// Your database credentials
$dbHost = 'eos';
$dbName = 'CardMaster';
$dbUser = 'username';
$dbPass = 'password';

// Connect to the database
$db = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8;port=23979", $dbUser, $dbPass);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Get all cards
$sql = "SELECT * FROM `Cards` ORDER BY `deck_id`, `card_question`";
$result = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

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
$db->beginTransaction();
try {
    foreach ($cards as $key => $card) {
        if (!empty($card['delete_ids'])) {
            // Update the row with combined card_answer
            $sql = "UPDATE `Cards` SET `card_answer` = ? WHERE `card_id` = ?";
            $db->prepare($sql)->execute([$card['card_answer'], $card['update_id']]);
            echo $card['card_answer'] . "\n";
            // Delete duplicate rows
            $sql = "DELETE FROM `Cards` WHERE `card_id` IN (" . implode(',', $card['delete_ids']) . ")";
            $db->exec($sql);
        }
    }
    // All changes are correct, commit them
    $db->commit();
} catch (Exception $e) {
    // An error occurred, rollback changes
    $db->rollBack();
    throw $e;
}

echo "Data fixed and unique index added successfully.\n";
