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
class SM_Barcode_Block_Adminhtml_Order extends Mage_Adminhtml_Block_Widget_Grid_Container{
    public function __construct(){
        $this->_blockGroup = 'barcode';
		$this->_controller = 'adminhtml_order';
		//$this->_headerText = Mage::helper('barcode')->__('Barcode')." - ".Mage::helper('barcode')->__('Products Selected');

		parent::__construct();
        /*
        $this->removeButton('add');
        $this->_addButton('print_barcode', array(
            'label'     => Mage::helper('barcode')->__('Print Barcode'),
            'onclick'   => "$('frmBarcodeGrid').submit();",
        ));
        */
        
        $this->setTemplate('sm/barcode/order.phtml');
    }
    
    protected function _prepareLayout()
    {        
        $this->setChild('order_products_grid', $this->getLayout()->createBlock('barcode/adminhtml_order_grid', 'order_products_grid'));
        return $this;
    }
    
    public function getOrderProductsGridHtml()
    {
        return $this->getChildHtml('order_products_grid');
    }
}
 
