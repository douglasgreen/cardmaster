<?php

namespace CardMaster;

use Exception;
use PDO;

class DeckManager
{
    /**
     * @var PDO
     */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(
        string $deckName,
        string $deckNote = null,
        int $cardQuestionLangId = null,
        int $cardAnswerLangId = null
    ): int {
        $query = <<<SQL
            INSERT INTO
                Decks (
                    deckName,
                    deckNote,
                    cardQuestionLangId,
                    cardAnswerLangId,
                    deckActive
                )
            VALUES
                (:deckName, :deckNote, :question, :answer, 1)
            SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':deckName' => $deckName,
            ':deckNote' => $deckNote ?: null,
            ':question' => $cardQuestionLangId ?: null,
            ':answer' => $cardAnswerLangId ?: null
        ]);

        $deckId = $this->pdo->lastInsertId();
        return $deckId;
    }

    public function delete(int $deckId): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM Decks WHERE deckId = ?");
        $stmt->execute([$deckId]);
    }

    public function readAll(): array
    {
        // Mastery assumes that cards has score 10 or more
        $query = <<<SQL
            SELECT
                Decks.*,
                SUM(Cards.correctAttempts) AS sumCorrectAttempts,
                SUM(Cards.allAttempts) AS sumAllAttempts,
                COUNT(CASE WHEN Cards.totalScore >= 9.99 THEN 1 END) AS masteredQuestions,
                COUNT(*) AS cardCount
            FROM
                Decks
                LEFT JOIN Cards USING (deckId)
            GROUP BY
                deckId
            ORDER BY
                deckName
            SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $rows = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['percentCorrect'] = $row['sumAllAttempts'] ? round($row['sumCorrectAttempts'] / $row['sumAllAttempts'] * 100) : 0;
            $row['percentMastered'] = $row['cardCount'] ? round($row['masteredQuestions'] / $row['cardCount'] * 100) : 0;
            $rows[] = $row;
        }
        return $rows;
    }

    public function read(int $deckId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM Decks WHERE deckId = ?");
        $stmt->execute([$deckId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function readByName(string $deckName): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM Decks WHERE deckName = ?");
        $stmt->execute([$deckName]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $deckId = $row['deckId'];
        return $row;
    }

    public function readNames(): array
    {
        $query = <<<SQL
            SELECT
                deckId,
                deckName
            FROM
                Decks
            ORDER BY
                deckName
            SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function rename(int $deckId, string $newName): array
    {
        $query = <<<SQL
            UPDATE
                Decks
            SET
                deckName = :deckName
            WHERE
                deckId = :deckId
            SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':deckName' => $newName, ':deckId' => $deckId]);

        return ['deckId' => $deckId, 'deckName' => $newName];
    }

    public function resetCards(int $deckId): void
    {
        $query = <<<SQL
            UPDATE
                Cards
                JOIN Decks USING (deckId)
            SET
                lastAttempt = NULL,
                correctAttempts = 0,
                allAttempts = 0
            WHERE
                deckId = :deckId
            SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':deckId' => $deckId
        ]);
    }

    public function toggleActive(int $deckId): array
    {
        $stmt = $this->pdo->prepare("SELECT deckActive FROM Decks WHERE deckId = :deckId");
        $stmt->execute([':deckId' => $deckId]);
        $deck = $stmt->fetch(PDO::FETCH_ASSOC);

        $newActiveState = $deck['deckActive'] ? "0" : "1";

        $query = <<<SQL
            UPDATE
                Decks
            SET
                deckActive = :deckActive
            WHERE
                deckId = :deckId
            SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':deckActive' => $newActiveState, ':deckId' => $deckId]);

        $deck['deckActive'] = $newActiveState;
        return $deck;
    }

    public function setAllActive(bool $active): void
    {
        $stmt = $this->pdo->prepare("UPDATE Decks SET deckActive = :deckActive");
        $stmt->execute([
            ':deckActive' => $active,
        ]);
    }

    public function updateNote(int $deckId, string $deckNote): void
    {
        $stmt = $this->pdo->prepare("UPDATE Decks SET deckNote = ? WHERE deckId = ?");
        $stmt->execute([$deckNote, $deckId]);
    }

    public function uploadCards(string $filePath): array
    {
        $file = fopen($filePath, 'r');

        $cards = [];
        $headerFields = ['Card Answer', 'Card Question'];
        $optionalHeaderFields = ['Card Note'];

        while (($data = fgetcsv($file)) !== false) {
            if (count($data) == 1 && $data == [0 => null]) {
                continue;
            }
            if ($headerFields) {
                if ($data != $headerFields && $data != array_merge($headerFields, $optionalHeaderFields)) {
                    throw new Exception('CSV file is missing header fields: ' . var_export($headerFields, true));
                }
                $headerFields = null;
                continue;
            }

            if (count($data) < 2 || count($data) > 3) {
                throw new Exception('CSV file has the wrong number of fields:' . var_export($data, true));
            }
            $cardAnswer = trim($data[0]);
            $cardQuestion = trim($data[1]);
            $cardNote = isset($data[2]) && strlen(trim($data[2])) > 0 ? trim($data[2]) : null;
            if (strlen($cardAnswer) == 0 || strlen($cardQuestion) == 0) {
                throw new Exception('CSV file has an empty field');
            }
            if (strlen($cardAnswer) > 255 || strlen($cardQuestion) > 255) {
                throw new Exception('CSV file has a field longer than 255 characters');
            }
            if (isset($cards[$cardQuestion])) {
                throw new Exception('Duplicate question detected');
            }
            $cards[$cardQuestion][$cardAnswer] = $cardNote;
        }
        fclose($file);
        return $cards;
    }
}
