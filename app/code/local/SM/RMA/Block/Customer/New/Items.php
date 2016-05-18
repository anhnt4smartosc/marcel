<?php
class SM_RMA_Block_Customer_New_Items extends Mage_Core_Block_Template{
    protected $_items;
    
    protected function _construct(){
        $this->setTemplate('rma/customer/new/items.phtml');
    }
    
    public function setItems($items){
        $this->_items = $items;
        
        return $this;
    }
    
    public function getItems(){
        return $this->_items;
    }
}

