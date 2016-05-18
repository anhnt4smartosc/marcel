<?php

class SM_RMA_Block_Adminhtml_Request_Edit_Tab_Items extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('rmaRmaRequestGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::registry('rma_item_collection_data');
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns() {
        $this->addColumn('item_id', array(
            'header' => Mage::helper('rma')->__('Item #'),
            'width' => '20px',
            'type' => 'number',
            'index' => 'item_id',
        ));

//        $this->addColumn('thumbnail', array(
//            'header' => Mage::helper('catalog')->__('Thumbnail'),
//            'type' => 'image',
//            'width' => '50px',
//            'index' => 'thumbnail',
//        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('rma')->__('Product Name'),
            'filter_index' => false,
            'width' => '150px',
            'renderer' => 'rma/adminhtml_request_edit_tab_item_name',
        ));

//        $this->addColumn('sku', array(
//            'header' => Mage::helper('rma')->__('SKU'),
//            'filter_index' => false,
//            'width' => '150px',
//            'renderer' => 'rma/adminhtml_request_edit_tab_item_name',
//        ));

        $this->addColumn('product_id', array(
            'header' => Mage::helper('rma')->__('Product #'),
            'type' => 'number',
            'filter_index' => false,
            'width' => '50px',
            'renderer' => 'rma/adminhtml_request_edit_tab_item_productid',
        ));

        $this->addColumn('qty_shipped', array(
            'header' => Mage::helper('rma')->__('Qty Shipped'),
            'width' => '60',
            'type' => 'number',
            'filter_index' => false,
            'renderer' => 'rma/adminhtml_request_edit_tab_item_shipped',
        ));

        $this->addColumn('qty_to_return', array(
            'header' => Mage::helper('rma')->__('Qty To Return'),
            'width' => '60',
            'type' => 'number',
            'index' => 'qty_to_return',
        ));

        $this->addColumn('request_type', array(
            'header' => Mage::helper('rma')->__('Action'),
            'width' => '130px',
            'filter_index' => false,
            'renderer' => 'rma/adminhtml_request_edit_tab_item_type',
        ));

        $this->addColumn('request_value', array(
            'header' => Mage::helper('rma')->__('Refund Amount'),
            'width' => '120',
            'filter_index' => false,
            'renderer' => 'rma/adminhtml_request_edit_tab_item_value',
        ));

        $this->addColumn('update_stock', array(
            'header' => Mage::helper('rma')->__('Update Stock?'),
            'width' => '20',
            'filter_index' => false,
            'renderer' => 'rma/adminhtml_request_edit_tab_item_stock',
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/gridedit', array('_current' => true));
    }

    public function getRowUrl($row) {
        return false;
    }

}

