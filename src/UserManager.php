<?php

namespace CardMaster;

use PDO;

class UserManager
{
    /**
     * @var PDO
     */
    private $pdo;
    private $pepper;

    public function __construct(PDO $pdo, array $config)
    {
        $this->pdo = $pdo;
        $this->pepper = $config["pepper"];
    }

    public function login(string $username, string $password): ?int
    {
        $pwdPeppered = hash_hmac("sha256", $password, $this->pepper);
        $query = <<<SQL
            SELECT
                userId,
                userPass
            FROM
                Users
            WHERE
                username = ?
            SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$username]);

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $pwdHashed = $user['userPass'];
            $result = password_verify($pwdPeppered, $pwdHashed);
            if ($result) {
                return $user['userId'];
            }
        }

        return null;
    }

    public function register(string $username, string $password): void
    {
        $pwdPeppered = hash_hmac("sha256", $password, $this->pepper);
        $pwdHashed = password_hash($pwdPeppered, PASSWORD_DEFAULT);

        $query = <<<SQL
            INSERT INTO
                Users
            SET
                username = :username,
                userPass = :userPass
            SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            'username' => $username,
            'userPass' => $pwdHashed
        ]);
    }

    public function update(string $username, string $password): void
    {
        $pwdPeppered = hash_hmac("sha256", $password, $this->pepper);
        $pwdHashed = password_hash($pwdPeppered, PASSWORD_DEFAULT);

        $query = <<<SQL
            UPDATE
                Users
            SET
                userPass = ?
            WHERE
                username = ?
            SQL;
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$pwdHashed, $username]);
    }
}
