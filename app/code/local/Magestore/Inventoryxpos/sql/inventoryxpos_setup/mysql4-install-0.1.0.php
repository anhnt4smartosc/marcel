<?php

$installer = $this;

$installer->startSetup();

$installer->run("

    DROP TABLE IF EXISTS {$this->getTable('erp_inventory_warehouse_xpos_user')};
    CREATE TABLE {$this->getTable('erp_inventory_warehouse_xpos_user')} (
      `warehouse_xpos_user_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `warehouse_id` INT(11) UNSIGNED NOT NULL,
      `xpos_user_id` INT(11) UNSIGNED NOT NULL,
      PRIMARY KEY (`warehouse_xpos_user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();