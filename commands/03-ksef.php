<?php

# submit company's ksef token

require '../bootstrap.php';

$query = "SELECT * FROM integrations WHERE id = 1 LIMIT 1";

# fetch test client's record
$query = $dbConnection->prepare($query);
$query->execute();
$result = $query->fetch(\PDO::FETCH_ASSOC);

if (!$result || !$result['uuid']) {
    exit;
}

# submit company to the A-Cube PL API
$payload = [
    "authorizationToken" => $_ENV['SAMPLE_KSEF_TOKEN'],
    "autoSelect" => false // IMPORTANT TO SET IT TO FALSE
];

# use httpclient to send post request to A-Cube PL API
$client = new \GuzzleHttp\Client(['http_errors' => false]);
$access_token = $_ENV['ACUBE_ACCESS_TOKEN'];

$response = $client->post($_ENV['ACUBE_API_URL'] . '/legal-entities/' . $result['uuid'] . '/ksef-token/create', [
    'headers' => [
        'Authorization' => 'Bearer ' . $access_token,
        'Content-Type' => 'application/json'
    ],
    'body' => json_encode($payload),
]);

$statusCode = $response->getStatusCode();
if ($statusCode === 201) {
    $body = $response->getBody()->getContents();
    $body = json_decode($body);

    # set integration status as active
    $query = "UPDATE integrations SET status = :status WHERE id = :id";
    $statement = $dbConnection->prepare($query);
    $statement->execute(array(
        'id' => 1,
        'status' => 200
    ));
} else {
    print_r($statusCode);
}
