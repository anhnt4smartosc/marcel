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
class Magestore_Inventoryfulfillment_Block_Adminhtml_Fulfillment_Shipping_Packagegrid extends Mage_Adminhtml_Block_Widget_Grid
{   
    public function __construct($attributes=array())
    {
        parent::__construct($attributes);
        $this->setId('package_shipping_grid_' . $this->getGroupName());
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->setTag($this->getGroupName());
    }

    protected function _getCollectionClass() {
        return 'sales/order_shipment_collection';
    }

    /**
     * Get all shipments (packages)
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection() {
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $resource = Mage::getSingleton('core/resource');
        $collection->getSelect()->join(
            array(
                'order' => $resource->getTableName('sales/order')),
                'main_table.order_id = order.entity_id',
                array(
                    'billing_address_id',
                    'fulfillment_status'
                )
        );
        $collection->getSelect()->joinLeft(
            array(
                'shipping' => $resource->getTableName('sales/order_address')),
                'order.shipping_address_id = shipping.entity_id',
                array(
                    'shipping_city' => 'city',
                    'shipping_street' => 'street',
                    'shipping_telephone' => 'telephone',
                    'shipping_postcode' => 'postcode',
                    'shipping_region' => 'region',
                    'shipping_email' => 'email'
                )
        );

        $collection->getSelect()->join(
            array(
                'order_grid' => $resource->getTableName('sales/order_grid')),
                'main_table.order_id = order_grid.entity_id',
                array(
                    'shipping_name'
                )
        );

        $this->setCollection($collection);

        $this->_filterCollection();

        return parent::_prepareCollection();
    }
    
    /**
     * Add filters to collection
     *
     * @return \Magestore_Inventoryfulfillment_Block_Adminhtml_Fulfillment_Picking
     */
    public function _filterCollection()
    {
        $waitingShipmentIds = $this->_helper()->getWaitingShipmentIds();
        $this->getCollection()->addFieldToFilter('main_table.entity_id', array('in' => $waitingShipmentIds));
        //$this->getCollection()->addFieldToFilter('order.fulfillment_status', Magestore_Inventoryfulfillment_Model_Order_Fulfillmentstatus::STATUS_PACKED);
        if($this->getGroupName()){
            $shippingMethods = Mage::helper('inventoryfulfillment')->getShippingMethodsFromCarrierCode($this->getGroupName());
            if(count($shippingMethods)){
                $this->getCollection()->addFieldToFilter('shipping_method', array('in' => $shippingMethods));
            }
        }
        return $this->getCollection();
    }    
    
    /**
     * Prepare columns
     * 
     * @return \Magestore_Inventoryfulfillment_Block_Adminhtml_Fulfillment_Shipping_Packagegrid
     */
    public function _prepareColumns() {
        $this->addColumn('increment_id', array(
            'header'=> Mage::helper('sales')->__('Shipment #'),
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
//	$this->addColumn('grand_total', array(
//            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
//            'index' => 'grand_total',
//            'type'  => 'currency',
//            'currency' => 'order_currency_code',
//            'filter_index' => 'main_table.grand_total'
//        ));
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
        
        /*
        $this->addColumn('fulfillment_status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'fulfillment_status',
            'type' => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('inventoryfulfillment/order_fulfillmentstatus')->getOptionArray(),
            'filter' => false,
            'sortable' => false            
        ));
         
         */
        
        $this->addColumn('renderer', array(
            'header' => '',
            'width' => '90px',
            'renderer' => 'inventoryfulfillment/adminhtml_renderer_action',
            'align' => 'center',
            'filter' => false,
            'sortable' => false
        ));

        $this->addColumn('select_order', array(
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'field_name' => 'shipment_' . $this->getGroupName(),
            'inline_css' => 'checkbox ship-order',
            'align' => 'center',
            'index' => 'entity_id',
            'editable' => true,   
            'filter' => false,
            'sortable' => false            
        ));        
        
	$this->sortColumnsByOrder();
        
        return $this;
    }       
    
    public function getGridUrl() {
        return $this->getUrl('*/*/packagegrid', array('group' => $this->getGroupName()));
    }    

    public function getRowDetails($row)
    {
        $url = $this->getUrl('adminhtml/inf_ordershipping/orderdetails');
        return array('url' => $url, 'order_id' => $row->getEntityId());
    }   
    
    /**
     * Get group name
     * 
     * @return string
     */
    public function getGroupName(){
        if(!$this->getData('group_name')){
            $group = $this->getRequest()->getParam('group');
            $this->setData('group_name', $group);
        }
        return $this->getData('group_name');
    }

    public function getRowClass($row) {
        return $this->getTag() . '-row-' . $row->getEntityId();
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