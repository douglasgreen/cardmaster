<?php

namespace CardMaster;

use PDO;

class CardManager
{
    /**
     * @var DeckManager
     */
    private $deckManager;

    /**
     * @var PDO
     */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->deckManager = new DeckManager($pdo);
    }

    public function create(
        int $deckId,
        string $cardAnswer,
        string $cardQuestion,
        ?string $cardNote = null
    ): string {
        $this->deckManager->mustBeOwnedByUser($deckId);
        $query = <<<SQL
            INSERT INTO
                Cards
            SET
                deckId = :deckId,
                cardAnswer = :cardAnswer,
                cardQuestion = :cardQuestion,
                cardNote = :cardNote
            SQL;
        $stmt = $this->pdo->prepare($query);
        $cardNote = strlen(trim($cardNote)) > 0 ? trim($cardNote) : null;
        $stmt->execute(
            [
                'deckId' => $deckId,
                'cardAnswer' => $cardAnswer,
                'cardQuestion' => $cardQuestion,
                'cardNote' => $cardNote,
            ]
        );
        $cardId = $this->pdo->lastInsertId();
        return $cardId;
    }

    public function delete(int $cardId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM Cards WHERE cardId = ?');
        $stmt->execute([$cardId]);
    }

    public function getNext(): ?array
    {
        // Select the next card in need of review at random.
        // Limit correct attempts between 3^0*5=5 minutes and 3^10*5 minutes = 205 days between repetitions.
        $query = <<<SQL
            SELECT
                Cards.*,
                DATE_ADD(
                    lastAttempt,
                    INTERVAL POW(3, LEAST(totalScore, 10)) * 5 MINUTE
                ) AS review_date
            FROM
                Cards
                JOIN Decks USING (deckId)
            WHERE
                deckActive = TRUE
                AND lastAttempt IS NOT NULL
            HAVING
                review_date < NOW()
            ORDER BY
                RAND()
            SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $reviewCount = $stmt->rowCount();
        if ($reviewCount) {
            $firstCard = $stmt->fetch(PDO::FETCH_ASSOC);
            $firstCard['reviewCount'] = $reviewCount;
            return $firstCard;
        }

        // Select the next card that hasn't been studied at random.
        $query = <<<SQL
            SELECT
                Cards.*
            FROM
                Cards
                JOIN Decks USING (deckId)
            WHERE
                deckActive = TRUE
                AND lastAttempt IS NULL
            ORDER BY
                RAND()
            SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $newCount = $stmt->rowCount();
        if ($newCount) {
            $firstCard = $stmt->fetch(PDO::FETCH_ASSOC);
            $firstCard['newCount'] = $newCount;
            return $firstCard;
        }

        // Select the next card that has been studied and isn't in need of review.
        // Limit correct attempts between 3^0*5=5 minutes and 3^10*5 minutes = 205 days between repetitions.
        $query = <<<SQL
            SELECT
                Cards.*,
                DATE_ADD(
                    lastAttempt,
                    INTERVAL POW(3, LEAST(totalScore, 10)) * 5 MINUTE
                ) AS review_date
            FROM
                Cards
                JOIN Decks USING (deckId)
            WHERE
                deckActive = TRUE
                AND lastAttempt IS NOT NULL
            HAVING
                review_date >= NOW()
            ORDER BY
                RAND()
            SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $oldCount = $stmt->rowCount();
        if ($oldCount) {
            $firstCard = $stmt->fetch(PDO::FETCH_ASSOC);
            $firstCard['oldCount'] = $oldCount;
            return $firstCard;
        }

        return null;
    }

    public function read(int $cardId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM Cards WHERE cardId = ?');
        $stmt->execute([$cardId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function readAll(): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM Cards');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readDeck(int $deckId): array
    {
        $query = <<<SQL
            SELECT
                Cards.*
            FROM
                Cards
                JOIN Decks USING (deckId)
            WHERE
                deckId = :deckId
            SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(
            [':deckId' => $deckId]
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateAttempts(
        int $cardId,
        string $lastAttempt,
        int $correctAttempts,
        float $score,
        float $timeTaken
    ): void {
        $query = <<<SQL
            UPDATE
                Cards
            SET
                lastAttempt = :lastAttempt,
                correctAttempts = :correctAttempts,
                allAttempts = allAttempts + 1,
                totalScore = LEAST(GREATEST(totalScore + :totalScore, 0), 10),
                totalTime = totalTime + :totalTime
            WHERE
                cardId = :cardId
            SQL;

        $stmt = $this->pdo->prepare($query);
        $stmt->execute(
            [
                'lastAttempt' => $lastAttempt,
                'correctAttempts' => $correctAttempts,
                'totalScore' => $score,
                'totalTime' => $timeTaken,
                'cardId' => $cardId,
            ]
        );
    }

    public function updateText(
        int $cardId,
        string $cardAnswer,
        string $cardQuestion,
        ?string $cardNote
    ): void {
        $query = <<<SQL
            UPDATE
                Cards
            SET
                cardAnswer = ?,
                cardQuestion = ?,
                cardNote = ?,
                lastAttempt = NULL,
                correctAttempts = 0,
                allAttempts = 0,
                totalScore = 0.0,
                totalTime = 0.0
            WHERE
                cardId = ?
            SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$cardAnswer, $cardQuestion, $cardNote, $cardId]);
    }

    public function checkForDuplicateAnswer(
        int $deckId,
        string $cardAnswer,
        string $cardQuestion
    ): bool {
        $dupAnswerQuery = 'SELECT COUNT(*) AS count FROM Cards WHERE deckId = :deckId AND cardAnswer = :cardAnswer';
        $stmt = $this->pdo->prepare($dupAnswerQuery);
        $stmt->execute(
            [
                ':deckId' => $deckId,
                ':cardAnswer' => $cardAnswer,
            ]
        );
        $dupAnswer = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;

        return $dupAnswer;
    }

    public function overwriteCardAnswer(
        int $deckId,
        string $cardAnswer,
        string $cardQuestion,
        ?string $cardNote
    ): void {
        $overwriteQuery = 'UPDATE Cards SET cardAnswer = :cardAnswer, cardQuestion = :cardQuestion, cardNote = :cardNote WHERE deckId = :deckId AND cardAnswer = :cardAnswer';
        $stmt = $this->pdo->prepare($overwriteQuery);
        $stmt->execute(
            [
                ':cardAnswer' => $cardAnswer,
                ':cardQuestion' => $cardQuestion,
                ':cardNote' => $cardNote,
                ':deckId' => $deckId,
            ]
        );
    }

    public function overwriteCardQuestion(
        int $deckId,
        string $cardAnswer,
        string $cardQuestion,
        ?string $cardNote
    ): void {
        $overwriteQuery = 'UPDATE Cards SET cardAnswer = :cardAnswer, cardQuestion = :cardQuestion, cardNote = :cardNote WHERE deckId = :deckId AND cardQuestion = :cardQuestion';
        $stmt = $this->pdo->prepare($overwriteQuery);
        $stmt->execute(
            [
                ':cardAnswer' => $cardAnswer,
                ':cardQuestion' => $cardQuestion,
                ':cardNote' => $cardNote,
                ':deckId' => $deckId,
            ]
        );
    }
}
