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
class Magestore_Inventoryfulfillment_Adminhtml_Inf_OrdershippingController extends Magestore_Inventoryfulfillment_Controller_Fulfillment {

    const STATUS_SHIPPED = 1;
    const STATUS_WAITING = 2;

    /**
     * Show shipping step
     * 
     */
    public function indexAction() {
        $this->loadLayout();
        /* check warehouse permission */
        if(!Mage::helper('inventoryfulfillment/warehouse')->hasPermission()) {
            $this->getLayout()->getBlock('root')->unsetChild('inventoryfulfillment_adminhtml_fulfillment_ship');
        }         
        $this->renderLayout();
    }
    
    /**
     * Show shipping step
     * 
     */
    public function listAction() {
        $this->loadLayout();
        $this->_setActiveMenu('inventoryplus/fulfillment/ship');
        $this->_title($this->__('Inventory Management'))
                ->_title($this->__('Ready-to-Ship Packages'));         
        $this->getLayout()->getBlock('header_title')->setTitle($this->_helper()->__('Fulfillment - Ready-to-Ship Packages'));
        /* check warehouse permission */
        if(!Mage::helper('inventoryfulfillment/warehouse')->hasPermission()) {
            $this->getLayout()->getBlock('content')->unsetChild('inventoryfulfillment_adminhtml_fulfillment_ship');
        }

        $this->renderLayout();
    }      

    /**
     * Show list of packages
     * 
     */
    public function packagegridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('inventoryfulfillment/adminhtml_fulfillment_shipping_packagegrid')->toHtml()
        );
    }

    /**
     * View package detail
     * 
     */
    public function orderdetailsAction() {
        $shipmentId = $this->getRequest()->getParam('order_id');
        $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
        Mage::register('current_shipment', $shipment);
        $orderId = $shipment->getOrderId();
        $this->getRequest()->setParam('order_id', $orderId);
        $this->_initOrder($orderId);
        $blockHtml = $this->getLayout()->createBlock('inventoryfulfillment/adminhtml_fulfillment_shipping_view', '', array('order_id' => $orderId))->toHtml();
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('return_html' => $blockHtml)));
    }

    /**
     * Process ship ỏder
     * 
     */
    public function shipAction() {
        $packageIds = explode(',', $this->getRequest()->getParam('package_ids'));
        try {
            // Each warehouse shipment is a package
            $warehouseShipments = Mage::getResourceModel('inventoryplus/warehouse_shipment_collection')
                    ->addFieldToFilter('shipment_id', array('in' => $packageIds));
            $orderId = $warehouseShipments->getFirstItem()->getOrderId();
            Mage::dispatchEvent('inventoryfulfillment_packages_ship_before', array('package_ids' => $packageIds,
                'packages' => $warehouseShipments
            ));
            /* process ship */
            /* TODO: Dante 2015/01/11 - Flaw in data consistency */
            if (count($warehouseShipments)) {
                foreach ($warehouseShipments as $warehouseShipment) {
                    $warehouseShipment->setFulfillmentStatus(self::STATUS_SHIPPED);
                    $warehouseShipment->save();
                    $fulfillmentPackage = Mage::getModel('inventoryfulfillment/package')->getCollection()
                        ->addFieldToFilter('order_id', array('eq' => $warehouseShipment->getOrderId()))
                        ->addFieldToFilter('item_id', array('eq' => $warehouseShipment->getItemId()))
                        ->getFirstItem();
                    if ($fulfillmentPackage->getQty() == $warehouseShipment->getQtyShipped()) {
                        $fulfillmentPackage->delete();
                    } else {
                        $fulfillmentPackage->setShippedQty($warehouseShipment->getQtyShipped());
                        $fulfillmentPackage->save();
                    }
                }
            }

            $order = Mage::getModel('sales/order')->load($orderId);
            $order->setFulfillmentStatus(Magestore_Inventoryfulfillment_Model_Order_Fulfillmentstatus::STATUS_SHIPPING);
            $order->save();

            Mage::dispatchEvent('inventoryfulfillment_packages_ship_after', array('package_ids' => $packageIds,
                'packages' => $warehouseShipments
            ));
            /* calculate total remaining packages */
            $packageGrid = $this->getLayout()
                    ->createBlock('inventoryfulfillment/adminhtml_fulfillment_shipping_packagegrid', 'package_grid' . $this->getRequest()->getParam('group_name'), array('group_name' => $this->getRequest()->getParam('group_name'))
            );
            $packageGrid->toHtml();

            $return = array('message' => $this->_helper()->__('These orders have been shipped!'),
                'total_packages' => $packageGrid->getCollection()->getSize()
            );
        } catch (Exception $ex) {
            $return = array('message' => $ex->getMessage(), 'error' => 1);
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($return));
    }

    /**
     * Print list of orders
     * 
     */
    public function printorderlistAction() {
        $packageIds = explode(',', $this->getRequest()->getParam('package_ids'));
        if (count($packageIds)) {
            foreach ($packageIds as $packageId) {
                $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                        ->addFieldToFilter('entity_id', array('eq' => $packageId))
                        ->load();
                if ($shipments->getSize()) {
                    if (!isset($pdf)) {
                        $pdf = Mage::getModel('sales/order_pdf_shipment')->getPdf($shipments);
                    } else {
                        $pages = Mage::getModel('sales/order_pdf_shipment')->getPdf($shipments);
                        $pdf->pages = array_merge($pdf->pages, $pages->pages);
                    }
                }
            }
        }
        if (isset($pdf)) {
            return $this->_prepareDownloadResponse(
                'shipments_' . Mage::getSingleton('core/date')->date('Y-m-d_H-i-s') . '.pdf', $pdf->render(), 'application/pdf'
            );
        }
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('inventoryplus/fulfillment/ship');
    }

    public function searchpackagebarcodeAction() {
        $result = array();
        $packageBarcode = $this->getRequest()->getParam('barcode_query');

        $shipment = Mage::getModel('sales/order_shipment')->getCollection()
            ->addFieldToFilter('increment_id', array('eq' => $packageBarcode))
            ->getFirstItem();
        if ($shipment) {
            $shipmentId = $shipment->getId();
            if ($shipmentId) {
                $result['shipment_id'] = $shipmentId;
                $result['status'] = 1;
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            } else {
                $block = $this->getLayout()->createBlock('adminhtml/template')
                    ->setTemplate('inventorybarcode/search/autocomplete.phtml');

                $this->getResponse()->setBody($block->toHtml());
            }
        } else {
            $block = $this->getLayout()->createBlock('adminhtml/template')
                ->setTemplate('inventorybarcode/search/autocomplete.phtml');

            $this->getResponse()->setBody($block->toHtml());
        }
    }

}
