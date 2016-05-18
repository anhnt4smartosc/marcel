<?php
class SM_RMA_Block_Adminhtml_Approve extends Mage_Adminhtml_Block_Widget_Grid_Container{
    public function __construct(){
        $this->_blockGroup = 'rma';
		$this->_controller = 'adminhtml_approve';

		parent::__construct();
        $this->setTemplate('sm/rma/approve.phtml');
    }
    
    protected function _prepareLayout()
    {        
        $this->setChild('return_products_grid', $this->getLayout()->createBlock('rma/adminhtml_approve_grid', 'return_products_grid'));
        return $this;
    }
    
    public function getReturnProductsGridHtml()
    {
        return $this->getChildHtml('return_products_grid');
    }
}
 
