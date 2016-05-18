<?php
class SM_RMA_Block_Adminhtml_Request extends Mage_Adminhtml_Block_Widget_Grid_Container{
    public function __construct(){
        $this->_blockGroup = 'rma';
		$this->_controller = 'adminhtml_request';
		$this->_headerText = Mage::helper('rma')->__('RMA')." - ".Mage::helper('rma')->__('Request List');

		parent::__construct();
        $this->removeButton('add');
        /*
        $this->_addButton('print_barcode', array(
            'label'     => Mage::helper('barcode')->__('Print Barcode'),
            'onclick'   => "$('frmBarcodeGrid').submit();",
        ));
        */
    }
}
 
 
