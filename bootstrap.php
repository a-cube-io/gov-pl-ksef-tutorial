<?php

require 'vendor/autoload.php';

use Dotenv\Dotenv;
use Src\System\DatabaseConnection;

error_reporting(E_ALL);

$dotenv = new DotEnv(__DIR__);
$dotenv->load();

# connect to db
$dbConnection = (new DatabaseConnection())->getConnection();
