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
 * Inventoryfulfillment Edit Block
 * 
 * @category     Magestore
 * @package     Magestore_Inventoryfulfillment
 * @author      Magestore Developer
 */
class Magestore_Inventoryfulfillment_Block_Adminhtml_Fulfillment_Packing_View extends Magestore_Inventoryfulfillment_Block_Adminhtml_Order_View
{   
    protected function _prepareChilds(){
        $this->setStep(Magestore_Inventoryfulfillment_Model_Step::FFM_STEP_PACKING);
        $printInvoiceUrl = '#';
        $printShipmentUrl = '#';
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
        if ($order->hasInvoices()) {
            $printInvoiceUrl = Mage::helper('adminhtml')->getUrl('adminhtml/inf_orderpacking/printinvoice', array('order_id' => $orderId));
            $printShipmentUrl = Mage::helper('adminhtml')->getUrl('adminhtml/inf_orderpacking/printshipment', array('order_id' => $orderId));
        }

        $printPackageBarcodeUrl = Mage::helper('adminhtml')->getUrl('adminhtml/inb_printbarcode/printbarcode');
        $packageCaptureUrl = Mage::helper('adminhtml')->getUrl('adminhtml/inf_orderpacking/capturepackage');
        $productsCaptureUrl = Mage::helper('adminhtml')->getUrl('adminhtml/inf_orderpacking/captureproducts');
        $addCommentUrl = Mage::helper('adminhtml')->getUrl('adminhtml/inf_orderpacking/addComment', array('order_id' => $orderId));
        $comment = $this->getCommentBlock();
        $barcode = $this->getBarcodeBlock();
//        $picture = $this->getPictureBlock();
        $emailNotification = $this->getNotificationEmailBlock();

        $submitOrderUrl = Mage::helper('adminhtml')->getUrl('adminhtml/inf_orderpacking/postorder', array('order_id' => $this->getOrder()->getId()));

        $this->addBottomLeftChild($barcode);
//        $this->addBottomLeftChild($picture);
        $this->addBottomLeftChild($comment);
        $this->addBelowButtonChild($emailNotification);

//        $this->_addButton('print_invoice', array(
//            'label'        => Mage::helper('adminhtml')->__('Print Invoice'),
//            'onclick'    => 'setLocation(\'' . $printInvoiceUrl . '\')',
//            'class'        => 'print_invoice',
//        ), 0, 1 , 'bottom_right');
//
//        $this->_addButton('print_shipping_address', array(
//            'label'        => Mage::helper('adminhtml')->__('Print Shipping Address'),
//            'onclick'    => 'setLocation(\'' . $printShipmentUrl . '\')',
//            'class'        => 'print_shipping_address',
//        ), 0, 10 , 'bottom_right');

//        $this->_addButton('print_package_barcode', array(
//            'label'        => Mage::helper('adminhtml')->__('Print Package Barcode'),
//            'onclick'    => 'barcodeCtrl.printPackageBarcode()',
//            'class'        => 'print_package_barcode',
//        ), 0, 20 , 'bottom_right');
//
//        $this->_addButton('product_pic', array(
//            'label'        => Mage::helper('adminhtml')->__('Take a Product Photo'),
//            'onclick'    => 'imageHandle.takeProductsPicture(\'' . $productsCaptureUrl . '\'' . ', ' . $orderId . ')',
//            'class'        => 'product_pic',
//        ), 0, 30 , 'bottom_right');
//
//        $this->_addButton('package_pic', array(
//            'label'        => Mage::helper('adminhtml')->__('Take a Package Photo'),
//            'onclick'    => 'imageHandle.takePackagePicture(\'' . $packageCaptureUrl . '\'' . ', ' . $orderId . ')',
//            'class'        => 'package_pic',
//        ), 0, 40 , 'bottom_right');

//        $this->addBottomRightInput($this->getOrderStatusBlock());

        $this->_addButton('submit', array(
            'label' => Mage::helper('adminhtml')->__('Submit Comment'),
            'onclick' => 'fulfillmentObj.submitVerifyOrder(this, \'' . $this->getOrder()->getId() . '\',\'' . $submitOrderUrl . '\')',
            'class' => 'submit',
        ), 0, 1, 'bottom_right');
        
        $this->_addButton('ship', array(
            'label'        => Mage::helper('adminhtml')->__('Commit Pack'),
            'onclick'    => 'fulfillmentObj.shipaction(this, \'' . $this->getOrderId() . '\')',
            'class'        => 'save',
        ), 0, 60 , 'bottom_right');
        
    }

    public function getPictureBlock() {
        return $this->getLayout()->createBlock('inventoryfulfillment/adminhtml_fulfillment_packing_picture');
    }

    public function getOrderStatusBlock() {
        return $this->getLayout()->createBlock('inventoryfulfillment/adminhtml_fulfillment_picking_orderstatus');
    }

    public function getItemsBlock() {
        return $this->getLayout()->createBlock('inventoryfulfillment/adminhtml_fulfillment_picking_items');
    }
    
}