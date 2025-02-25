<?php
namespace App;

use PDO;

class Config {
    const JWT_SECRET = 'CuboCardApiS3cr3tK3yJWT';

    public static $DB_HOST = '127.0.0.1';
    public static $DB_NAME = 'cubocard';
    public static $DB_USER = 'root';
    public static $DB_PASS = 'Uitgeest#22!';

    public static function getPDO() {
        try {
            $dsn = "mysql:host=" . self::$DB_HOST . ";dbname=" . self::$DB_NAME . ";charset=utf8mb4";
            return new PDO($dsn, self::$DB_USER, self::$DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        } catch (\PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
}