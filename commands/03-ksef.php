<?php


use ACube\Client\CommonApi\lib\Api\LoginCheckApi;
use ACube\Client\CommonApi\lib\Model\LoginCheckPostRequest;
use ACube\Client\PlApi\lib\Api\AuthorizationFlowApi;
use ACube\Client\PlApi\lib\Configuration;
use ACube\Client\PlApi\lib\Model\KsefTokenCreateAuthorizationFlowCollectionRequest;
use GuzzleHttp\Client;

# submit company's ksef token

require __dir__.'./../bootstrap.php';

$query = "SELECT * FROM integrations WHERE id = 1 LIMIT 1";

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
    ->setApiKeyPrefix('Authorization','Bearer')
    ->setApiKey('Authorization', $access_token);

# api instance
$apiInstance = new AuthorizationFlowApi(new Client(), $config);

# add ksef token
$ksef_token_create_authorization_flow_collection_request = new KsefTokenCreateAuthorizationFlowCollectionRequest();
$ksef_token_create_authorization_flow_collection_request->setAuthorizationToken($_ENV['SAMPLE_KSEF_TOKEN']);
$ksef_token_create_authorization_flow_collection_request->setAutoSelect(false);

try {
    $result = $apiInstance->ksefTokenCreateAuthorizationFlowCollection($result['uuid'], $ksef_token_create_authorization_flow_collection_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AuthorizationFlowApi->ksefTokenCreateAuthorizationFlowCollection: ', $e->getMessage(), PHP_EOL;
    die;
}

# set integration status as active
$query = "UPDATE integrations SET status = :status WHERE id = :id";
$statement = $dbConnection->prepare($query);
$statement->execute([
    'id' => 1,
    'status' => 200
]);
print "KSeF token is added.\n";
