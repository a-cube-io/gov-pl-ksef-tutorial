<?php

# launch the company's runners

use ACube\Client\CommonApi\Authorization;
use ACube\Client\PlApi\lib\Api\AuthorizationFlowApi;
use ACube\Client\PlApi\lib\Configuration;
use ACube\Client\PlApi\lib\Model\KsefRevokeTokenAuthorizationFlowCollectionRequest;
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
    ->setApiKeyPrefix('Authorization','Bearer')
    ->setApiKey('Authorization', $access_token);

# api instance
$apiInstance = new AuthorizationFlowApi(new Client(), $config);

$ksef_revoke_token_authorization_flow_collection_request = new KsefRevokeTokenAuthorizationFlowCollectionRequest();
$ksef_revoke_token_authorization_flow_collection_request->setUuid($_ENV['ACUBE_ACCESS_TOKEN_UUID']);

try {
    $result = $apiInstance->ksefTokenSelectAuthorizationFlowCollection($result['uuid'], $ksef_revoke_token_authorization_flow_collection_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AuthorizationFlowApi->ksefTokenSelectAuthorizationFlowCollection: ', $e->getMessage(), PHP_EOL;
}
