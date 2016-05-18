<?php

class SM_RMA_Block_Adminhtml_Request_Edit_Tab_Exchangeitems extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('rmaRmaRequestGridExchange');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $request_id = $this->getRequest()->getParam('id');
        $collection = Mage::getModel('rma/exchangeitem')->getCollection()
                ->addFieldToFilter('rma_id', $request_id)
        ;
        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns() {
        $this->addColumn('x_id', array(
            'header' => Mage::helper('rma')->__('Item #'),
            'width' => '20px',
            'type' => 'number',
            'index' => 'id',
        ));

        $this->addColumn('xname', array(
            'header' => Mage::helper('rma')->__('Product Name'),
            'filter_index' => false,
            'width' => '250px',
            'renderer' => 'rma/adminhtml_request_edit_tab_exchangeitem_name',
        ));
        
        $this->addColumn('xitem_id', array(
            'header' => Mage::helper('rma')->__('Product #'),
            'width' => '20px',
            'type' => 'number',
            'index' => 'item_id',
        ));

        $this->addColumn('xitem_price', array(
            'header' => Mage::helper('rma')->__('Product price'),
            'width' => '80px',
            'filter_index' => false,
            'renderer' => 'rma/adminhtml_request_edit_tab_exchangeitem_price',
        ));
        
        $this->addColumn('xqty_to_exchange', array(
            'header' => Mage::helper('rma')->__('Qty To Exchange'),
            'width' => '80px',
            'filter_index' => false,
            'renderer' => 'rma/adminhtml_request_edit_tab_exchangeitem_qty',
        ));

        $this->addColumn('xrequest_value', array(
            'header' => Mage::helper('rma')->__('Accept'),
            'width' => '80px',
            'filter_index' => false,
            'renderer' => 'rma/adminhtml_request_edit_tab_exchangeitem_type',
        ));


        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/gridexchange', array('_current' => true));
    }

    public function getRowUrl($row) {
        return false;
    }

}

