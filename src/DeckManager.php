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
        int $userId,
        string $deckName,
        string $deckNote = null,
        int $cardQuestionLangId = null,
        int $cardAnswerLangId = null
    ): int {
        $query = <<<SQL
            INSERT INTO
                Decks (
                    userId,
                    deckName,
                    deckNote,
                    cardQuestionLangId,
                    cardAnswerLangId,
                    deckActive
                )
            VALUES
                (:userId, :deckName, :deckNote, :question, :answer, 1)
            SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':userId' => $userId,
            ':deckName' => $deckName,
            ':deckNote' => $deckNote ?: null,
            ':question' => $cardQuestionLangId ?: null,
            ':answer' => $cardAnswerLangId ?: null
        ]);

        $deckId = $this->pdo->lastInsertId();
        return $deckId;
    }

    public function delete(int $userId, int $deckId): void
    {
        $this->mustBeOwnedByUser($userId, $deckId);
        $stmt = $this->pdo->prepare("DELETE FROM Decks WHERE userId = ? AND deckId = ?");
        $stmt->execute([$userId, $deckId]);
    }

    public function mustBeOwnedByUser(int $userId, int $deckId): void
    {
        $query = <<<SQL
            SELECT
                COUNT(*) as count
            FROM
                Decks
            WHERE
                userId = :userId
                AND deckId = :deckId
            SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':userId' => $userId,
            ':deckId' => $deckId
        ]);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] == 0) {
            throw new Exception("Deck is not owned by user");
        }
    }

    public function readAll(int $userId): array
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
            WHERE
                Decks.userId = :userId
            GROUP BY
                deckId
            ORDER BY
                deckName
            SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':userId' => $userId]);
        $rows = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['percentCorrect'] = $row['sumAllAttempts'] ? round($row['sumCorrectAttempts'] / $row['sumAllAttempts'] * 100) : 0;
            $row['percentMastered'] = $row['cardCount'] ? round($row['masteredQuestions'] / $row['cardCount'] * 100) : 0;
            $rows[] = $row;
        }
        return $rows;
    }

    public function read(int $userId, int $deckId): array
    {
        $this->mustBeOwnedByUser($userId, $deckId);
        $stmt = $this->pdo->prepare("SELECT * FROM Decks WHERE userId = ? AND deckId = ?");
        $stmt->execute([$userId, $deckId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function readByName(int $userId, string $deckName): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM Decks WHERE userId = ? AND deckName = ?");
        $stmt->execute([$userId, $deckName]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $deckId = $row['deckId'];
        $this->mustBeOwnedByUser($userId, $deckId);
        return $row;
    }

    public function readNames(int $userId): array
    {
        $query = <<<SQL
            SELECT
                deckId,
                deckName
            FROM
                Decks
            WHERE
                userId = :userId
            ORDER BY
                deckName
            SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':userId' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function rename(int $userId, int $deckId, string $newName): array
    {
        $this->mustBeOwnedByUser($userId, $deckId);
        $query = <<<SQL
            UPDATE
                Decks
            SET
                deckName = :deckName
            WHERE
                userId = :userId
                AND deckId = :deckId
            SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':userId' => $userId, ':deckName' => $newName, ':deckId' => $deckId]);

        return ['deckId' => $deckId, 'deckName' => $newName];
    }

    public function resetCards(int $userId, int $deckId): void
    {
        $this->mustBeOwnedByUser($userId, $deckId);
        $query = <<<SQL
            UPDATE
                Cards
                JOIN Decks USING (deckId)
            SET
                lastAttempt = NULL,
                correctAttempts = 0,
                allAttempts = 0
            WHERE
                userId = :userId
                AND deckId = :deckId
            SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':userId' => $userId,
            ':deckId' => $deckId
        ]);
    }

    public function toggleActive(int $userId, int $deckId): array
    {
        $this->mustBeOwnedByUser($userId, $deckId);
        $stmt = $this->pdo->prepare("SELECT deckActive FROM Decks WHERE userId = :userId AND deckId = :deckId");
        $stmt->execute([':userId' => $userId, ':deckId' => $deckId]);
        $deck = $stmt->fetch(PDO::FETCH_ASSOC);

        $newActiveState = $deck['deckActive'] ? "0" : "1";

        $query = <<<SQL
            UPDATE
                Decks
            SET
                deckActive = :deckActive
            WHERE
                userId = :userId
                AND deckId = :deckId
            SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':userId' => $userId, ':deckActive' => $newActiveState, ':deckId' => $deckId]);

        $deck['deckActive'] = $newActiveState;
        return $deck;
    }

    public function setAllActive(int $userId, bool $active): void
    {
        $stmt = $this->pdo->prepare("UPDATE Decks SET deckActive = :deckActive WHERE userId = :userId");
        $stmt->execute([
            ':deckActive' => $active,
            ':userId' => $userId
        ]);
    }

    public function updateNote(int $userId, int $deckId, string $deckNote): void
    {
        $this->mustBeOwnedByUser($userId, $deckId);
        $stmt = $this->pdo->prepare("UPDATE Decks SET deckNote = ? WHERE userId = ? AND deckId = ?");
        $stmt->execute([$deckNote, $userId, $deckId]);
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
