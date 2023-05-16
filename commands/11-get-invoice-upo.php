<?php

# get invoice content


use ACube\Client\PlApi\lib\GovPlApi;

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

# api instance
$govPlApi = new GovPlApi(
    $_ENV['MAIN_URL'],
    $_ENV['ACUBE_AUTH_URL'],
    $_ENV['ACUBE_USER_EMAIL'],
    $_ENV['ACUBE_USER_PASSWORD']
);

$invoiceUuid = $_ENV['INVOICE_UUID'];

try {
    $result = $govPlApi->getInvoiceApi()->upoInvoiceItem($invoiceUuid);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling InvoiceApi->upoInvoiceItem: ', $e->getMessage(), PHP_EOL;
}
