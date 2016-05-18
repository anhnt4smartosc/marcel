<?php

class SM_RMA_Model_Mysql4_Exchangeitem extends Mage_Core_Model_Mysql4_Abstract {

    protected function _construct() {
        $this->_init('rma/exchangeitem', 'id');
    }

}
