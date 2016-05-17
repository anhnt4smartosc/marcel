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
class Magestore_Inventoryfulfillment_Block_Adminhtml_Fulfillment_Verifying extends Magestore_Inventoryfulfillment_Block_Adminhtml_Widget_Grid
{
    public function __construct() {
        parent::__construct();
        $this->setId('order_verifying_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->setTag('verifying');
        $this->setCurrGrid('order_verifying_grid');
    }
    
    /**
     * Add filters to collection
     * 
     * @return \Magestore_Inventoryfulfillment_Block_Adminhtml_Fulfillment_Verifying
     */
    public function _filterCollection()
    {
        $newStatuses = $this->_helper()->getNewOrderStatuses();
        $this->getCollection()->addFieldToFilter('main_table.status', array('in' => $newStatuses));
        $this->getCollection()->getSelect()->where('order.shipping_progress IN (?)', array(0,1));
        return $this;
    }    
    
    public function _prepareColumns() {
        $this->addColumn('real_order_id', array(
            'header'=> Mage::helper('sales')->__('Order #'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'increment_id',
            'filter_index' => 'main_table.increment_id'
        ));
        $this->addColumn('created_at', array(
            'header' => Mage::helper('inventoryplus')->__('Purchased On'),
            'type' => 'date',
            'width' => '60',
            'index' => 'created_at',
            'filter_index' => 'main_table.created_at'
        ));
        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('inventoryplus')->__('Ship to Name'),
            'width' => '110px',
            'index' => 'shipping_name'
        ));
	$this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
            'filter_index' => 'main_table.grand_total'
        ));
	$this->addColumn('customer_email', array(
            'header' => Mage::helper('inventoryplus')->__('Email'),
            'width' => '80px',
            'index' => 'shipping_email',
            'filter_index' => 'shipping.email'
        ));
        $this->addColumn('telephone', array(
            'header' => Mage::helper('inventoryplus')->__('Telephone'),
            'index' => 'shipping_telephone',
            'filter_index' => 'shipping.telephone'
        ));
	$this->addColumn('street_address', array(
            'header' => Mage::helper('inventoryplus')->__('Street Adress'),
            'index' => 'shipping_street',
            'filter_index' => 'shipping.street'
        ));
        $this->addColumn('shipping_city', array(
            'header' => Mage::helper('sales')->__('City/Provide'),
            'index' => 'shipping_city',
            'filter_index' => 'shipping.city'
        ));
	$this->addColumn('fulfillment_status', array(
            'header' => Mage::helper('sales')->__('Verified'),
            'index' => 'fulfillment_status',
            'type'  => 'options',
            'width' => '70px',
            'options' => array(0 => $this->__('Not Verify'), 
                               Magestore_Inventoryfulfillment_Model_Order_Fulfillmentstatus::STATUS_VERIFIED => $this->__('Verified')),
            'renderer' => 'inventoryfulfillment/adminhtml_fulfillment_verifying_renderer_status'
        ));
        $this->addColumn('renderer', array(
            'header' => Mage::helper('inventoryplus')->__('Action'),
            'width' => '90px',
            'renderer' => 'inventoryfulfillment/adminhtml_renderer_action',
            'filter' => false,
            'sortable' => false
        ));
        
	$this->sortColumnsByOrder();
        
        return $this;
    }    

    public function getRowDetails($row) {
        $url = $this->getUrl('adminhtml/inf_orderverifying/orderdetails');
        return array('url' => $url, 'order_id' => $row->getId());
    }   

}