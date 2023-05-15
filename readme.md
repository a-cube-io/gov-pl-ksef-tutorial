# ACube PL API

## Integration Steps

#### Purpose

This tutorial explains how to integrate your system with polish e-invocing using A-Cube PL API as a provider. As a
result you will understand how to connect company to KSeF, synchronize invoices from KSeF (payable and accountable),
receive notifications about invoice in KSeF.

#### About this tutorial

This example integration is written in native PHP language in the simplest way just so you can focus on understanding
the required elements you need to build at your side, plus proper sequence of activation of them

**Environment**

This tutorial use `sandbox` environment. You can easily change it by replacing API URLS in your config.

**Architecture**

To wrap things up, for successful integration with KSeF with A-Cube PL API, you will need following elements in your
system:

* Moduł do przechowyania kredencjałów potrzebnych do zalogowania się do A-Cube PL API.
* Moduł do integracji z A-Cube
* Moduł do przychodzących faktur
* Moduł do wysłanych faktur
* Powiadomienia (webhook)

### Pre-requisites

Before you start make sure you have following information that needs to be pasted into `.env` file

1. `ACUBE_USER_EMAIL` - This is you customer's username/email to A-Cube API.
2. `ACUBE_USER_PASSWORD` - Your password to A-Cube API
3. `SAMPLE_NIP` - This should be real NIP (Tax ID) number of your company registered with KSeF.
4. `SAMPLE_KSEF_TOKEN` - This should be real Authorization Token generated under your KSeF Web account.

**Where do I get these information?**

* 1, 2 - You need to obtain these credentials from A-Cube. (https://acubeapi.com/)
* 3 - You obviously should have it already if you are legally registered business in Poland
* 4 - You need to obtain them from KSeF Web App (https://ksef-demo.mf.gov.pl/web/login).

--- 
### Process

We prepared set of **commands** explaining each step of the process. You should implement these steps in defined
sequence into your system. For this demo purpose we will be launching command one by one, explaing in
details what happens.

Each step is defined as a script in `/commands` folder.

#### Login to the A-Cube

First you need to authenticate into A-Cube API Platform. As a result you will receive a JWT that lasts 24 hours.
It is required that in your system you will have solution to store and handle this token authentication.

More about Authentication you will find here: https://docs.acubeapi.com/documentation/gov-pl/

```shell
curl --location --request POST 'https://common-sandbox.api.acubeapi.com/login' \
--header 'Content-Type: application/json' \
--data-raw '{
    "email": "ACUBE_USER_EMAIL",
    "password": "ACUBE_USER_PASSWORD"
}'
```

#### Database

In your database you will need following tables:

| table   	       | description   	                                                           |
|-----------------|---------------------------------------------------------------------------|
| `clients` 	     | 	data about companies you will authorize to access KSeF                   | 
| `integrations`	 | 	records about integration status and UUID of the company in A-Cube PL API |
| `invoices`	     | 	synchronized invoices from KSeF                                          |

In `database` folder, you will find a SQL script that will set up this database structure for you. 

```sql
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
  ENGINE=INNODB;

```

#### Run seeder

Now, we will add sample company record to the database.

```
php 01-seeder.php
```

This should create a record in ``clients`` table with `id = 1`.

We will use this ID for test purpose in next steps.



#### Connect Company with A-Cube

Having company in database, we need to submit it to the A-Cube API to register in polish API. 
You need to provide all data for this payload. Please notice that both address lines are required. Additionaly, 
postcode must be valid postcode. Country ISO is a 2-letter code, i.e. Poland = PL 

```php
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
```

```
php 02-connect.php
```

* This script will create record in `integrations` table, related to the client's ID.
* Then, payload will be sent using POST method to the A-Cube PL API.
* As a result, you will receive ``uuid`` which is the ID of the company in A-Cube PL API system.
* At the end of this operation, integration's status should be set to 100 (this can be in fact any number you want. We assumed that it can be `0 - new entry`, `100 - pending`, `200 - active`)

#### Submit KSeF Token
Having company binded between your system and A-Cube PL API, we need to provide KSEF Token that will be used by 
A-Cube to access proper account in KSeF.

#### Submit Webhooks

#### Launch Runners

#### Invoice Synchronization

#### Run example commands

```sh
docker-compose up -d
docker-compose exec php composer run-set-project
#you should copy uuid from command (php ./commands/03-ksef.php) and put value to file .env: ACUBE_ACCESS_TOKEN_UUID=your new uuid
docker-compose exec php composer run-launch
docker-compose exec php composer run-invoices
```