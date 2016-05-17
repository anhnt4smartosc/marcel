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
class Magestore_Inventoryfulfillment_Adminhtml_Inf_FulfillmentController extends Magestore_Inventoryfulfillment_Controller_Fulfillment {

    /**
     * init layout and set active for current menu
     *
     * @return Magestore_Inventoryfulfillment_Adminhtml_FulfillmentController
     */
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('inventoryplus/fulfillment')
                ->_addBreadcrumb(
                        Mage::helper('adminhtml')->__('Fulfilment Management'), Mage::helper('adminhtml')->__('Fulfilment Management')
        );
        return $this;
    }

    /**
     * index action
     */
    public function indexAction() {
        $this->_initAction();
        $this->_title($this->__('Inventory Management'))
                ->_title($this->__('Fulfillment'));          
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('inventoryfulfillment/adminhtml_fulfillment_edit'))
                ->_addLeft($this->getLayout()->createBlock('inventoryfulfillment/adminhtml_fulfillment_edit_tabs'));

        $this->renderLayout();
    }

    /**
     * Show dashboard
     * 
     */
    public function dashboardAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Show picking step
     * 
     */
    public function orderpickingAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Show packing step
     * 
     */
    public function orderpackingAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Show ship step
     * 
     */
    public function ordershipAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Refresh warehouse dropdown in order verifying step
     * 
     */
    public function refreshwarehouselistAction() {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
        foreach ($order->getItemsCollection() as $item) {
            $needToShipQty = $this->helper('inventoryfulfillment')->getNeedToShipQty($_item);
            if ($needToShipQty) {
                $selectBox = $this->helper('inventoryfulfillment')->getWarehouseSelectBoxByOrderItem($_item);
            }
        }
    }

    /**
     * Check global stock via API
     * 
     */
    public function checkgstockAction() {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
        $requestItems = array();
        foreach ($order->getAllItems() as $item) {
            if ($item->getChildrenItems()) {
                foreach ($item->getChildrenItems() as $childItem) {
                    $requestQty = $this->_helper()->getNeedToShipQty($childItem);
                    if ($requestQty) {
                        $requestItems[$childItem->getId()]['qty'] = $requestQty;
                        $requestItems[$childItem->getId()]['barcode'] = $childItem->getProduct()->getSku();
                    }
                }
            } else {
                $requestQty = $this->_helper()->getNeedToShipQty($item);
                if ($requestQty) {
                    $requestItems[$item->getId()]['qty'] = $requestQty;
                    $requestItems[$item->getId()]['barcode'] = $item->getProduct()->getSku();
                }
            }
        }
        if (count($requestItems)) {
            foreach ($requestItems as $itemId => $requestData) {
                $remainingQty = $this->_getGlobalStock($requestData['barcode']);
                $requestItems[$itemId]['gstock'] = $remainingQty;
                $requestItems[$itemId]['is_available'] = $this->_checkAvailableGstock($requestData['qty'], $remainingQty);
                $requestItems[$itemId]['id'] = $itemId;
                if ($requestItems[$itemId]['is_available']) {
                    $messsage = $this->_helper()->__('In Stock (%s)', $requestItems[$itemId]['qty'] . '/' . $remainingQty);
                } else {
                    $messsage = $this->_helper()->__('Out Stock (%s)', $requestItems[$itemId]['qty'] . '/' . $remainingQty);
                }
                $requestItems[$itemId]['message'] = $messsage;
            }
        }
        return $this->getResponse()->setBody(
                        Mage::helper('core')->jsonEncode(array('items' => $requestItems))
        );
    }

    /**
     * Get global stock from API
     * 
     * @param string $barcode
     * @return int|float
     */
    protected function _getGlobalStock($barcode) {
        return rand(15, 40);
    }

    /**
     * Check if glock stock is available or not
     * 
     * @param int|float $requestQty
     * @param int|float $remaingQty
     * @return boolean
     */
    protected function _checkAvailableGstock($requestQty, $remaingQty) {
        $thresholdQty = Mage::getStoreConfig('inventoryplus/fulfillment/threshold_global_stock');
        if ($requestQty + $thresholdQty <= $remaingQty)
            return true;
        return false;
    }
    
    public function changewarehouseAction() {
        $warehouseId = $this->getRequest()->getParam('warehouse_id');
        Mage::helper('inventoryfulfillment')->setCurrWarehouseId($warehouseId);
    }

    /**
     * mass change status for item(s) action
     */
    public function massStatusAction() {
        $inventoryfulfillmentIds = $this->getRequest()->getParam('inventoryfulfillment');
        if (!is_array($inventoryfulfillmentIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($inventoryfulfillmentIds as $inventoryfulfillmentId) {
                    Mage::getSingleton('inventoryfulfillment/inventoryfulfillment')
                            ->load($inventoryfulfillmentId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($inventoryfulfillmentIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * export grid item to CSV type
     */
    public function exportCsvAction() {
        $fileName = 'inventoryfulfillment.csv';
        $content = $this->getLayout()
                ->createBlock('inventoryfulfillment/adminhtml_inventoryfulfillment_grid')
                ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export grid item to XML type
     */
    public function exportXmlAction() {
        $fileName = 'inventoryfulfillment.xml';
        $content = $this->getLayout()
                ->createBlock('inventoryfulfillment/adminhtml_inventoryfulfillment_grid')
                ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }
    

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('inventoryplus/fulfillment/overview');
    }

}
