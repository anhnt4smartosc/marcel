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
 * Inventoryfulfillment Model
 *
 * @category    Magestore
 * @package     Magestore_Inventoryfulfillment
 * @author      Magestore Developer
 */
class Magestore_Inventoryfulfillment_Model_Package extends Mage_Core_Model_Abstract {

    const STATUS_PICKING = 1;
    const STATUS_PACKING = 2;
    const STATUS_SHIPPING = 3;

    public function _construct() {
        parent::_construct();
        $this->_init('inventoryfulfillment/package');
    }

    /**
     * Add item to package
     * 
     * @param Mage_Sales_Model_Order_Item $item
     * @param int|float $qty
     */
    public function addItem($item, $qty, $warehouseId, $replace = false) {
        $package = $this->getCollection()
                ->addFieldToFilter('item_id', $item->getId())
                ->addFieldToFilter('warehouse_id', $warehouseId)
                ->getFirstItem();
        $qty_before = $package->getQty();
        if ($package->getId()) {
            if ($replace) {
                $package->setQty($qty);
            } else {
                $package->setQty($package->getQty() + $qty);
            }
        } else {
            $package->setOrderId($item->getOrderId())
                    ->setItemId($item->getId())
                    ->setProductId($item->getProductId())
                    ->setWarehouseId($warehouseId)
                    ->setQty($qty);
        }
        $package->save();
        $qty_after = $package->getQty();
        $qty_change = $qty_after - $qty_before;
        Mage::helper('inventoryfulfillment/warehouse')->updatePickQty($item->getProductId(), $warehouseId, $qty_change);
        return $package;
    }

    /**
     * Get need to pick qty in package
     *
     * @return int|float
     */
    public function getNeedToPickQty() {
        return max(0, $this->getQty() - $this->getPickedQty());
    }

    public function getPackages($orderId, $status, $warehouseId = null) {
        $sortPackages = array();
        $packages = $this->getCollection()
                ->addFieldToFilter('order_id', $orderId)
                ->addFieldToFilter('status', $status);
        if ($warehouseId) {
            $packages->addFieldToFilter('warehouse_id', $warehouseId);
        }
        return $packages;
    }

}
