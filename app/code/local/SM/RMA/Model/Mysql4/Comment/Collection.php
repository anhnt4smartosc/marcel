<?php

class SM_RMA_Model_Mysql4_Comment_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('rma/comment');
    }

    public function orderBy($str) {
        $this->getSelect()
                ->order($str);
        return $this;
    }

}
 
