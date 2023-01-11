<?php
# obtain a token

require '../bootstrap.php';

$payload = [
    "email" => $_ENV['ACUBE_USER_EMAIL'],
    "password" => $_ENV['ACUBE_USER_PASSWORD']
];

$client = new \GuzzleHttp\Client(['http_errors' => false]);

$response = $client->post($_ENV['ACUBE_AUTH_URL'] . '/login', [
    'headers' => [
        'Content-Type' => 'application/json'
    ],
    'body' => json_encode($payload),
]);

$statusCode = $response->getStatusCode();
if ($statusCode === 200) {
    $body = $response->getBody()->getContents();
    $body = json_decode($body);
    print $body->token;
} else {
    print_r($statusCode);
}
