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
class SM_Barcode_Block_Adminhtml_Label extends Mage_Adminhtml_Block_Widget_Grid_Container{
    public function __construct(){
        parent::__construct();
        $this->_blockGroup = 'label';
        $this->_controller = 'adminhtml_label';
        $this->_headerText = Mage::helper('barcode')->__('Barcode')." - ".Mage::helper('barcode')->__('Barcode label');

        $this->setTemplate('sm/barcode/label.phtml');
    }

    protected function _prepareLayout()
    {
        $this->setChild('labelgrid', $this->getLayout()->createBlock('barcode/adminhtml_label_grid', 'label.grid'));
        return $this;
    }

    public function getBarcodeGridHtml()
    {
        return $this->getChildHtml('labelgrid');
    }
}
 
