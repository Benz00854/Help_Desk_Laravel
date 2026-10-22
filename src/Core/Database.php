<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        // โหลดค่าจากไฟล์ .env (ถ้ามี)
        $envFile = __DIR__ . '/../../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || strpos($line, '#') === 0) continue;
                if (strpos($line, '=') !== false) {
                    [$k, $v] = explode('=', $line, 2);
                    $k = trim($k);
                    $v = trim($v, " \t\n\r\0\x0B\"'");
                    putenv("$k=$v");
                    $_ENV[$k] = $v;
                }
            }
        }

        $host = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? 'sql102.infinityfree.com');
        $port = getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? '3306');
        $db = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'if0_43243909_epiz_helpdesk');
        $user = getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? 'if0_43243909');
        $pass = getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? 'sYFewTEg70');
        $charset = 'utf8mb4';

        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int) $e->getCode());
        }
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }
    public function commit(): void
    {
        $this->pdo->commit();
    }
    public function rollBack(): void
    {
        $this->pdo->rollBack();
    }
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }
}