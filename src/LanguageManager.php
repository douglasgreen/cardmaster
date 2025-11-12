<?php

namespace CardMaster;

use PDO;

class LanguageManager
{
    /**
     * @var PDO
     */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(string $langName, string $ietfTag): string
    {
        $query = <<<SQL
            INSERT INTO
                Languages
            SET
                langName = :langName,
                ietfTag = :ietfTag
            SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(
            [
                ':langName' => $langName,
                ':ietfTag' => $ietfTag,
            ]
        );
        return $this->pdo->lastInsertId();
    }

    public function delete(int $langId): void
    {
        $sql = 'DELETE FROM Languages WHERE langId = :langId';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':langId' => $langId]);
    }

    public function read(int $langId): array
    {
        $sql = 'SELECT * FROM Languages WHERE langId = :langId';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':langId' => $langId]);
        return $stmt->fetch();
    }

    public function readAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM Languages ORDER BY langName');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update(int $langId, string $langName, string $ietfTag): void
    {
        $query = <<<SQL
            UPDATE
                Languages
            SET
                langName = :langName,
                ietfTag = :ietfTag
            WHERE
                langId = :langId
            SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(
            [
                ':langName' => $langName,
                ':ietfTag' => $ietfTag,
                ':langId' => $langId
            ]
        );
    }
}
