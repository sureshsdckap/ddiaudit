<?php

/*****************************************************
 * This script is to clear non default billing & shipping addresses.
 * Use this script as part of deployment of DDI eCommPro release v1.2.3 or v1.2.4 version.
 * DONT run this script for other versions of DDI eCommPro
 ******************************************************/

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\ObjectManager;

require __DIR__ . '/app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);

$objectManager = $bootstrap->getObjectManager();
$connection = $objectManager->get(Magento\Framework\App\ResourceConnection::class)->getConnection();

$customerTable = "customer_entity";
$customerAddressTable = "customer_address_entity";
$finalArr = array();

$defaultBillingQuery = "SELECT `default_billing` FROM {$customerTable}";
$billingResult = $connection->fetchAll($defaultBillingQuery);
if ($billingResult && count($billingResult)) {
    foreach ($billingResult as $res) {
        if ($res['default_billing'] != '') {
            $finalArr[] = $res['default_billing'];
        }
    }
}

$defaultShippingQuery = "SELECT `default_shipping` FROM {$customerTable}";
$shippingResult = $connection->fetchAll($defaultShippingQuery);
if ($shippingResult && count($shippingResult)) {
    foreach ($shippingResult as $res) {
        if ($res['default_shipping'] != '') {
            $finalArr[] = $res['default_shipping'];
        }
    }
}

array_unique($finalArr);
$idsToDelete = implode(', ', $finalArr);
echo "default addresses excluded in removal \n";
echo $idsToDelete;
$deleteQuery = "DELETE FROM {$customerAddressTable} WHERE `entity_id` NOT IN({$idsToDelete})";
$connection->query($deleteQuery);
echo "\n address delete done";
