<?php

# launch the company's runners


use ACube\Client\PlApi\lib\GovPlApi;
use ACube\Client\PlApi\lib\Model\KsefRevokeTokenAuthorizationFlowCollectionRequest;

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

$ksef_revoke_token_authorization_flow_collection_request = new KsefRevokeTokenAuthorizationFlowCollectionRequest();
$ksef_revoke_token_authorization_flow_collection_request->setUuid($_ENV['ACUBE_ACCESS_TOKEN_UUID']);

try {
    $result = $govPlApi->getAuthorizationFlowApi()->ksefTokenSelectAuthorizationFlowCollection(
        $result['uuid'],
        $ksef_revoke_token_authorization_flow_collection_request
    );
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AuthorizationFlowApi->ksefTokenSelectAuthorizationFlowCollection: ', $e->getMessage(), PHP_EOL;
}
