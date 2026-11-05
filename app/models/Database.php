<?php
// Clase de conexión a la base de datos MariaDB usando PDO.
// Patrón Singleton con soporte extendido para interceptar PDO::query en PHP 8.

class ExtendedPDO extends PDO
{
    // Se remueve la declaración ?int para que PHP acepte arreglos de parámetros en $mode
    public function query(string $statement, $mode = null, ...$fetch_mode_args): PDOStatement|false
    {
        if (is_array($mode)) {
            $stmt = $this->prepare($statement);
            $stmt->execute($mode);
            return $stmt;
        }

        if ($mode !== null) {
            return parent::query($statement, $mode, ...$fetch_mode_args);
        }

        return parent::query($statement);
    }
}

class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        $config = require __DIR__ . '/../../config/database.php';

        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";

        try {
            $this->pdo = new ExtendedPDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options']
            );
        } catch (PDOException $e) {
            throw new PDOException("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }

    private function __clone() {}
    public function __wakeup() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): ExtendedPDO
    {
        return $this->pdo;
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        if ($stmt === false) {
            throw new PDOException('No se pudo preparar la consulta SQL.');
        }
        $stmt->execute($params);
        return $stmt;
    }

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }
}