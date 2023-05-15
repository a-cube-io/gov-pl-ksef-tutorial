<?php

# submit webhooks for the company


use ACube\Client\CommonApi\lib\Api\LoginCheckApi;
use ACube\Client\CommonApi\lib\Model\LoginCheckPostRequest;
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
