<?php

class SM_RMA_Block_Adminhtml_Request_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('rmaRmaRequestGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('rma/request')->getCollection();
        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns() {
        $this->addColumn('id', array(
            'header' => Mage::helper('rma')->__('RMA #'),
            'width' => '50px',
            'type' => 'number',
            'index' => 'id',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header' => Mage::helper('sales')->__('Request on (Store)'),
                'width' => '180px',
                'index' => 'store_id',
                'type' => 'store',
                'store_view' => true,
                'display_deleted' => true,
            ));
        }

        $this->addColumn('created_time', array(
            'header' => Mage::helper('rma')->__('Created Time'),
            'width' => '150px',
            'type' => 'datetime',
            'index' => 'created_time',
        ));
        $this->addColumn('customer_name', array(
            'header' => Mage::helper('rma')->__('Customer Name'),
            'index' => 'customer_name',
        ));
        $this->addColumn('customer_email', array(
            'header' => Mage::helper('rma')->__('Customer Email'),
            'width' => '180px',
            'index' => 'customer_email',
        ));
        $this->addColumn('order_increment_id', array(
            'header' => Mage::helper('rma')->__('Order #'),
            'width' => '180px',
            'index' => 'order_increment_id',
        ));
        $this->addColumn('status', array(
            'header' => Mage::helper('rma')->__('Status'),
            'width' => '150px',
            'index' => 'status',
            'type' => 'options',
            'renderer' => 'rma/adminhtml_request_grid_status',
            'options' => Mage::getModel('rma/request')->getAllStatuses(),
        ));
        $store = $this->_getStore();

        /* $this->addColumn('action',
          array(
          'header'    => Mage::helper('catalog')->__('Action'),
          'width'     => '100px',
          'type'      => 'action',
          'getter'     => 'getId',
          'actions'   => array(
          array(
          'caption' => Mage::helper('catalog')->__('Print Barcode'),
          'id' => "printbarcode",
          'url'     => array(
          'base'=>'rma/adminhtml_print/show',
          'params'=>array(
          'store'=>$this->getRequest()->getParam('store'),
          //'required' => $this->_getRequiredAttributesIds(),
          'popup'    => 1,
          //'product'  => $this->_getProduct()->getId()
          )
          ),
          'onclick'  => 'window.open(this.href, "_blank","toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, copyhistory=no, width=800, height=600, left=20, top=20"); return false;',
          'field'   => 'id',
          )
          ),
          'filter'    => false,
          'sortable'  => false,
          'index'     => 'stores',
          )); */

        return parent::_prepareColumns();
    }

    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    public function getRowUrl($row) {
        return $this->getUrl('adminhtml/rma_request/edit', array('id' => $row->getId()));
    }

}

