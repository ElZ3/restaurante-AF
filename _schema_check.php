<?php
$config = require __DIR__ . '/config/database.php';
try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $sql = "SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME IN ('reservas','mesas','productos','pedidos','detalle_pedido','pagos','usuarios','areas','configuracion','categorias')
            ORDER BY TABLE_NAME, ORDINAL_POSITION";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$config['dbname']]);
    foreach ($stmt as $r) {
        echo $r['TABLE_NAME'] . '.' . $r['COLUMN_NAME'] . ' ' . $r['COLUMN_TYPE'] . ' null=' . $r['IS_NULLABLE'] . PHP_EOL;
    }
} catch (Throwable $e) {
    echo 'ERR ' . $e->getMessage() . PHP_EOL;
}
