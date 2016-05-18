<?php
class SM_RMA_Block_Customer_View extends Mage_Core_Block_Template{ 
    protected function _construct(){
		parent::_construct();
	}
    
    public function getRMA(){
        return Mage::getModel('rma/request')->load($this->getRequest()->getParam('id'));
    }
}

