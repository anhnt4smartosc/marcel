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
class Magestore_Inventoryfulfillment_Block_Adminhtml_Fulfillment_Packing extends Magestore_Inventoryfulfillment_Block_Adminhtml_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('order_packing_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->setTag('packing');
    }

    /**
     * Add filters to collection
     *
     * @return \Magestore_Inventoryfulfillment_Block_Adminhtml_Fulfillment_Packing
     */
    public function _filterCollection()
    {
        $warehouseId = $this->helper('inventoryfulfillment')->getCurrWarehouseId();
        $pickOrderIds = $this->helper('inventoryfulfillment')->getPickingOrderIds($warehouseId);
        $this->getCollection()->addFieldToFilter('entity_id', array('in' => $pickOrderIds));       
        //$this->getCollection()->addFieldToFilter('order.fulfillment_status', Magestore_Inventoryfulfillment_Model_Order_Fulfillmentstatus::STATUS_VERIFIED);
        return $this->getCollection();
    }
    
    protected function _prepareColumns() {
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
    
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('orders');

        $this->getMassactionBlock()->addItem('print', array(
            'label'        => Mage::helper('inventoryfulfillment')->__('Print Picking List'),
            'url'        => $this->getUrl('*/*/massPrintPickinglist'),
            'selected' => 'selected'
        ));
        return $this;
    }    

    /*
     *  Called from above renderer
     */
    public function getRowDetails($row)
    {
        $url = $this->getUrl('adminhtml/inf_orderpacking/orderdetails');
        return array('url' => $url, 'order_id' => $row->getId());
    }
}