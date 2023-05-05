<?php

require 'vendor/autoload.php';

use Src\System\DatabaseConnection;
use Symfony\Component\Dotenv\Dotenv;

error_reporting(E_ALL);

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env.example',__DIR__.'/.env');

# connect to db
$dbConnection = (new DatabaseConnection())->getConnection();
