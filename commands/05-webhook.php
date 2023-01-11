<?php

# submit webhooks for the company

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

$webhooks = [
    'legal-entity-invoice-sync',
    'legal-entity-on-boarding-activated',
    'legal-entity-session-manager',
    'legal-entity-invoice-sender',
    'legal-entity-invoice-receiver',
    'legal-entity-invoice-upo'
];

$exampleURL = 'https://webhook.site/888d7347-a0b3-493c-9e58-84b732bce2c9';

# use httpclient to send post request to A-Cube PL API
$client = new \GuzzleHttp\Client(['http_errors' => false]);
$access_token = $_ENV['ACUBE_ACCESS_TOKEN'];

foreach($webhooks as $webhook) {
    $payload = [
        "legalEntity" => $result['uuid'],
        "webhookType" => $webhook,
        "webhookUrl" => $exampleURL
    ];

    $response = $client->post($_ENV['ACUBE_API_URL'] . '/webhooks', [
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

        print_r($body);
        print "Webhook created\n";
    } else {
        print_r($statusCode);
    }
}