<?php

# connect company


use ACube\Client\CommonApi\lib\Api\LoginCheckApi;
use ACube\Client\CommonApi\lib\Model\LoginCheckPostRequest;
use ACube\Client\PlApi\lib\Api\LegalEntityApi;
use ACube\Client\PlApi\lib\Configuration;
use ACube\Client\PlApi\lib\Model\LegalEntityLegalEntityInput;
use GuzzleHttp\Client;

require __dir__.'./../bootstrap.php';

$query = "SELECT * FROM clients WHERE id = 1 LIMIT 1";

# fetch test client's record
$query = $dbConnection->prepare($query);
$query->execute();
$result = $query->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    exit;
}

# create integration for the client
$query = "
INSERT INTO integrations (id, client_id, name, status) 
VALUES (1, 1, 'ksef', 0)";
$query = $dbConnection->prepare($query);
$query->execute();

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
$apiInstance = new LegalEntityApi(new Client(), $config);

# add legal entity
$legal_entity_legal_entity_input = new LegalEntityLegalEntityInput();
$legal_entity_legal_entity_input->setNip($result['tax_id']);
$legal_entity_legal_entity_input->setName($result['name']);
$legal_entity_legal_entity_input->setAddressLine1($result['address_line1']);
$legal_entity_legal_entity_input->setAddressLine2($result['address_line2']);
$legal_entity_legal_entity_input->setPostcode($result['postcode']);
$legal_entity_legal_entity_input->setCity($result['city']);
$legal_entity_legal_entity_input->setCountryIso2($result['country_iso']);
$legal_entity_legal_entity_input->setEmail($result['email']);
$legal_entity_legal_entity_input->setProvince('');
$legal_entity_legal_entity_input->setUrl('');

try {
    $result = $apiInstance->postLegalEntityCollection($legal_entity_legal_entity_input);
} catch (Exception $e) {
    echo 'Exception when calling LegalEntityApi->postLegalEntityCollection: ', $e->getMessage(), PHP_EOL;
    die;
}

# update integration for the client
$query = "UPDATE integrations SET uuid = :uuid, status = :status WHERE id = :id";
$statement = $dbConnection->prepare($query);
$statement->execute(array(
    'id' => 1,
    'uuid' => $result->getUuid(),
    'status' => 100
));
print "Received {$result->getUuid()} of the Company from A-Cube PL API\n";
