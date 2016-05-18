<?php

$installer = $this;

$installer->startSetup();
$dateTimeVar = date('Y-m-d H:i:s');
$installer->run("

ALTER TABLE `sm_rma_items` ADD `update_stock` TINYINT( 1 ) NOT NULL DEFAULT '1',
ADD `amount` FLOAT NOT NULL

");

$installer->run("

ALTER TABLE `sm_rma_exchangeitems` ADD `amount` FLOAT NOT NULL

");

$installer->endSetup();
