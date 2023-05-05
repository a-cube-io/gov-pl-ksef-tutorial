<?php

namespace Src\System;

class DatabaseConnection
{
    private ?\PDO $dbConnection = null;

    public function __construct()
    {
        $driver = $_ENV['DB_DRIVER'];
        $host = $_ENV['DB_HOST'];
        $port = $_ENV['DB_PORT'];
        $db = $_ENV['DB_DATABASE'];
        $user = $_ENV['DB_USERNAME'];
        $pass = $_ENV['DB_PASSWORD'];

        $this->dbConnection = new \PDO("$driver:host=$host;port=$port;dbname=$db", $user, $pass);
    }

    public function getConnection(): ?\PDO
    {
        return $this->dbConnection;
    }
}
