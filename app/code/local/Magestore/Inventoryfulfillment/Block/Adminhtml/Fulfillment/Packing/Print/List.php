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
class Magestore_Inventoryfulfillment_Block_Adminhtml_Fulfillment_Packing_Print_List extends Mage_Adminhtml_Block_Template {

    protected function _prepareLayout() {
        parent::_prepareLayout();
        $this->setTemplate('inventoryfulfillment/packing/print/list.phtml');
    }
    /**
     * Get sales order items in picking list
     * 
     * @return type
     */
    public function getItems() {
        $items = array();
        $packages = Mage::getModel('inventoryfulfillment/package')->getCollection()
                ->addFieldToFilter('order_id', array('in' => $this->getOrderIds()))
                ->addFieldToFilter('warehouse_id', $this->getWarehouseId());
        $packages->getSelect()->where(new Zend_DB_Expr('qty - shipped_qty > 0'));
        if (count($packages)) {
            foreach ($packages as $package) {
                if (isset($items[$package->getItemId()])) {
                    $items[$package->getItemId()] += $package->getQty() - $package->getShippedQty();
                } else {
                    $items[$package->getItemId()] = $package->getQty();
                }
            }
        }

        $orderItems = Mage::getResourceModel('sales/order_item_collection')
                ->addFieldToFilter('item_id', array('in' => array_keys($items)));
        if (count($orderItems)) {
            foreach ($orderItems as $orderItem) {
                $orderItem->setPickQty($items[$orderItem->getId()]);
            }
        }

        return $orderItems;
    }

    public function getOrderIds() {
        $orderIds = $this->getRequest()->getPost('orders');
        return $orderIds;
    }

    public function getWarehouseId() {
        $warehouseId = $this->helper('inventoryfulfillment')->getCurrWarehouseId();
        return $warehouseId;
    }
    
    public function getBarCodes($productId) {
        $barcodes = Mage::helper('inventorybarcode')->getBarcodeByProductId($productId);
        return $barcodes;
    }

}
