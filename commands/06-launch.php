<?php

# launch the company's runners

require '../bootstrap.php';

$query = "SELECT c.*, i.uuid 
          FROM clients as c 
          LEFT JOIN integrations as i ON i.client_id = c.id 
          WHERE c.id = 1 LIMIT 1";

# fetch test client's record
$query = $dbConnection->prepare($query);
$query->execute();
$result = $query->fetch(\PDO::FETCH_ASSOC);

if (!$result || !$result['uuid']) {
    exit;
}

# submit company to the A-Cube PL API
$payload = [
    "uuid" => $result['uuid']
];

# use httpclient to send post request to A-Cube PL API
$client = new \GuzzleHttp\Client(['http_errors' => false]);
$access_token = $_ENV['ACUBE_ACCESS_TOKEN'];

$response = $client->post($_ENV['ACUBE_API_URL'] . '/legal-entities/' . $result['uuid'] . '/ksef-token/select', [
    'headers' => [
        'Authorization' => 'Bearer ' . $access_token,
        'Content-Type' => 'application/json'
    ],
    'body' => json_encode($payload),
]);

$statusCode = $response->getStatusCode();
if ($statusCode === 200) {
    $body = $response->getBody()->getContents();
    $body = json_decode($body);

    print_r($body);
    print "Runners launched\n";
} else {
    print_r($statusCode);
}