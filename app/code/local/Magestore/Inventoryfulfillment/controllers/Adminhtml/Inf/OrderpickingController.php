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
 * Inventoryfulfillment Adminhtml Controller
 *
 * @category    Magestore
 * @package     Magestore_Inventoryfulfillment
 * @author      Magestore Developer
 */
class Magestore_Inventoryfulfillment_Adminhtml_Inf_OrderpickingController extends Magestore_Inventoryfulfillment_Controller_Fulfillment {

    /**
     * Show picking step
     *
     */
    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }
    
    /**
     * Show picking step
     * 
     */
    public function listAction() {
        $this->loadLayout();
        $this->_setActiveMenu('inventoryplus/fulfillment/pick');
        $this->getLayout()->getBlock('header_title')->setTitle($this->_helper()->__('Fulfillment - Order Picking'));
        $this->renderLayout();
    }        

    /**
     * Grid action
     */
    public function gridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('inventoryfulfillment/adminhtml_fulfillment_picking')->toHtml()
        );
    }

    /**
     * View order detail
     */
    public function orderdetailsAction() {
        $orderId = $this->getRequest()->getParam('order_id');
        $this->_initOrder($orderId);
        $blockHtml = $this->getLayout()->createBlock('inventoryfulfillment/adminhtml_fulfillment_picking_view', '', array('order_id' => $orderId))->toHtml();
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('return_html' => $blockHtml)));
    }

    /**
     * Process picking order
     * 
     */
    public function packAction() {
        $orderId = $this->getRequest()->getParam('order_id');
        $isNotifyEmail = $this->getRequest()->getParam('is_notify_email');
        $selectedwarehouses = $this->getRequest()->getParam('selectedwarehouses');
        parse_str($selectedwarehouses, $selectedwarehouses);

        try {
            $order = $this->_initOrder($orderId);
            $warehouse = Mage::helper('inventoryplus/warehouse')->getPrimaryWarehouse();
            Mage::dispatchEvent('inventoryfulfillment_order_pack_before', array('order' => $order, 'is_notify_email' => $isNotifyEmail));
            
            /* update fulfillment status to order */
            $order->setFulfillmentStatus(Magestore_Inventoryfulfillment_Model_Order_Fulfillmentstatus::STATUS_PICKED);
            $order->save();

            /* save warehouse_id to items */
            foreach ($order->getAllItems() as $item) {
                $item->setAssignWarehouseId($warehouse->getId())
                        ->save();
            }
            
            Mage::dispatchEvent('inventoryfulfillment_order_pack_after', array('order' => $order, 'is_notify_email' => $isNotifyEmail));

            $return = array('message' => $this->_helper()->__('The order have been picked successful!'));
        } catch (Exception $ex) {
            $order->setFulfillmentStatus(Magestore_Inventoryfulfillment_Model_Order_Fulfillmentstatus::STATUS_VERIFIED);
            $order->save();            
            $return = array('message' => $ex->getMessage(), 'error' => 1);
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($return));
    }
    
    /**
     * 
     * @param Mage_Sales_Model_Order $order
     * @return aray
     */
    protected function _prepareShipmentParams($order) {
        $params = array('order_id' => $order->getId());
        $params['shipment'] = array();
        foreach ($order->getAllVisibleItems() as $item) {
            $params['shipment']['items'][$item->getId()] = $this->_helper()->getNeedToShipQty($item);
            if (!$warehouseId = $item->getAssignWarehouseId()) {
                if (count($item->getChildrenItems())) {
                    foreach ($item->getChildrenItems() as $citem) {
                        if ($warehouseId = $citem->getAssignWarehouseId())
                            break;
                    }
                }
            }
            $params['warehouse-shipment']['items'][$item->getId()] = $warehouseId;
        }
        return $params;
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('inventoryplus/fulfillment/pick');
    }

    /**
     * Initialize shipment model instance
     *
     * @return Mage_Sales_Model_Order_Shipment|bool
     */
    protected function _initShipment($order) {
        /**
         * Check shipment is available to create separate from invoice
         */
        if ($order->getForcedDoShipmentWithInvoice()) {
            throw new Exception($this->__('Cannot do shipment for the order separately from invoice.'));
        }
        /**
         * Check shipment create availability
         */
        if (!$order->canShip()) {
            throw new Exception($this->__('Cannot do shipment for the order.'));
        }
        $savedQtys = $this->_getItemQtys();
        $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($savedQtys);

        Mage::register('current_shipment', $shipment);
        return $shipment;
    }
    
    /**
     * Initialize shipment items QTY
     */
    protected function _getItemQtys()
    {
        $data = $this->getRequest()->getParam('shipment');
        if (isset($data['items'])) {
            $qtys = $data['items'];
        } else {
            $qtys = array();
        }
        return $qtys;
    }    
    
    /**
     * Save shipment and order in one transaction
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return Mage_Adminhtml_Sales_Order_ShipmentController
     */
    protected function _saveShipment($shipment)
    {
        $shipment->getOrder()->setIsInProcess(true);
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($shipment)
            ->addObject($shipment->getOrder())
            ->save();

        return $this;
    }    
    
    /**
     * Return item_id when checking barcode
     */
    public function markitemcheckedAction() {
        $result = array();
        $barcodeId = $this->getRequest()->getParam('barcode_id');
        $orderId = $this->getRequest()->getParam('order_id');
        $barcode = Mage::getModel('inventorybarcode/barcode')->load($barcodeId);
        $productId = $barcode->getProductEntityId();
        
        $package = Mage::getModel('inventoryfulfillment/package')
                        ->getCollection()
                        ->addFieldToFilter('order_id', $orderId)
                        ->addFieldToFilter('product_id', $productId)
                        ->addFieldToFilter('warehouse_id', $this->_helper()->getCurrWarehouseId())
                        ->getFirstItem();            

        if ($package->getId()) {
            $result['package_id'] = $package->getId();
            $result['status'] = 1;
        } else {
            $result['status'] = 0;
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }    

}
