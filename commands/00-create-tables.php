<?php

require __dir__.'./../bootstrap.php';

if ($_ENV['DB_DRIVER'] === 'mysql') {
    $statement = "
CREATE TABLE clients
(
    id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    tax_id      VARCHAR(255) NOT NULL,
    name        VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
    address_line1       VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
    address_line2       VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
    postcode       VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
    city       VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
    country_iso VARCHAR(2)   NOT NULL COLLATE 'utf8mb4_unicode_ci',
    email       VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
    created_at  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at  TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id) USING BTREE
) COLLATE='utf8mb4_unicode_ci'
  ENGINE=INNODB;

CREATE TABLE integrations
(
    id         BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    client_id  BIGINT(20) UNSIGNED NOT NULL,
    name       VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
    uuid       VARCHAR(255) DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
    status     INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id) USING BTREE,
    INDEX      `integrations_client_id_foreign` (`client_id`) USING BTREE,
    CONSTRAINT `integrations_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
) COLLATE='utf8mb4_unicode_ci'
  ENGINE=INNODB;

CREATE TABLE invoices
(
    id                            BIGINT(20) NOT NULL AUTO_INCREMENT,
    client_id                     BIGINT(20) UNSIGNED NOT NULL,
    invoice_number                VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
    invoice_date                  DATE NULL DEFAULT NULL,
    einvoice_gov_reference_number VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
    einvoice_acquisition_time     DATETIME NULL DEFAULT NULL,
    einvoice_legal_entity_uuid    VARCHAR(128) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
    einvoice_uuid                 VARCHAR(128) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
    einvoice_status               VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
    sender_company                VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
    sender_tax_id                 VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
    recipient_company             VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
    recipient_tax_id              VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
    einvoice_unique_url           VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
    amount                        INT(10) NULL DEFAULT NULL,
    currency                      VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
    direction                     TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
    created_at                    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id) USING BTREE,
    UNIQUE INDEX `invoices_einvoice_gov_reference_number_unique` (`einvoice_gov_reference_number`) USING BTREE,
    UNIQUE INDEX `invoices_einvoice_uuid_unique` (`einvoice_uuid`) USING BTREE,
    INDEX                         `fk_invoices_client_idx` (`client_id`) USING BTREE,
    CONSTRAINT `fk_invoices_client_idx` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
) COLLATE='utf8mb4_unicode_ci'
  ENGINE=INNODB;";

} elseif ($_ENV['DB_DRIVER'] === 'pgsql') {
    $statement = "
CREATE TABLE clients
(
    id          SERIAL PRIMARY KEY,
    tax_id      VARCHAR(255) NOT NULL,
    name        VARCHAR(255) NOT NULL,
    address_line1       VARCHAR(255) NOT NULL,
    address_line2       VARCHAR(255) NOT NULL,
    postcode       VARCHAR(255) NOT NULL,
    city       VARCHAR(255) NOT NULL,
    country_iso VARCHAR(2)   NOT NULL,
    email       VARCHAR(255) NOT NULL,
    created_at  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at  TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE integrations
(
    id         SERIAL PRIMARY KEY,
    client_id  INTEGER NOT NULL,
    name       VARCHAR(255) NOT NULL,
    uuid       VARCHAR(255) DEFAULT NULL,
    status     INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE invoices
(
    id                            SERIAL PRIMARY KEY,
    client_id                     INTEGER NOT NULL,
    invoice_number                VARCHAR(255) NULL DEFAULT NULL,
    invoice_date                  DATE NULL DEFAULT NULL,
    einvoice_gov_reference_number VARCHAR(255) NULL DEFAULT NULL,
    einvoice_acquisition_time     TIMESTAMP NULL DEFAULT NULL,
    einvoice_legal_entity_uuid    VARCHAR(128) NULL DEFAULT NULL,
    einvoice_uuid                 VARCHAR(128) NULL DEFAULT NULL,
    einvoice_status               VARCHAR(50) NULL DEFAULT NULL,
    sender_company                VARCHAR(255) NULL DEFAULT NULL,
    sender_tax_id                 VARCHAR(255) NULL DEFAULT NULL,
    recipient_company             VARCHAR(255) NULL DEFAULT NULL,
    recipient_tax_id              VARCHAR(255) NULL DEFAULT NULL,
    einvoice_unique_url           VARCHAR(255) NULL DEFAULT NULL,
    amount                        INTEGER NULL DEFAULT NULL,
    currency                      VARCHAR(255) NULL DEFAULT NULL,
    direction                     INTEGER NOT NULL DEFAULT '0',
    created_at                    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
)
";
} else {
    throw new LogicException('This statement is not implemented.');
}


$seeding = $dbConnection->exec($statement);
echo "Tables created.\n";
