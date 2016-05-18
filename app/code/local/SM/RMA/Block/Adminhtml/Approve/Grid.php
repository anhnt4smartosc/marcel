<?php

class SM_RMA_Block_Adminhtml_Approve_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_defaultLimit = 999999999;

    public function __construct()
    {
        parent::__construct();
        $this->setId('rmaReturnProductsGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->setPagerVisibility(false);
    }

    protected function _prepareCollection()
    {
        if ($this->getRequest()->getParam('order_id')) {
            $order_id = $this->getRequest()->getParam('order_id');
        } else {
            $order_id = 0;
        }
        $order = Mage::getModel('sales/order')->loadByIncrementID($order_id);
        $collection = Mage::getModel('sales/order_item')->getCollection()
            ->addFieldToFilter('main_table.order_id', $order->getId())
            ->addFieldToFilter('qty_ordered', array('gt' => 0))
            ->addFieldToFilter('qty_shipped', array('gt' => 0))
            //->addFieldToFilter('main_table.product_type', 'simple')
            //->addFieldToFilter('qty_shipped',array('gt' => 0))
        ;

        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('valid',
            array(
                'header' => Mage::helper('rma')->__(''),
                'width' => '50px',
                'renderer' => 'rma/adminhtml_approve_grid_renderer_valid',
            ));
        $this->addColumn('product_id',
            array(
                'header' => Mage::helper('rma')->__('Product ID'),
                'type' => 'number',
                'index' => 'product_id',
            ));
        $this->addColumn('name',
            array(
                'header' => Mage::helper('rma')->__('Product Name'),
                'index' => 'name',
            ));
        //////////////////////////////////////////////////////
        $this->addColumn('sku',
            array(
                'header' => Mage::helper('rma')->__('SKU'),
                'index' => 'sku',
            ));
        //////////////////////////////////////////////////////
        $this->addColumn('qty_ordered',
            array(
                'header' => Mage::helper('rma')->__('Qty Ordered'),
                'width' => '50px',
                'renderer' => 'rma/adminhtml_approve_grid_renderer_ordered',
                'index' => 'qty_ordered',
            ));
        $this->addColumn('qty_shipped',
            array(
                'header' => Mage::helper('rma')->__('Qty Shipped'),
                'width' => '50px',
                'renderer' => 'rma/adminhtml_approve_grid_renderer_shipped',
                'index' => 'qty_shipped',
            ));
        $this->addColumn('qty_returned',
            array(
                'header' => Mage::helper('rma')->__('Qty Returned'),
                'width' => '50px',
                'renderer' => 'rma/adminhtml_approve_grid_renderer_returned',
                'index' => 'qty_refunded',
            ));
        $this->addColumn('qty_scanned',
            array(
                'header' => Mage::helper('rma')->__('Qty Scanned'),
                'width' => '50px',
                'filter' => false,
                'sortable'=> false,
                'renderer' => 'rma/adminhtml_approve_grid_renderer_scanned',
            ));
        //////////////////////////////////////////////////////////////////
        $this->addColumn('check',
            array(
                'header' => Mage::helper('rma')->__('Manual Scan'),
                'width' => '110px',
                'filter' => false,
                'renderer' => 'rma/adminhtml_approve_grid_renderer_noscan',
            ));
        //////////////////////////////////////////////////////////////////

        $store = $this->_getStore();


        return parent::_prepareColumns();
    }

    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
}
