<?php

/*****************************************************
 * This script is to clear non default billing & shipping addresses.
 * Use this script as part of deployment of DDI eCommPro release v1.2.3 or v1.2.4 version.
 * DONT run this script for other versions of DDI eCommPro
 ******************************************************/

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\ObjectManager;

require __DIR__ . '/app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);

$objectManager = $bootstrap->getObjectManager();
$connection = $objectManager->get(Magento\Framework\App\ResourceConnection::class)->getConnection();

$customerTable = "customer_entity";
$customerAddressTable = "customer_address_entity";

/* truncate all the addresses */
$deleteQuery = "DELETE FROM {$customerAddressTable}";
$res = $connection->query($deleteQuery);
echo 'All addresses has been deleted successfully.';

//$truncateQuery = "TRUNCATE TABLE {$customerAddressTable}";
//$connection->query($truncateQuery);
