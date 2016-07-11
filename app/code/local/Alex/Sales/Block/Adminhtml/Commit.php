<?php

class Alex_Sales_Block_Adminhtml_Commit extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'alexsales';
        $this->_controller = 'adminhtml_commit';
        $this->_headerText = Mage::helper('alexsales')->__('Commit of Cashiers');

        parent::__construct();
    }
}