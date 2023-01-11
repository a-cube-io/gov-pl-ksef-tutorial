<?php

require '../bootstrap.php';

$yourNIP = $_ENV['SAMPLE_NIP'];

$statement = "
    INSERT 
        INTO clients (id, tax_id, name, address_line1, address_line2, postcode, city, country_iso, email) 
    VALUES 
        (1, 
         $yourNIP,
         'MyCompany', 
         'AddressLine1',
         'AddressLine2',
         '00-001',
         'Warszawa',
         'PL', 
         'email@email.com');
";

try {
    $seeding = $dbConnection->exec($statement);
    echo "Seeding Completed!\n";
} catch (\PDOException $e) {
    exit($e->getMessage());
}
