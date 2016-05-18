<?php

class SM_RMA_Model_Mysql4_Request_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('rma/request');
    }

    public function addCustomerFilter($id) {
        $this->getSelect()->where('customer_id=?', $id);
        return $this;
    }

    public function orderBy($str) {
        $this->getSelect()
                ->order($str);
        return $this;
    }

    public function findItemsReturned() {
        return $this;
    }

}
 
