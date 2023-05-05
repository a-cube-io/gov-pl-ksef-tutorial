<?php

# submit webhooks for the company

use ACube\Client\CommonApi\Authorization;
use ACube\Client\PlApi\lib\Api\WebhookApi;
use ACube\Client\PlApi\lib\Configuration;
use ACube\Client\PlApi\lib\Model\WebhookWebhookInput;
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

$legalEntityUuid = $result['uuid'];
$webhooks = [
    'legal-entity-invoice-receiver',
    'legal-entity-invoice-sender',
    'legal-entity-invoice-sync',
    'legal-entity-invoice-upo',
    'legal-entity-on-boarding-activated',
    'legal-entity-on-boarding-deactivated',
    'legal-entity-session-manager',
];

$exampleURL = 'https://webhook.site/f1eeb992-0153-450f-bf28-356ca1a82880';

# create acube token
$authorization = new Authorization($_ENV['ACUBE_AUTH_URL']);
$access_token = $authorization->authorize($_ENV['ACUBE_USER_EMAIL'], $_ENV['ACUBE_USER_PASSWORD']);

# configuration api client
$config = Configuration::getDefaultConfiguration()
    ->setHost($_ENV['MAIN_URL'])
    ->setApiKeyPrefix('Authorization','Bearer')
    ->setApiKey('Authorization', $access_token);

# api instance
$apiInstance = new WebhookApi(new Client(), $config);

# send webhook data
foreach($webhooks as $webhook) {
    $webhook_webhook_input = new WebhookWebhookInput();
    $webhook_webhook_input->setLegalEntity($legalEntityUuid);
    $webhook_webhook_input->setWebhookType($webhook);
    $webhook_webhook_input->setWebhookUrl($exampleURL);

    try {
        $result = $apiInstance->postWebhookCollection($webhook_webhook_input);
        print "Webhook created\n";
        print_r($result);
    } catch (Exception $e) {
        echo 'Exception when calling WebhookApi->postWebhookCollection: ', $e->getMessage(), PHP_EOL;
    }
}
