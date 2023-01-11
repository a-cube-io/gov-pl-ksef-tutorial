<?php

# connect company

require '../bootstrap.php';

$query = "SELECT * FROM clients WHERE id = 1 LIMIT 1";

# fetch test client's record
$query = $dbConnection->prepare($query);
$query->execute();
$result = $query->fetch(\PDO::FETCH_ASSOC);

if (!$result) {
    exit;
}

# create integration for the client
$query = "
            INSERT 
                INTO integrations (id, client_id, name, status) 
            VALUES 
                (1, 1, 'ksef', 0)
        ";
$query = $dbConnection->prepare($query);
$query->execute();


# submit company to the A-Cube PL API
$payload = [
    "nip" => $result['tax_id'],
    "name" => $result['name'],
    "addressLine1" => $result['address_line1'],
    "addressLine2" => $result['address_line2'],
    "postcode" => $result['postcode'],
    "city" => $result['city'],
    "countryIso2" => $result['country_iso'],
    "email" => $result['email'],
];

# use httpclient to send post request to A-Cube PL API
$client = new \GuzzleHttp\Client(['http_errors' => false]);
$access_token = $_ENV['ACUBE_ACCESS_TOKEN'];

$response = $client->post($_ENV['ACUBE_API_URL'] . '/legal-entities', [
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

    # update integration for the client
    $query = "UPDATE integrations SET uuid = :uuid, status = :status WHERE id = :id";
    $statement = $dbConnection->prepare($query);
    $statement->execute(array(
        'id' => 1,
        'uuid' => $body->uuid,
        'status' => 100
    ));
    print "Received {$body->uuid} of the Company from A-Cube PL API\n";
} else {
    print_r($statusCode);
}
