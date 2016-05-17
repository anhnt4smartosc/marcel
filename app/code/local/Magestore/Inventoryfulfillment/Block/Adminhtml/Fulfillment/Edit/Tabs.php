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
 * Inventoryfulfillment Edit Tabs Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryfulfillment
 * @author      Magestore Developer
 */
class Magestore_Inventoryfulfillment_Block_Adminhtml_Fulfillment_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('fulfillment_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('inventoryfulfillment')->__('Item Information'));
    }
    
    /**
     * prepare before render block to html
     *
     * @return Magestore_Inventoryfulfillment_Block_Adminhtml_Fulfillment_Edit_Tabs
     */
    protected function _beforeToHtml()
    {
        /*
        $this->addTab('dashboard_section', array(
            'label' => Mage::helper('inventoryfulfillment')->__('Dashboard'),
            'title' => Mage::helper('inventoryfulfillment')->__('Dashboard'),
            'url' => $this->getUrl('adminhtml/inf_dashboard/index', array(
                '_current' => true,
                'id' => $this->getRequest()->getParam('id'),
                'store' => $this->getRequest()->getParam('store')
            )),
            'class' => 'ajax',
        ));
        */
        
        $this->addTab('verifying_section', array(
            'label' => Mage::helper('inventoryfulfillment')->__('Verify Order & Stock Availability'),
            'title' => Mage::helper('inventoryfulfillment')->__('Verify Order & Stock Availability'),
            'url' => $this->getUrl('adminhtml/inf_orderverifying/index', array(
                '_current' => true
            )),
            'class' => 'ajax',
        )); 
        
        /*
        $this->addTab('picking_section', array(
            'label' => Mage::helper('inventoryfulfillment')->__('Order Picking'),
            'title' => Mage::helper('inventoryfulfillment')->__('Order Picking'),
            'url' => $this->getUrl('adminhtml/inf_orderpicking/index', array(
                '_current' => true,
                'id' => $this->getRequest()->getParam('id'),
                'store' => $this->getRequest()->getParam('store')
            )),
            'class' => 'ajax',
        ));   
        */
        
        $this->addTab('packing_section', array(
            'label' => Mage::helper('inventoryfulfillment')->__('Picking Items & Packing Slips'),
            'title' => Mage::helper('inventoryfulfillment')->__('Picking Items & Packing Slips'),
            'url' => $this->getUrl('adminhtml/inf_orderpacking/index', array(
                '_current' => true,
                'id' => $this->getRequest()->getParam('id'),
                'store' => $this->getRequest()->getParam('store')
            )),
            'class' => 'ajax',
        ));

        $this->addTab('ship_section', array(
            'label' => Mage::helper('inventoryfulfillment')->__('Ready-To-Ship Packages'),
            'title' => Mage::helper('inventoryfulfillment')->__('Ready-To-Ship Packages'),
            'url' => $this->getUrl('adminhtml/inf_ordershipping/index', array(
                '_current' => true,
                'id' => $this->getRequest()->getParam('id'),
                'store' => $this->getRequest()->getParam('store')
            )),
            'class' => 'ajax',
        ));

        $this->addTab('shipping_section', array(
            'label' => Mage::helper('inventoryfulfillment')->__('Shipped Orders'),
            'title' => Mage::helper('inventoryfulfillment')->__('Shipped Orders'),
            'url' => $this->getUrl('adminhtml/inf_shipprogressing/index', array(
                '_current' => true,
                'id' => $this->getRequest()->getParam('id'),
                'store' => $this->getRequest()->getParam('store')
            )),
            'class' => 'ajax',
        ));

        return parent::_beforeToHtml();
    }
}