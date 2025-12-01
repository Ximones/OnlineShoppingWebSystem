<?php

/**
 * Centralised PDO connection helper.
 * Keep the credentials here while the schema and seed data live inside /database.
 */
$_db_config = [
    'host' => '127.0.0.1',
    'port' => 3306,
    'name' => 'test',
    'user' => 'root',
    'pass' => '',
    'charset' => 'utf8mb4',
];

/**
 * Returns a shared PDO instance. All models call this helper instead of creating
 * their own connections so that transactions and error handling are consistent.
 */
function db(): PDO {
    static $pdo = null;
    global $_db_config;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $_db_config['host'],
            $_db_config['port'],
            $_db_config['name'],
            $_db_config['charset']
        );

        $pdo = new PDO($dsn, $_db_config['user'], $_db_config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    return $pdo;
}

/**
 * Convenience wrapper to run a callback inside a DB transaction.
 */
function db_transaction(callable $callback) {
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $result = $callback($pdo);
        $pdo->commit();
        return $result;
    } catch (Throwable $ex) {
        $pdo->rollBack();
        throw $ex;
    }
}
