<?php

# get invoice content

use ACube\Client\CommonApi\Authorization;
use ACube\Client\PlApi\lib\Api\InvoiceApi;
use ACube\Client\PlApi\lib\Configuration;
use GuzzleHttp\Client;

require __dir__.'./../bootstrap.php';

$query = "SELECT c.*, i.uuid 
          FROM clients as c 
          LEFT JOIN integrations as i ON i.client_id = c.id 
          WHERE c.id = 1 LIMIT 1";

# fetch test client's record
$query = $dbConnection->prepare($query);
$query->execute();
$result = $query->fetch(PDO::FETCH_ASSOC);

if (!$result || !$result['uuid']) {
    exit;
}

# create acube token
$authorization = new Authorization($_ENV['ACUBE_AUTH_URL']);
$access_token = $authorization->authorize($_ENV['ACUBE_USER_EMAIL'], $_ENV['ACUBE_USER_PASSWORD']);

# configuration api client
$config = Configuration::getDefaultConfiguration()
    ->setHost($_ENV['MAIN_URL'])
    ->setApiKeyPrefix('Authorization', 'Bearer')
    ->setApiKey('Authorization', $access_token);

# api instance
$apiInstance = new InvoiceApi(new Client(), $config);

# invoice uuid
$uuid = $_ENV['INVOICE_UUID'];

try {
    $result = $apiInstance->getInvoiceItem($uuid, 'application/json', true);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling InvoiceApi->getInvoiceItem: ', $e->getMessage(), PHP_EOL;
}

try {
    $result = $apiInstance->getInvoiceItem($uuid, 'application/xml', true);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling InvoiceApi->getInvoiceItem: ', $e->getMessage(), PHP_EOL;
}
