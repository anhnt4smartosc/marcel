<?php
/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryfulfillment
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$connection = $installer->getConnection();

$installer->startSetup();

$installer->run("

");

// Add fulfillment status to sales_flat_order table
$connection->addColumn(
    $this->getTable('sales_flat_order'),
    'fulfillment_status',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'length' => 11,
        'default' => 0,
        'comment' => '1: verified, 2: picked, 3: packed, 4: shipping'
    )
);

$connection->addColumn(
    $this->getTable('sales_flat_order'),
    'package_web_path',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'comment' => 'Link of package image to display on browser'
    )
);

$connection->addColumn(
    $this->getTable('sales_flat_order'),
    'package_physical_path',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'comment' => 'Physical path of package image on server'
    )
);

$connection->addColumn(
    $this->getTable('sales_flat_order'),
    'products_web_path',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'comment' => 'Link of products image to display on browser. Each link is separated by semicolon'
    )
);

$connection->addColumn(
    $this->getTable('sales_flat_order'),
    'products_physical_path',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'comment' => 'Physical path of products image on server. Each path is separated by semicolon'
    )
);

$connection->addColumn(
    $this->getTable('sales_flat_order'),
    'num_products_pic',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'length' => 11,
        'default' => 0,
        'comment' => 'Number of product pictures of an order'
    )
);

$connection->addColumn(
    $this->getTable('sales_flat_order'),
    'product_name_index',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'length' => 11,
        'default' => 1,
        'comment' => 'Index to name product picture'
    )
);

$connection->addColumn(
    $this->getTable('sales_flat_order_item'),
    'assign_warehouse_id',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'comment' => 'Selected Warehouse to ship'
    )
);

$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('erp_inventory_fulfillment_log')};
    CREATE TABLE {$this->getTable('erp_inventory_fulfillment_log')} (
                `fulfillment_log_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `order_id` INT(11) UNSIGNED NOT NULL,
                `package_id` INT(11) UNSIGNED NOT NULL,
                `fulfillment_action` VARCHAR(255) NULL DEFAULT '',
                `updated_at` DATETIME NULL DEFAULT NULL,
	        `updated_by` VARCHAR(255) NULL DEFAULT '',
	        `status` TINYINT(1) NOT NULL DEFAULT '1',
                PRIMARY KEY  (`fulfillment_log_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            
    DROP TABLE IF EXISTS {$this->getTable('erp_inventory_fulfillment_package')};
    CREATE TABLE {$this->getTable('erp_inventory_fulfillment_package')} (
                `package_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `order_id` INT(11) UNSIGNED NOT NULL,
                `warehouse_id` INT(11) UNSIGNED NOT NULL,
                `item_id` INT(11) UNSIGNED NOT NULL,
                `product_id` INT(11) UNSIGNED NOT NULL,
                `qty` DECIMAL(12,4) UNSIGNED NOT NULL,
                `picked_qty` DECIMAL(12,4) UNSIGNED NOT NULL,
                `shipped_qty` DECIMAL(12,4) UNSIGNED NOT NULL,
                `status` TINYINT NOT NULL DEFAULT '1',
                PRIMARY KEY  (`package_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            
");
    
    
$connection->addColumn(
    $this->getTable('erp_inventory_warehouse_shipment'),
    'fulfillment_status',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'length' => 1,
        'default' => 1,
        'comment' => '1: shipped, 2: waiting'
    )
);

$connection->addColumn(
    $this->getTable('erp_inventory_warehouse_product'),
    'picking_qty',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length' => '12,4',
        'after' => 'available_qty',
        'default' => 0,
        'comment' => 'total of on-hold qty for picking'
    )
);   

$connection->addColumn(
    $this->getTable('erp_inventory_warehouse_permission'),
    'can_pick_pack_item',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'length' => 1,
        'default' => 0,
        'comment' => 'permission to pick and pack item in fulfillment process'
    )
);


$connection->addColumn(
    $this->getTable('erp_inventory_warehouse_permission'),
    'can_ship_order',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'length' => 1,
        'default' => 0,
        'comment' => 'permission to ship packages in fulfillment process'
    )
); 


$installer->endSetup();

