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

/**
 * Inventoryfulfillment Adminhtml Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryfulfillment
 * @author      Magestore Developer
 */
class Magestore_Inventoryfulfillment_Block_Adminhtml_Page_Warehouses extends Mage_Adminhtml_Block_Template {

    protected function _prepareLayout() {
        parent::_prepareLayout();
        $this->setTemplate('inventoryfulfillment/page/warehouses.phtml');
        return $this;
    }

    public function getWarehouses() {
        return $this->helper('inventoryplus/warehouse')->getAllWarehouseName();
    }

    public function getCurrWarehouseId() {
        return $this->helper('inventoryfulfillment')->getCurrWarehouseId();
    }

    /*
     * Get permitted warehouses
     * 
     * @param string $action
     * @return array
     */

    public function getPermittedWarehouses() {
        if (!$this->hasData('permitted_warehouse')) {
            $warehouses = $this->helper('inventoryfulfillment/warehouse')->getPermittedWarehouses();
            $this->setData('permitted_warehouse', $warehouses);
        }
        return $this->getData('permitted_warehouse');
    }

    /**
     * 
     * @param string $action
     * @return bool
     */
    public function hasPermission() {
        return $this->helper('inventoryfulfillment/warehouse')->hasPermission();
    }

    /**
     * Get default warehouse ID
     * 
     * @return int
     */
    public function getDefaultWarehouseId() {
        return $this->helper('inventoryfulfillment/warehouse')->getDefaultWarehouseId();
    }

}
