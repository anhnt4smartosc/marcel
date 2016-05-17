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
 * Inventoryfulfillment Grid Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryfulfillment
 * @author      Magestore Developer
 */
class Magestore_Inventoryfulfillment_Block_Adminhtml_Widget_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct($attributes=array()) {
        parent::__construct($attributes);
        $this->setId('fulfillment_widget_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->setTag('grid');
    }

    protected function _getCollectionClass() {
        return 'sales/order_grid_collection';
    }

    protected function _prepareCollection() {
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $resource = Mage::getSingleton('core/resource');
        $collection->getSelect()->join(
                array('order' => $resource->getTableName('sales/order')), 'main_table.entity_id = order.entity_id', array('billing_address_id', 'fulfillment_status')
        );
        $collection->getSelect()->joinLeft(
                array('shipping' => $resource->getTableName('sales/order_address')), 'order.shipping_address_id = shipping.entity_id', array('shipping_city' => 'city',
            'shipping_street' => 'street',
            'shipping_telephone' => 'telephone',
            'shipping_postcode' => 'postcode',
            'shipping_region' => 'region', 'shipping_email' => 'email')
        );

        $this->setCollection($collection);

        $this->_filterCollection();

        return parent::_prepareCollection();
    }

    protected function _filterCollection() {
        return $this;
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
        $this->addColumn('fulfillment_status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'fulfillment_status',
            'type' => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('inventoryfulfillment/order_fulfillmentstatus')->getOptionArray(),
        ));
        $this->addColumn('renderer', array(
            'header' => Mage::helper('inventoryplus')->__('Action'),
            'width' => '90px',
            'renderer' => 'inventoryfulfillment/adminhtml_renderer_action',
            'filter' => false,
            'sortable' => false
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid');
    }

    public function getRowUrl($row) {
        return false;
    }

    /*
     *  Called from above renderer
     */

    public function getRowDetails($row) {
        $url = $this->getUrl('*/*/orderdetails');
        return array('url' => $url, 'order_id' => $row->getId());
    }

    public function getRowClass($row) {
        return $this->getTag() . '-row-' . $row->getId();
    }

    protected function _helper() {
        return Mage::helper('inventoryfulfillment');
    }
   
    public function setCurrGrid($gridId){
        if(!Mage::registry(Magestore_Inventoryfulfillment_Helper_Data::GRID_ID)){
            Mage::register(Magestore_Inventoryfulfillment_Helper_Data::GRID_ID, $gridId);
        }
    }    

}
