<?php

# get invoice content


use ACube\Client\CommonApi\lib\Api\LoginCheckApi;
use ACube\Client\CommonApi\lib\Model\LoginCheckPostRequest;
use ACube\Client\PlApi\lib\Api\InvoiceAttachmentsApi;
use ACube\Client\PlApi\lib\Configuration;
use ACube\Client\PlApi\lib\Model\InvoiceAttachmentsInvoiceAttachmentOutput;
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
$config = \ACube\Client\CommonApi\lib\Configuration::getDefaultConfiguration()
    ->setHost('https://common-sandbox.api.acubeapi.com');

$authorization = new LoginCheckApi(new Client(), $config);
$access_token = $authorization->loginCheckPost(
    new LoginCheckPostRequest([
        'email' => $_ENV['ACUBE_USER_EMAIL'],
        'password' => $_ENV['ACUBE_USER_PASSWORD'],
    ])
)->getToken();

# configuration api client
$config = Configuration::getDefaultConfiguration()
    ->setHost($_ENV['MAIN_URL'])
    ->setApiKeyPrefix('Authorization', 'Bearer')
    ->setApiKey('Authorization', $access_token);

# api instance
$apiInstance = new InvoiceAttachmentsApi(new Client(), $config);
$invoiceUuid = $_ENV['INVOICE_UUID'];

try {
    $attachments = array_map(
        static function (InvoiceAttachmentsInvoiceAttachmentOutput $attachment) use ($apiInstance, $invoiceUuid) {
            return $apiInstance->getInvoiceAttachmentsItem($invoiceUuid, $attachment->getUuid(), "text/plain");
        },
        $apiInstance->getInvoiceAttachmentsCollection($invoiceUuid)
    );

    print_r($attachments);
} catch (Exception $e) {
    echo 'Exception when calling InvoiceAttachmentsApi->getInvoiceAttachmentsCollection: ', $e->getMessage(), PHP_EOL;
}
