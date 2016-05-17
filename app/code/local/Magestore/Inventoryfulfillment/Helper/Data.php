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
class Magestore_Inventoryfulfillment_Helper_Data extends Mage_Core_Helper_Abstract {

    const STATUS_VERIFIED = 1;
    const STATUS_PICKED = 2;
    const STATUS_PACKED = 3;
    const STATUS_SHIPPING = 4;
    const GRID_ID = 'fulfillment_grid_id';

    protected $_warehouseProducts = array();

    public function getFulfillmentStep() {
        return Mage::registry('fulfillment_step');
    }

    public function getCurrGridId() {
        return Mage::registry(self::GRID_ID);
    }

    /**
     * 
     * @return array
     */
    public function getNewOrderStatuses() {
        $statuses = Mage::getStoreConfig('inventoryplus/fulfillment/neworderstatus');
        $statuses = explode(',', $statuses);
        if (!count($statuses)) {
            $statuses = array('pending_payment', 'pending', 'holded', 'pending_paypal');
        }
        return $statuses;
    }

    /**
     * 
     * @return array
     */
    public function getNewOrderStatusOptionArray() {
        $options = array();
        $statuses = $this->getNewOrderStatuses();
        $statusLabels = Mage::getSingleton('sales/order_config')->getStatuses();
        foreach ($statuses as $status) {
            if (isset($statusLabels[$status])) {
                $options[] = array('value' => $status, 'label' => $statusLabels[$status]);
            }
        }
        return $options;
    }

    /**
     * 
     * @return array
     */
    public function getNewOrderStatusOption() {
        $options = array();
        $statuses = $this->getNewOrderStatuses();
        $statusLabels = Mage::getSingleton('sales/order_config')->getStatuses();
        foreach ($statuses as $status) {
            if (isset($statusLabels[$status])) {
                $options[$status] = $statusLabels[$status];
            }
        }
        return $options;
    }

    /**
     * Get barcode from product sku
     *
     * @param $sku
     * @return string
     */
    public function getBarcodeFromSku($sku) {
        $barcode = Mage::getModel('inventorybarcode/barcode')->getCollection()
                ->addFieldToFilter('product_sku', array('eq' => $sku))
                ->getFirstItem()
                ->getData('barcode');
        if ($barcode) {
            return $barcode;
        } else {
            return '';
        }
    }
    
    /**
     * Get barcode from product Id
     *
     * @param int $productId
     * @return string|array
     */    
    public function getBarcodeFromProductId($productId, $asArray = false) {
        $barcodes = Mage::helper('inventorybarcode')->getBarcodeByProductId($productId);
        if($asArray) {
            return $barcodes;
        } else {
        return implode(', ', $barcodes);
        }
    }

    /**
     * Get shipping methods in packaged orders
     * 
     * @return array
     */
    public function getAvailableCarriers() {
        $carriers = array();
        $resource = Mage::getSingleton('core/resource');
        $shipmentIds = $this->getWaitingShipmentIds();
        $shipmentIds = implode("','", $shipmentIds);

        $orders = Mage::getResourceModel('sales/order_collection');
        $orders->getSelect()->join(
                array(
            'shipment' => $resource->getTableName('sales_flat_shipment_grid')), 'main_table.entity_id = shipment.order_id AND shipment.entity_id in (\'' . $shipmentIds . '\')', array(
            'shipment_id' => 'shipment.entity_id'
        ));
        if (count($orders)) {
            foreach ($orders as $order) {
                list($carrierCode, $method) = explode('_', $order->getShippingMethod(), 2);
                if ($carrierCode && $method) {
                    $carriers[$carrierCode] = $carrierCode;
                } else {
                    $carriers['no_shipping'] = 'no_shipping';
                }
            }
        }

        if (count($carriers)) {
            foreach ($carriers as $carrierCode => $value) {
                $className = Mage::getStoreConfig('carriers/' . $carrierCode . '/model');
                if ($className) {
                    $carrierModel = Mage::getModel($className);
                    $carriers[$carrierCode] = $carrierModel->getConfigData('title');
                } elseif ($carrierCode == 'no_shipping') {
                    $carriers[$carrierCode] = $this->__('No Shipping Method');
                } else {
                    unset($carriers[$carrierCode]);
                }
            }
        }

        return $carriers;
    }

    /**
     * 
     * @param string $carrierCode
     * @return array
     */
    public function getShippingMethodsFromCarrierCode($carrierCode) {
        $methods = array();
        $className = Mage::getStoreConfig('carriers/' . $carrierCode . '/model');
        if ($className) {
            $carrierModel = Mage::getModel($className);
            $availMethods = $carrierModel->getAllowedMethods();
            if (count($availMethods)) {
                foreach ($availMethods as $methodCode => $availMethod) {
                    $methods[$methodCode] = $carrierCode . '_' . $methodCode;
                }
            }
        }
        return $methods;
    }

    public function getShippingDescription($method) {
        $description = '';
        if ($method) {
            list($carrierCode, $methodCode) = explode('_', $method, 2);
            $className = Mage::getStoreConfig('carriers/' . $carrierCode . '/model');
            if ($className) {
                $carrierModel = Mage::getModel($className);
                if ($carrierModel) {
                    $description .= $carrierModel->getConfigData('title');
                    $availMethods = $carrierModel->getAllowedMethods();
                    if (isset($availMethods[$methodCode])) {
                        $description .= ' - ' . $availMethods[$methodCode];
                    }
                }
            }
        }
        return $description;
    }

    /**
     * Get need to ship qty of sales order item 
     * 
     * @param type $orderItem
     * @return int|float
     */
    public function getNeedToShipQty($orderItem) {
        $order = $orderItem->getOrder();
        $hadRefundedQty = 0;
        /* calculate refunded qty */
        $creditmemos = $order->getCreditmemosCollection();
        if (count($creditmemos)) {
            foreach ($creditmemos as $creditmemo) {
                $item = $creditmemo->getItemByOrderId($orderItem->getId());
                if ($item) {
                    $parentItem = $orderItem->getParentItemId() ? $creditmemo->getItemByOrderId($orderItem->getParentItemId()) : false;
                    $hadRefundedQty += $parentItem ? ($parentItem->getQty() * $item->getQty()) : $item->getQty();
                }
            }
        }
        /* calculate canceled qty */
        $canceledQty = $orderItem->getQtyCanceled();
        if ($orderItem->getParentItemId()) {
            $parentOrderItem = $order->getItemById($orderItem->getParentItemId());
            if ($parentOrderItem && $parentOrderItem->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                $canceledQty = $parentOrderItem->getQtyCanceled();
            }
        }
        /* calculate shipped qty */
        $shippedQty = $orderItem->getQtyShipped();
        if ($orderItem->getParentItemId()) {
            $parentOrderItem = $order->getItemById($orderItem->getParentItemId());
            if ($parentOrderItem && $parentOrderItem->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                $shippedQty = $parentOrderItem->getQtyShipped();
            }
        }
        $needToShipQty = ($orderItem->getQtyOrdered() - $shippedQty - $hadRefundedQty - $canceledQty);
        $needToShipQty = ($needToShipQty >= 0) ? $needToShipQty : 0;
        return $needToShipQty;
    }

    public function getWarehouseSelectBoxByOrderItem($orderItem) {
        $orderId = $orderItem->getOrderId();
        $productId = $orderItem->getProductId();
        $orderItemId = $orderItem->getId();
        $needToShipQty = $this->getNeedToShipQty($orderItem);

        if (!$firstWarehouse = $orderItem->getAssignWarehouseId()) {
            $warehouseOrder = Mage::getModel('inventoryplus/warehouse_order')->getCollection()
                    ->addFieldToFilter('order_id', $orderId)
                    ->addFieldToFilter('product_id', $productId);
            $firstWarehouse = $warehouseOrder->getFirstItem()->getWarehouseId();
        }

        $allWarehouse = Mage::helper('inventoryplus/warehouse')->getAllWarehouseNameEnable();
        $warehouseProductModel = Mage::getModel('inventoryplus/warehouse_product')->getCollection()
                ->addFieldToFilter('product_id', $productId)
                ->setOrder('total_qty', 'DESC');

        $return = "<select class='warehouse-shipment' name='$orderItemId' id='warehouse_shipment[$orderItemId]' onchange='fulfillmentObj.selectWarehouseToShip(this);'> ";
        $this->_warehouseProducts[$orderItem->getId()] = array();
        foreach ($warehouseProductModel as $model) {
            $warehouseId = $model->getWarehouseId();
            if (!isset($allWarehouse[$warehouseId]))
                continue;
            $warehouseName = $allWarehouse[$warehouseId];
            /* subtract picking qty */
            $productQty = $model->getTotalQty() - $model->getPickingQty();
            /* empty qty */
            if ($productQty <= 0) {
                //continue;
            }
            if ($warehouseName != '') {
                if (!$firstWarehouse)
                    $firstWarehouse = $warehouseId;
                $return .= "<option value='$warehouseId' ";
                if ($warehouseId == $firstWarehouse) {
                    $return .= ' selected';
                }
                $return .= ">$warehouseName (" . ($productQty + 0) . ")</option>";
                $this->_warehouseProducts[$orderItem->getId()][$warehouseId] = $productQty + 0;
            }
        }
        $return .= "</select>";

        if (!count($this->_warehouseProducts[$orderItem->getId()])) {
            return null;
        }
        $script = '<div style="display:none" id="warehouse_qty_json_' . $orderItem->getId() . '">' . json_encode($this->_warehouseProducts[$orderItem->getId()]) . '</div>';
        return $return . $script;
    }

    /**
     * Get list of available warehouses for an order item
     * 
     * @param type $orderItem
     * @return array
     */
    public function getWarehouseQtys($orderItem) {
        if (isset($this->_warehouseProducts[$orderItem->getId()])) {
            return $this->_warehouseProducts[$orderItem->getId()];
        }
        return null;
    }

    /**
     * Get stock to primary warehouse to ship
     * 
     */
    public function prepareStockToShip($order) {
        $requestStock = Mage::getModel('inventorywarehouse/requeststock');
        $warehouse = Mage::helper('inventoryplus/warehouse')->getPrimaryWarehouse();
        $data = array();
        $data['warehouse_id_from'] = 0;
        $data['warehouse_name_from'] = 'Others';
        $data['warehouse_id_to'] = $warehouse->getId();
        $data['warehouse_name_to'] = $warehouse->getWarehouseName();
        $data['created_at'] = now();
        $data['created_by'] = Mage::getSingleton('admin/session')->getUser()->getUsername();
        $data['status'] = 1;
        $data['reason'] = $this->__('Request stock to ship order #%s', $order->getIncrementId());
        $itemData = array();
        $totalProducts = 0;
        foreach ($order->getAllItems() as $item) {
            if ($item->getProduct()->isComposite())
                continue;
            if ($needToShipQty = $this->getNeedToShipQty($item)) {
                $itemData[$item->getId()] = array('product_id' => $item->getProductId(),
                    'product_sku' => $item->getSku(),
                    'product_name' => $item->getName(),
                    'qty' => $needToShipQty
                );
                $totalProducts += $needToShipQty;
            }
        }
        $data['total_products'] = $totalProducts;
        if (count($itemData)) {
            $requestStock->setData($data)->save();
            foreach ($itemData as $sitemData) {
                $sitemData['warehouse_requeststock_id'] = $requestStock->getId();
                Mage::getModel('inventorywarehouse/requeststock_product')
                        ->setData($sitemData)
                        ->save();
                /* update stock to warehouse */
                $warehouse->updateStock($sitemData['product_id'], $sitemData['qty'], $sitemData['qty']);
                /* update qty catalog */
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($sitemData['product_id']);
                $stockItem->setQty($stockItem->getQty() + $sitemData['qty'])
                        ->save();
            }
        }
    }

    public function isNullOrEmptyString($str) {
        return (!isset($str) || trim($str) === '');
    }

    /**
     * Send notify email to customers after changed order
     * 
     * 
     * @param Mage_Sales_Model_Order $order
     */
    public function sendMailToNotifyCustomer($order) {
        $template_id = 'inventoryfulfillment_email_notify_to_customer';

        $email_to = $order->getCustomerEmail();
        $customer_name = $order->getCustomerName();

        $email_template = Mage::getModel('core/email_template')->loadDefault($template_id);

        $email_template_variables = array(
            'order' => $order,
            'store' => Mage::app()->getStore($order->getStoreId())
        );

        $sender_name = Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_NAME);
        $sender_email = Mage::getStoreConfig('trans_email/ident_general/value') ?
                Mage::getStoreConfig('trans_email/ident_general/value') :
                Mage::getSingleton('core/config')->init()->getXpath('/config/default/trans_email/ident_general/email');
        $email_template->setSenderName($sender_name);
        $email_template->setSenderEmail($sender_email);

        try {
            $email_template->send($email_to, $customer_name, $email_template_variables);
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }
    }

    /**
     * Get admin user
     *
     * @return mixed
     */
    public function getAdminUser() {
        if (Mage::getSingleton('admin/session')->getUser()->getId()) {
            return Mage::getSingleton('admin/session')->getUser();
        }
    }

    /**
     * Log fulfillment action
     *
     * @param $order
     */
    public function createFulfillmentLog($package) {
        $admin = $this->getAdminUser()->getUsername();
        $fulfillmentStatus = $package->getFulfillmentStatus();
        $orderId = $package->getOrderId();
        $order = Mage::getModel('sales/order')->load($orderId);
        $packageId = $package->getShipmentId();
        $fulfillmentStatusMessages = Mage::getSingleton('inventoryfulfillment/order_fulfillmentstatus')->getOptionArray();
        $fulfillmentStatusMessage = $fulfillmentStatusMessages[$fulfillmentStatus];
        $fulfillmentLog = Mage::getModel('inventoryfulfillment/fulfillmentlog')
                ->setData('updated_at', date("Y-m-d H:i:s"))
                ->setData('updated_by', $admin)
                ->setData('package_id', $packageId)
                ->setData('order_id', $orderId)
                ->setData('status', $fulfillmentStatus)
                ->setData('fulfillment_action', $this->__('Account ' . $admin . ' changed package #' . $packageId . ' status of order #' . $order->getIncrementId() . ' to ' . $fulfillmentStatusMessage));

        try {
            $fulfillmentLog->save();
        } catch (Exception $e) {
            
        }
    }

    public function getFulfillmentLogCollection() {
        $dateFrom = date('Y-m-d', strtotime('-30 days'));
        $dateTo = date('Y-m-d 23:59:59');
        $collection = Mage::getModel('inventoryfulfillment/fulfillmentlog')->getCollection();
        $collection->addFieldToFilter('updated_at', array(
            'from' => $dateFrom,
            'to' => $dateTo,
            'date' => true,
        ));
        $collection->getSelect()->columns(array('date_without_hour' => 'date(`updated_at`)'));
        return $collection;
    }

    public function isChangedToPicking($order) {
        return true;
    }

    public function getPickingOrderIds($warehouseId = 0) {
        $orderIds = array();
        $packages = Mage::getModel('inventoryfulfillment/package')
                ->getCollection()
                ->addFieldToFilter('status', Magestore_Inventoryfulfillment_Model_Package::STATUS_PICKING)
        ;
        $packages->getSelect()->where(new Zend_Db_Expr('(qty - picked_qty) > 0'));
        if ($warehouseId) {
            $packages->addFieldToFilter('warehouse_id', $warehouseId);
        }

        if (count($packages)) {
            foreach ($packages as $package) {
                $orderIds[$package->getOrderId()] = $package->getOrderId();
            }
        }
        return $orderIds;
    }

    /**
     * Get total picking qty of Item
     * 
     * @param int $itemId
     * @param int $warehouseId
     * @return int|float
     */
    public function getPickingQty($itemId, $warehouseId = 0) {
        $pickingQty = 0;
        $packages = Mage::getModel('inventoryfulfillment/package')
                ->getCollection()
                ->addFieldToFilter('item_id', $itemId)
                ->addFieldToFilter('status', Magestore_Inventoryfulfillment_Model_Package::STATUS_PICKING);
        if ($warehouseId) {
            $packages->addFieldToFilter('warehouse_id', $warehouseId);
        }
        if (count($packages)) {
            foreach ($packages as $package) {
                $pickingQty+= $package->getQty() - $package->getPickedQty();
            }
        }
        return $pickingQty;
    }

    /**
     * Get need to pick qty of item
     * 
     * @param Mage_Sales_Model_Order_Item $item
     * @return int|float
     */
    public function getNeedToPickQty($item) {
        return $this->getNeedToShipQty($item) - $this->getPickingQty($item->getId());
    }

    /**
     * Get waiting shipment Ids
     * 
     * @return array
     */
    public function getWaitingShipmentIds() {
        $warehouseId = $this->getCurrWarehouseId();
        $shipmentIds = array();
        $shipments = Mage::getModel('inventoryplus/warehouse_shipment')
                ->getCollection()
                ->addFieldToFilter('warehouse_id', $warehouseId)
                ->addFieldToFilter('fulfillment_status', 2);
        if (count($shipments)) {
            foreach ($shipments as $shipment) {
                $shipmentIds[$shipment->getShipmentId()] = $shipment->getShipmentId();
            }
        }
        return $shipmentIds;
    }

    public function getCurrWarehouseId() {
        if ($warehouseId = Mage::getSingleton('adminhtml/session')->getData('curr_warehouse_id')) {
            return $warehouseId;
        }
        $warehouseId = Mage::helper('inventoryfulfillment/warehouse')->getDefaultWarehouseId();
        $this->setCurrWarehouseId($warehouseId);
        return $warehouseId;
    }

    public function setCurrWarehouseId($warehouseId) {
        return Mage::getSingleton('adminhtml/session')->setData('curr_warehouse_id', $warehouseId);
    }
    public function getProductLocation($warehouseId,$productId){
        $model= Mage::getModel('inventoryplus/warehouse_product')
                ->getCollection()
                ->addFieldToFilter('warehouse_id', $warehouseId)
                ->addFieldToFilter('product_id', $productId)
		->getFirstItem();
        $location=$model->getProductLocation();
        return $location;
    }

}
