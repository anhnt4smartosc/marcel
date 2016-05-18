<?php
/**
 * SmartOSC Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * 
 * @category   SM
 * @package    SM_Barcode
 * @version    2.0
 * @author     hoadx@smartosc.com
 * @copyright  Copyright (c) 2010-2011 SmartOSC Co. (http://www.smartosc.com)
 */
class SM_Barcode_Block_Adminhtml_Return_Grid extends Mage_Adminhtml_Block_Widget_Grid{
    public function __construct(){
		parent::__construct();
		$this->setId('barcodeReturnProductsGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
	}
	
	protected function _prepareCollection()
    {
        if($this->getRequest()->getParam('order_id')){
            $order_id = $this->getRequest()->getParam('order_id');
        }
        else{
            $order_id = 0;
        }
        $order = Mage::getModel('sales/order')->loadByIncrementID($order_id);
        $collection = Mage::getModel('sales/order_item')->getCollection()
                                    ->addFieldToFilter('order_id', $order->getId())
                                    ->addFieldToFilter('product_type', 'simple')
                                    ->addFieldToFilter('qty_shipped',array('gt' => 0));
        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }
    
    protected function _prepareColumns()
    {
        $this->addColumn('valid',
            array(
                'header'=> Mage::helper('barcode')->__(''),
                'width' => '50px',
                'renderer' => 'barcode/adminhtml_return_grid_renderer_valid',
        ));
        $this->addColumn('product_id',
            array(
                'header'=> Mage::helper('barcode')->__('Product ID'),
                'type'  => 'number',
                'index' => 'product_id',
        ));
        $this->addColumn('name',
            array(
                'header'=> Mage::helper('barcode')->__('Product Name'),
                'index' => 'name',
        ));
        $this->addColumn('qty_ordered',
            array(
                'header'=> Mage::helper('barcode')->__('Qty Ordered'),
                'width' => '50px',
                'renderer' => 'barcode/adminhtml_return_grid_renderer_ordered',
        ));
        $this->addColumn('qty_shipped',
            array(
                'header'=> Mage::helper('barcode')->__('Qty Shipped'),
                'width' => '50px',
                'renderer' => 'barcode/adminhtml_return_grid_renderer_shipped',
        ));
        $this->addColumn('qty_scanned',
            array(
                'header'=> Mage::helper('barcode')->__('Qty Returned'),
                'width' => '50px',
                'type'  => 'number',
                'renderer'  => 'barcode/adminhtml_return_grid_renderer_scanned',
        ));
        
        $store = $this->_getStore();
        

        return parent::_prepareColumns();
    }
    
    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
} 
