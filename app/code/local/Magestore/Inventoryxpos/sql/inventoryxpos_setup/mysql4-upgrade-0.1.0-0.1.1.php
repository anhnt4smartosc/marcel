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
 * @package     Magestore_Inventoryxpos
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
$installer = $this;

$installer->startSetup();

$installer->run("

    ALTER TABLE {$this->getTable('erp_inventory_warehouse_xpos_user')}
    ADD CONSTRAINT fk_XposWarehouse
    FOREIGN KEY (`warehouse_id`)
    REFERENCES {$this->getTable('erp_inventory_warehouse')}(`warehouse_id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE;

    ALTER TABLE {$this->getTable('erp_inventory_warehouse_xpos_user')}
    ADD CONSTRAINT fk_XposUser
    FOREIGN KEY (`xpos_user_id`)
    REFERENCES {$this->getTable('sm_xpos_user')}(`xpos_user_id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE;

");

$installer->endSetup();