<?php

class Alex_Sales_Model_Resource_Transaction extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('alexsales/transaction', 'transaction_id');
    }
}