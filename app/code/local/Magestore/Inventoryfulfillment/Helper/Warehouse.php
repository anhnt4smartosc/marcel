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
 * Inventoryfulfillment Helper
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryfulfillment
 * @author      Magestore Developer
 */
class Magestore_Inventoryfulfillment_Helper_Warehouse extends Mage_Core_Helper_Abstract {

    const PERMISSION_PICK_PACK = 'pick_pack_item';
    const PERMISSION_SHIP = 'ship_order';

    protected $_permittedWarehouses = array();

    /**
     * Get total available qty of product for picking
     * 
     * @param int $productId
     * @param int $warehouseId
     * @return int|float
     */
    public function getAvailablePickQty($productId, $warehouseId) {
        $product = $this->getWarehouseProduct($productId, $warehouseId);
        return ($product->getTotalQty() - $product->getPickingQty());
    }

    /**
     * Update picking_qty to warehouse_product table
     * 
     * @param int $productId
     * @param int $warehouseId
     * @param int|float $changeQty
     * @return Magestore_Inventoryplus_Model_Warehouse_Product
     */
    public function updatePickQty($productId, $warehouseId, $changeQty) {
        $product = $this->getWarehouseProduct($productId, $warehouseId);
        $pickingQty = max(0, $product->getPickingQty() + $changeQty);
        $product->setPickingQty($pickingQty)
                ->save();
        return $product;
    }

    /**
     * Get Warehouse Product object
     * 
     * @param int $productId
     * @param int $warehouseId
     * @return Magestore_Inventoryplus_Model_Warehouse_Product
     */
    public function getWarehouseProduct($productId, $warehouseId) {
        $product = Mage::getResourceModel('inventoryplus/warehouse_product_collection')
                ->addFieldToFilter('product_id', $productId)
                ->addFieldToFilter('warehouse_id', $warehouseId)
                ->getFirstItem();
        return $product;
    }

    /**
     * Get list of permitted warehouses
     * 
     * @param string $action
     * @return array
     */
    public function getPermittedWarehouses() {
        $action = $this->getCurrAction();
        if (!isset($this->_permittedWarehouses[$action])) {
            $permittedWarehouses = array();
            $warehouses = Mage::getModel('inventoryplus/warehouse')->getCollection();
            $warehouseHelper = Mage::helper('inventoryplus/warehouse');
            if (count($warehouses)) {
                foreach ($warehouses as $warehouse) {
                    if ($warehouseHelper->isAllowAction($action, $warehouse)) {
                        $permittedWarehouses[$warehouse->getId()] = $warehouse->getWarehouseName();
                    }
                }
            }
            $this->_permittedWarehouses[$action] = $permittedWarehouses;
        }
        return $this->_permittedWarehouses[$action];
    }

    /**
     * Get current fulfillment action
     * 
     * @return string
     */
    public function getCurrAction() {
        switch (Mage::app()->getRequest()->getControllerName()) {
            case 'inf_orderpacking':
                $action = self::PERMISSION_PICK_PACK;
                break;
            case 'inf_ordershipping':
                $action = self::PERMISSION_SHIP;
                break;
            default:
                $action = self::PERMISSION_PICK_PACK;
        }
        return $action;
    }

    /**
     * Get default permitted warehouse Id
     * 
     * @return int
     */
    public function getDefaultWarehouseId() {
        $action = $this->getCurrAction();
        $warehouses = $this->getPermittedWarehouses($action);
        if (count($warehouses)) {
            foreach ($warehouses as $warehouseId => $warehouseName) {
                return $warehouseId;
            }
        }
        return null;
    }

    /**
     * Check fulfillment permission of user
     * 
     * @return boolean
     */
    public function hasPermission() {
        if (!$this->getDefaultWarehouseId()) {
            return false;
        }
        return true;
    }

}
