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
class Magestore_Inventoryfulfillment_Adminhtml_Inf_OrderpackingController extends Magestore_Inventoryfulfillment_Controller_Fulfillment {

    const STATUS_SUCCESS = 1;
    const STATUS_ERROR = 0;
    
    protected $_packages;

    public function indexAction() {
        $this->loadLayout();
        /* check warehouse permission */
        if(!Mage::helper('inventoryfulfillment/warehouse')->hasPermission()) {
            $this->getLayout()->getBlock('root')->unsetChild('inventory_adminhtml_fulfilment_packing_grid');
        }        
        
        $this->renderLayout();
    }

    /**
     * Show packing step
     * 
     */
    public function listAction() {
        new Mage_Core_Block_Text_List();
        $this->loadLayout();
        $this->_setActiveMenu('inventoryplus/fulfillment/pack');
        $this->_title($this->__('Inventory Management'))
                ->_title($this->__('Picking items & Packing Slips'));        
        $this->getLayout()->getBlock('header_title')->setTitle($this->_helper()->__('Order Fulfillment - Picking Items & Packing Slips'));
        /* check warehouse permission */
        if(!Mage::helper('inventoryfulfillment/warehouse')->hasPermission()) {
            $this->getLayout()->getBlock('content')->unsetChild('inventory_adminhtml_fulfilment_packing_grid');
        }
        $this->renderLayout();
    }

    public function gridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('inventoryfulfillment/adminhtml_fulfillment_packing')->toHtml()
        );
    }

    /**
     * View order detail
     */
    public function orderdetailsAction() {
        $orderId = $this->getRequest()->getParam('order_id');
        $this->_initOrder($orderId);
        $blockHtml = $this->getLayout()->createBlock('inventoryfulfillment/adminhtml_fulfillment_packing_view', '', array('order_id' => $orderId))->toHtml();
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('return_html' => $blockHtml)));
    }


    public function capturepackageAction() {
        $orderId = $this->getRequest()->getParam('orderId');
        $order = Mage::getModel('sales/order')
                ->getCollection()
                ->addFieldToFilter('entity_id', $orderId)
                ->getFirstItem();
        $filePath = '';
        $inputValue = $this->getRequest()->getParam('dataUri');
        try {
            $imgData = file_get_contents($inputValue);
            $filePath = Mage::getBaseDir('media') . DS . 'inventoryfulfillment' . DS . 'order' . $orderId . DS;
            if (!is_dir($filePath)) {
                mkdir($filePath, 0777, true);
            }
            $filePath .= 'package.png';
            $file = fopen($filePath, 'w');
            fwrite($file, $imgData);
            fclose($file);

            $packageWebPath = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'inventoryfulfillment/order' . $orderId . '/package.png';
            $order->setData('package_web_path', $packageWebPath);
            $order->setData('package_physical_path', $filePath);
            $order->save();

            $pictureBlockHtml = $this->getLayout()->createBlock('inventoryfulfillment/adminhtml_fulfillment_packing_picture')->toHtml();

            $this->getResponse()->setBody($pictureBlockHtml);
        } catch (Exception $e) {
            if (is_file($filePath)) {
                unlink($filePath);
            }
        }
    }

    public function captureproductsAction() {
        $orderId = $this->getRequest()->getParam('orderId');
        $order = Mage::getModel('sales/order')
                ->getCollection()
                ->addFieldToFilter('entity_id', $orderId)
                ->getFirstItem();
        $oldProductsWebPath = is_null($order->getData('products_web_path')) ? '' : $order->getData('products_web_path');
        $oldProductsPhysicalPath = is_null($order->getData('products_physical_path')) ? '' : $order->getData('products_physical_path');
        $oldProductNameIndex = $order->getData('product_name_index');
        $oldNumProductsPic = $order->getData('num_products_pic');
        $filePath = '';
        $inputValue = $this->getRequest()->getParam('dataUri');
        try {
            $imgData = file_get_contents($inputValue);
            $filePath = Mage::getBaseDir('media') . DS . 'inventoryfulfillment' . DS . 'order' . $orderId . DS;
            if (!is_dir($filePath)) {
                mkdir($filePath, 0777, true);
            }
            $filePath .= 'product' . $oldProductNameIndex . '.png';
            $file = fopen($filePath, 'w');
            fwrite($file, $imgData);
            fclose($file);

            $newProductsWebPath = $oldProductsWebPath . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'inventoryfulfillment/order' . $orderId . '/product' . $oldProductNameIndex . '.png;';
            $newProductsPhysicalPath = $oldProductsPhysicalPath . $filePath . ';';
            $order->setData('products_web_path', $newProductsWebPath);
            $order->setData('products_physical_path', $newProductsPhysicalPath);
            $order->setData('num_products_pic', $oldNumProductsPic + 1);
            $newProductNameIndex = $oldProductNameIndex + 1;
            $order->setData('product_name_index', $newProductNameIndex);
            $order->save();

            $pictureBlockHtml = $this->getLayout()->createBlock('inventoryfulfillment/adminhtml_fulfillment_packing_picture')->toHtml();

            $this->getResponse()->setBody($pictureBlockHtml);
        } catch (Exception $e) {
            if (is_file($filePath)) {
                unlink($filePath);
            }
        }
    }

    /**
     * Delete package picture
     */
    public function deletepackagepictureAction() {
        // Get param from request
        $orderId = $this->getRequest()->getParam('orderId');

        // Get order
        $order = Mage::getModel('sales/order')
                ->getCollection()
                ->addFieldToFilter('entity_id', $orderId)
                ->getFirstItem();
        $webPath = $order->getData('package_web_path');
        $physicalPath = $order->getData('package_physical_path');
        try {
            // Delete web path in database
            if ($webPath) {
                $order->setData('package_web_path', null);
            }

            // Delete physical path in database
            if ($physicalPath) {
                $order->setData('package_physical_path', null);
            }
            $order->save();
            // Delete real file
            if (is_file($physicalPath)) {
                unlink($physicalPath);
            }
            $pictureBlockHtml = $this->getLayout()->createBlock('inventoryfulfillment/adminhtml_fulfillment_packing_picture')->toHtml();

            $this->getResponse()->setBody($pictureBlockHtml);
        } catch (Exception $e) {
            // If action failed, restore the data in database
            $order->setData('package_web_path', $webPath);
            $order->setData('package_physical_path', $physicalPath);
            $order->save();
        }
    }

    /**
     * Delete product's picture
     */
    public function deleteproductspictureAction() {
        // Get parameters from request
        $orderId = $this->getRequest()->getParam('orderId');
        $imageWebPath = $this->getRequest()->getParam('imageWebPath');

        // Get name of file to be deleted
        $fileName = substr($imageWebPath, strrpos($imageWebPath, '/') + 1);

        // Get full physical path of file to be deleted
        $imagePhysicalPath = Mage::getBaseDir('media') . DS . 'inventoryfulfillment' . DS . 'order' . $orderId . DS . $fileName;

        // Get order
        $order = Mage::getModel('sales/order')
                ->getCollection()
                ->addFieldToFilter('entity_id', $orderId)
                ->getFirstItem();

        // Build new web path to save to database, delete requested file
        $webPath = $order->getData('products_web_path');
        $webPathArray = explode(';', $webPath);
        $i = 0;
        foreach ($webPathArray as $item) {
            if (strcmp($imageWebPath, $item) == 0) {
                array_splice($webPathArray, $i, 1);
                break;
            }
            $i++;
        }
        $newWebPath = implode(';', $webPathArray);

        // Build new physical path to save to database, delete requested file
        $physicalPath = $order->getData('products_physical_path');
        $physicalPathArray = explode(';', $physicalPath);
        $j = 0;
        foreach ($physicalPathArray as $item) {
            if (strcmp($imagePhysicalPath, $item) == 0) {
                array_splice($physicalPathArray, $j, 1);
                break;
            }
            $j++;
        }
        $newPhysicalPath = implode(';', $physicalPathArray);

        // Update number of pictures
        $numProductsPic = $order->getData('num_products_pic');
        $newNumProductsPic = $numProductsPic - 1;
        try {
            $order->setData('products_web_path', $newWebPath);
            $order->setData('products_physical_path', $newPhysicalPath);
            $order->setData('num_products_pic', $newNumProductsPic);
            $order->save();

            // Delete real file
            if (is_file($imagePhysicalPath)) {
                unlink($imagePhysicalPath);
            }

            $pictureBlockHtml = $this->getLayout()->createBlock('inventoryfulfillment/adminhtml_fulfillment_packing_picture')->toHtml();

            $this->getResponse()->setBody($pictureBlockHtml);
        } catch (Exception $e) {
            // If action failed, restore the data in database
            $order->setData('package_web_path', $webPath);
            $order->setData('package_physical_path', $physicalPath);
            $order->save();
        }
    }

    /**
     * Print invoices
     */
    public function printinvoiceAction() {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
        // Get all invoice ids
        if ($order->hasInvoices()) {
            $invoiceIds = array();
            foreach ($order->getInvoiceCollection() as $inv) {
                $invoiceIds[] = $inv->getId();
            }
        }
        if (count($invoiceIds) > 0) {
            foreach ($invoiceIds as $invoiceId) {
                if ($invoice = Mage::getModel('sales/order_invoice')->load($invoiceId)) {
                    $pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf(array($invoice));
                    $this->_prepareDownloadResponse('invoice' . Mage::getSingleton('core/date')->date('Y-m-d_H-i-s') .
                            '.pdf', $pdf->render(), 'application/pdf');
                }
            }
        }
    }

    /**
     * Print shipments
     */
    public function printshipmentAction() {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
        $shipmentIds = array();
        // Get all shipment ids
        foreach ($order->getShipmentsCollection() as $shipment) {
            $shipmentIds[] = $shipment->getId();
        }
        if (count($shipmentIds) > 0) {
            foreach ($shipmentIds as $shipmentId) {
                if ($shipment = Mage::getModel('sales/order_shipment')->load($shipmentId)) {
                    $pdf = Mage::getModel('sales/order_pdf_shipment')->getPdf(array($shipment));
                    $this->_prepareDownloadResponse('packingslip' . Mage::getSingleton('core/date')->date('Y-m-d_H-i-s') . '.pdf', $pdf->render(), 'application/pdf');
                }
            }
        }
    }

    public function shipAction() {
        $orderId = $this->getRequest()->getParam('order_id');
        $isNotifyEmail = $this->getRequest()->getParam('is_notify_email');
        $dataBefore = new Varien_Object();
        $dataAfter = new Varien_Object();
        try {
            $order = $this->_initOrder($orderId);

            Mage::dispatchEvent('inventoryfulfillment_order_ready_before', array(
                'order' => $order,
                'data_before' => $dataBefore,
                    'is_notify_email' => $isNotifyEmail
                    )
            );

            //$order->setFulfillmentStatus(Magestore_Inventoryfulfillment_Model_Order_Fulfillmentstatus::STATUS_PACKED);

            /* transfer stocks from global to primary warehouse */
            //$this->_helper()->prepareStockToShip($order);

            /* create shipment */
            $shipmentParams = $this->_prepareShipmentParams($order);
            $this->getRequest()->setParams($shipmentParams);
            $shipment = $this->_initShipment($order);
            $shipment->register();
            $this->_saveShipment($shipment);
            $order->save();
            $this->_updatePackageStatus();
            $this->_updateWarehouseShipment($shipment);

            Mage::dispatchEvent('inventoryfulfillment_order_ready_after', array(
                'order' => $order,
                'data_after' => $dataAfter,
                    'is_notify_email' => $isNotifyEmail
                    )
            );

            $return = array('message' => $this->_helper()->__('The Packing Slip has been submitted successfully!'));
        } catch (Exception $ex) {
            $return = array('message' => $ex->getMessage(), 'error' => 1);
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($return));
    }

    /**
     * Return item_id when checking barcode
     */
    public function markitemcheckedAction() {
        $result = array();
        $barcodeId = $this->getRequest()->getParam('barcode_id');
        $orderId = $this->getRequest()->getParam('order_id');
        if(Mage::helper('inventorybarcode')->isMultipleBarcode()) {
            /* multiple barcode */
            $barcode = Mage::getModel('inventorybarcode/barcode')->load($barcodeId);
            $productId = $barcode->getProductEntityId();
        } else {
            /* single barcode */
            $productId = $barcodeId;
        }
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

    /**
     * Add order comment action
     */
    public function addCommentAction() {
        $orderId = $this->getRequest()->getParam('order_id');
        if ($order = $this->_initOrder($orderId)) {
            try {
                $response = false;
                $data = $this->getRequest()->getPost('history');
                $notify = isset($data['is_customer_notified']) ? $data['is_customer_notified'] : false;
                $visible = isset($data['is_visible_on_front']) ? $data['is_visible_on_front'] : false;

                $order->addStatusHistoryComment($data['comment'], $data['status'])
                        ->setIsVisibleOnFront($visible)
                        ->setIsCustomerNotified($notify);

                $comment = trim(strip_tags($data['comment']));

                $order->save();
                $order->sendOrderUpdateEmail($notify, $comment);

                $this->loadLayout('empty');
                $this->renderLayout();
            } catch (Mage_Core_Exception $e) {
                $response = array(
                    'error' => true,
                    'message' => $e->getMessage(),
                );
            } catch (Exception $e) {
                $response = array(
                    'error' => true,
                    'message' => $this->__('Cannot add order history.')
                );
            }
            if (is_array($response)) {
                $response = Mage::helper('core')->jsonEncode($response);
                $this->getResponse()->setBody($response);
            }
        }
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
        $savedQtys = $this->_getItemQtys($order);
        $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($savedQtys);

        Mage::register('current_shipment', $shipment);
        return $shipment;
    }

    /**
     * Initialize shipment items QTY
     * 
     * @param Mage_Sales_Model_Order $order
     */
    protected function _getItemQtys($order) {
        $data = $this->getRequest()->getParam('shipment');
        $shipItems = array();
        if (isset($data['items'])) {
            $qtys = $data['items'];
        } else {
            $qtys = array();
        }
        
        if(count($qtys)){
            foreach($qtys as $itemId=>$qty) {
                $item = $order->getItemById($itemId);
                if($parentItem = $item->getParentItem()){
                    $shipItems[$parentItem->getId()] = $qty;
                } else {
                    $shipItems[$item->getId()] = $qty;
                }
            }
        }
        return $shipItems;
    }

    /**
     * Save shipment and order in one transaction
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return Mage_Adminhtml_Sales_Order_ShipmentController
     */
    protected function _saveShipment($shipment) {
        $shipment->getOrder()->setIsInProcess(true);
        $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($shipment)
                ->addObject($shipment->getOrder())
                ->save();

        return $this;
    }

    /**
     * 
     * @param Mage_Sales_Model_Order $order
     * @return aray
     */
    protected function _prepareShipmentParams($order) {
        $params = array('order_id' => $order->getId());
        $params['shipment'] = array();
        $warehouseId = $this->_helper()->getCurrWarehouseId();
        $packages = Mage::getModel('inventoryfulfillment/package')
                        ->getPackages($order->getId(), Magestore_Inventoryfulfillment_Model_Package::STATUS_PICKING, $warehouseId);
        if(count($packages)){
            foreach($packages as $package) {
               $params['shipment']['items'][$package->getItemId()] = $package->getNeedToPickQty(); 
               $params['warehouse-shipment']['items'][$package->getItemId()] = $warehouseId;
               /* update qty in package */
               $qty_before = $package->getPickedQty();
               $package->setPickedQty($package->getQty());
               $qty_after = $package->getPickedQty();
               $package->setQtyChange($qty_before - $qty_after);
            }
        }
        $this->_packages = $packages;
        return $params;
    }
    
    /**
     * Update packages
     * 
     */
    protected function _updatePackageStatus(){
        if(count($this->_packages)){
            foreach($this->_packages as $package){
                $qty_change = $package->getQtyChange();
                $package->save();
                Mage::helper('inventoryfulfillment/warehouse')->updatePickQty($package->getProductId(), $package->getWarehouseId(), $qty_change);
            }
        }       
        
    }
    
    /**
     * Update status of warehouse shipment
     * 
     * @param Mage_Sales_Model_Order_Shipment $shipment
     */
    protected function _updateWarehouseShipment($shipment) {
        $warehouseShipments = Mage::getModel('inventoryplus/warehouse_shipment')
                                        ->getCollection()
                                        ->addFieldToFilter('shipment_id', $shipment->getId());
        if(count($warehouseShipments)){
            foreach($warehouseShipments as $warehouseShipment) {
                /* change status of warehouse shipment to waiting */
                $warehouseShipment->setFulfillmentStatus(2)
                                    ->save();
            }
        }
    }


    public function massPrintPickinglistAction() {
        $this->loadLayout();
        $this->_title($this->__('Inventory Management'))
                ->_title($this->__('Picking List'));        
        $this->renderLayout();
    }
    
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('inventoryplus/fulfillment/pack');
    }    
    
    
    /**
     * Search barcode action
     * 
     */
    public function searchbarcodeAction() {
        $items = array();
        $start = $this->getRequest()->getParam('start', 1);
        $limit = $this->getRequest()->getParam('limit', 10);
        $query = $this->getRequest()->getParam('barcode_query', '');
        if(Mage::helper('inventorybarcode')->isMultipleBarcode()) {
            /* Multiple barcode mode */
            $barcode = Mage::getModel('inventorybarcode/barcode')->load($query, 'barcode');
            if ($barcode->getId()) {
                $result = array();
                $result['barcode_id'] = $barcode->getId();
                $result['show'] = true;
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }
        } else {
            /* barcode is a product attribute */
            $barcodeAtt = Mage::helper('inventorybarcode')->getBarcodeAttribute();
            $product = Mage::getModel('catalog/product')->getCollection()
                                ->addAttributeToFilter($barcodeAtt, $query)
                                ->getFirstItem();
            if ($product->getId()) {
                $result = array();
                $result['barcode_id'] = $product->getId();
                $result['barcode'] = $product->getData($barcodeAtt);
                $result['show'] = true;
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));   
            }
        }
    }

    /**
     * Submit order action
     */
    public function postorderAction() {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        Mage::setIsDeveloperMode(true);
        $orderId = $this->getRequest()->getParam('order_id');
        $comment = $this->getRequest()->getParam('comment');
        $selectedMethod = $this->getRequest()->getParam('selectedmethod');
        if ($order = $this->_initOrder($orderId)) {
            try {
                $response = array();
                $data = array('comment' => $comment, 'status' => $order->getStatus());
                $notify = isset($data['is_customer_notified']) ? $data['is_customer_notified'] : false;
                $visible = isset($data['is_visible_on_front']) ? $data['is_visible_on_front'] : false;
                if ($data['comment']) {
                    $order->addStatusHistoryComment($data['comment'], $data['status'])
                        ->setIsVisibleOnFront($visible)
                        ->setIsCustomerNotified($notify);
                }
                if ($order->getShippingMethod() != $selectedMethod) {
                    $order->setShippingMethod($selectedMethod);
                    $order->setShippingDescription($this->_helper()->getShippingDescription($selectedMethod));
                }
                $order->save();

                $comment = trim(strip_tags($data['comment']));
                $order->sendOrderUpdateEmail($notify, $comment);

                $this->loadLayout('empty');
                $response = array('orderHistory' => $this->getLayout()->getBlock('order_comment_history')->toHtml(),
                    'message' => $this->_helper()->__('Order has been updated successfully!'));
            } catch (Mage_Core_Exception $e) {
                $response = array(
                    'error' => true,
                    'message' => $e->getMessage(),
                );
            } catch (Exception $e) {
                $response = array(
                    'error' => true,
                    'message' => $this->_helper()->__('Cannot add order history.')
                );
            }
            if (is_array($response)) {
                $response = Mage::helper('core')->jsonEncode($response);
                $this->getResponse()->setBody($response);
            }
        }
    }
}
