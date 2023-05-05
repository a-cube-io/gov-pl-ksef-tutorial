<?php

require __dir__.'./../bootstrap.php';

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
} catch (PDOException $e) {
    echo "If you don't created tables. Try run this command: \"php ./commands/00-create-tables.php\"\n\n";
    exit($e->getMessage());
}
