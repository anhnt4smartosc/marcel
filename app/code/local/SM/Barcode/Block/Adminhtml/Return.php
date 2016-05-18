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
class SM_Barcode_Block_Adminhtml_Return extends Mage_Adminhtml_Block_Widget_Grid_Container{
    public function __construct(){
        $this->_blockGroup = 'barcode';
		$this->_controller = 'adminhtml_return';

		parent::__construct();
        $this->setTemplate('sm/barcode/return.phtml');
    }
    
    protected function _prepareLayout()
    {        
        $this->setChild('return_products_grid', $this->getLayout()->createBlock('barcode/adminhtml_return_grid', 'return_products_grid'));
        return $this;
    }
    
    public function getReturnProductsGridHtml()
    {
        return $this->getChildHtml('return_products_grid');
    }
}
 
