<?php
/*****************************************************
 * This script is to clear active carts.
 * Use this script as part of deployment of DDI eCommPro release v1.2.3 or v1.2.4 version.
 * DONT run this script for other versions of DDI eCommPro
******************************************************/
use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\ObjectManager;
require __DIR__ . '/app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);

$objectManager = $bootstrap->getObjectManager();

//$state = $obj->get(Magento\Framework\App\State::class);
//$state->setAreaCode('frontend');
$cartTableName = "quote";
$connection = $objectManager->get(Magento\Framework\App\ResourceConnection::class)->getConnection();

$cartTable = $connection->getTableName($cartTableName);
$clearActiveCart = "UPDATE `".$cartTableName."` SET `is_active`= 0 WHERE is_active = 1";
$connection->query($clearActiveCart);
echo "Active Carts Cleared";
