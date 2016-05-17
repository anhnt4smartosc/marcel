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
class Magestore_Inventoryfulfillment_Adminhtml_Inf_ShipprogressingController extends Magestore_Inventoryfulfillment_Controller_Fulfillment {

    /**
     * init layout and set active for current menu
     *
     * @return Magestore_Inventoryshipment_Adminhtml_InventoryshipmentController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('inventoryplus/fulfillment/follow_ship')
            ->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Inventory Management'),
                Mage::helper('adminhtml')->__('Shipped Orders')
            );
        return $this;
    }

    /**
     * index action
     */
    public function indexAction()
    {
        $this->_title($this->__('Inventory'))
            ->_title($this->__('Shipping Order'));
        $this->_initAction()
            ->renderLayout();
    }
    
    /**
     * Show shipping step
     * 
     */
    public function listAction() {
        $this->loadLayout();
        $this->_setActiveMenu('inventoryplus/fulfillment/follow_ship');
        $this->_title($this->__('Inventory Management'))
            ->_title($this->__('Shipped Orders'));        
        $this->getLayout()->getBlock('header_title')->setTitle($this->_helper()->__('Order Fulfilment - Shipped Orders'));
        $this->renderLayout();
    }         

    /**
     * Grid action
     */
    public function gridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('inventoryfulfillment/adminhtml_fulfillment_shipprogressing')->toHtml()
        );
    }
    
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('inventoryplus/fulfillment/follow_ship');
    }    
}