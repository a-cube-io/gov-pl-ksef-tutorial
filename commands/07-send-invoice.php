<?php

# send invoice


use ACube\Client\CommonApi\lib\Api\LoginCheckApi;
use ACube\Client\CommonApi\lib\Model\LoginCheckPostRequest;
use ACube\Client\PlApi\lib\Api\InvoiceApi;
use ACube\Client\PlApi\lib\Configuration;
use ACube\Client\PlApi\lib\Model\AdnotacjeAType;
use ACube\Client\PlApi\lib\Model\FaAType;
use ACube\Client\PlApi\lib\Model\InvoiceFaktura;
use ACube\Client\PlApi\lib\Model\KodFormularzaAType;
use ACube\Client\PlApi\lib\Model\Podmiot1AType;
use ACube\Client\PlApi\lib\Model\Podmiot2AType;
use ACube\Client\PlApi\lib\Model\TAdresPolskiType;
use ACube\Client\PlApi\lib\Model\TAdresType;
use ACube\Client\PlApi\lib\Model\TNaglowekType;
use ACube\Client\PlApi\lib\Model\TPodmiot1Type;
use ACube\Client\PlApi\lib\Model\TPodmiotType;
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
    ->setApiKeyPrefix('Authorization', 'Bearer')
    ->setApiKey('Authorization', $access_token);

# api instance
$apiInstance = new InvoiceApi(new Client(), $config);

$invoice_faktura = (new InvoiceFaktura())
    ->setNaglowek(
        (new TNaglowekType())
            ->setKodFormularza(
                (new KodFormularzaAType())
                    ->setValue('FA')
                    ->setKodSystemowy('FA (1)')
                    ->setWersjaSchemy('1-0E')
            )
            ->setWariantFormularza('1')
            ->setDataWytworzeniaFa(new DateTime())
    )
    ->setPodmiot1(
        (new Podmiot1AType())
            ->setDaneIdentyfikacyjne((new TPodmiot1Type())->setNIp($_ENV['SAMPLE_NIP'])->setPelnaNazwa('Firma'))
            ->setAdres(
                (new TAdresType())->setAdresPol(
                    (new TAdresPolskiType())
                        ->setKodKraju('PL')
                        ->setKodPocztowy('00-000')
                        ->setMiejscowosc('City')
                        ->setUlica('Szosa')
                        ->setNrDomu('9')
                )
            )
    )
    ->setPodmiot2(
        (new Podmiot2AType())
            ->setDaneIdentyfikacyjne((new TPodmiotType())->setBrakId(1))
            ->setAdres(
                (new TAdresType())->setAdresPol(
                    (new TAdresPolskiType())
                        ->setKodKraju('PL')
                        ->setKodPocztowy('00-000')
                        ->setMiejscowosc('City')
                        ->setUlica('Szosa')
                        ->setNrDomu('9')
                )
            )
    )
    ->setFa(
        (new FaAType())
            ->setKodWaluty('PLN')
            ->setP1(new DateTime('2022-01-01'))
            ->setP2('FV/2022/TEST')
            ->setP6(new DateTime('2022-01-01'))
            ->setP15(1666.66)
            ->setP141(333.34)
            ->setP131(2000)
            ->setP133(0.95)
            ->setP143(0.05)
            ->setRodzajFaktury('VAT')
            ->setAdnotacje(
                (new AdnotacjeAType())
                    ->setP16('2')
                    ->setP17('2')
                    ->setP18('2')
                    ->setP18A('2')
                    ->setP19('2')
                    ->setP22('2')
                    ->setP23('2')
                    ->setPPMarzy('2')
            )
    );

try {
    $result = $apiInstance->postV1InvoiceCollection($invoice_faktura);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling InvoiceApi->postV1InvoiceCollection: ', $e->getMessage(), PHP_EOL;
}
