<?php

class Alex_Sales_Block_Adminhtml_Transaction extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'alexsales';
        $this->_controller = 'adminhtml_transaction';
        $this->_headerText = Mage::helper('alexsales')->__('Transaction of Cashiers');

        parent::__construct();
    }
}