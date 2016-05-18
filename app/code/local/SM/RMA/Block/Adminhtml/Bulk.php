<?php
class SM_RMA_Block_Adminhtml_Bulk extends Mage_Adminhtml_Block_Widget_Grid_Container{
    public function __construct(){
        $this->_blockGroup = 'rma';
        $this->_controller = 'adminhtml_bulk';

        parent::__construct();
        $this->setTemplate('sm/rma/bulk.phtml');
    }

    protected function _prepareLayout()
    {
        $this->setChild('return_products_grid', $this->getLayout()->createBlock('rma/adminhtml_bulk_grid', 'return_products_grid')->setTemplate('sm/rma/bulk/grid.phtml'));
        return $this;
    }

    public function getReturnProductsGridHtml()
    {
        return $this->getChildHtml('return_products_grid');
    }
}
 
