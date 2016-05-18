<?php

$installer = $this;

$installer->startSetup();
$dateTimeVar = date('Y-m-d H:i:s');
$installer->run("

DROP TABLE IF EXISTS {$this->getTable('sm_rma_request')};
CREATE TABLE {$this->getTable('sm_rma_request')} (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `store_id` int(4) NOT NULL,
    `order_id` int(11) NOT NULL,
    `order_increment_id` varchar(128) NOT NULL,
    `customer_id` int(11) NOT NULL,
    `customer_name` varchar(255) NOT NULL,
    `customer_email` varchar(255) NOT NULL,
    `package_opened` tinyint(3) NOT NULL,
    `request_type` tinyint(3) NOT NULL,
    `status` varchar(32) NOT NULL,
    `created_time` datetime DEFAULT NULL,
    `created_by` varchar(32) NOT NULL
    ) ENGINE = MYISAM ;

DROP TABLE IF EXISTS {$this->getTable('sm_rma_items')};
CREATE TABLE {$this->getTable('sm_rma_items')} (
    `id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `rma_id` INT( 11 ) NOT NULL ,
    `item_id` INT( 11 ) NOT NULL ,
    `qty_to_return` SMALLINT( 6 ) NOT NULL,
    `done` tinyint(1) NOT NULL DEFAULT '0',
    `last_log` text NOT NULL,
    `update_stock` tinyint(1) NOT NULL DEFAULT '1',
    `amount` FLOAT NOT NULL
    ) ENGINE = MYISAM ;

DROP TABLE IF EXISTS {$this->getTable('sm_rma_exchangeitems')};
CREATE TABLE {$this->getTable('sm_rma_exchangeitems')} (
    `id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `rma_id` INT( 11 ) NOT NULL ,
    `item_id` INT( 11 ) NOT NULL ,
    `qty_to_exchange` SMALLINT( 6 ) NOT NULL,
    `done` tinyint(1) NOT NULL DEFAULT '0',
    `last_log` text NOT NULL,
    `amount` FLOAT NOT NULL
    ) ENGINE = MYISAM ;

DROP TABLE IF EXISTS {$this->getTable('sm_rma_comments')};
CREATE TABLE {$this->getTable('sm_rma_comments')} (
    `id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `rma_id` INT( 11 ) NOT NULL ,
    `customer_id` INT( 11 ) NOT NULL ,
    `customer_name` VARCHAR( 128 ) NOT NULL ,
    `customer_email` VARCHAR( 128 ) NOT NULL ,
    `content` TEXT NOT NULL ,
    `created_time` DATETIME NULL
) ENGINE = MYISAM ;

");


$installer->endSetup();
