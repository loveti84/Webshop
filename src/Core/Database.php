<?php

namespace Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function connect(): PDO
    {
        if (self::$instance === null) {
            $host = $_ENV['DB_HOST'];
            $name = $_ENV['DB_NAME'];
            $user = $_ENV['DB_USER'];
            $pass = $_ENV['DB_PASS'];
            $port = $_ENV['DB_PORT'];

            try {
                self::$instance = new PDO(
                    "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
                    $user,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,// lijst met key(kolom) value (waarde) paar
                        PDO::ATTR_EMULATE_PREPARES => false //eerst query  compileren dan waarde invoeren
                    ]
                );
            } catch (PDOException $e) {
                error_log('Database connection failed: ' . $e->getMessage());
                ErrorHandler::showErrorPage();
            }
        }

        return self::$instance;
    }
}
