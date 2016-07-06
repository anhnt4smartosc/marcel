<?php

class Alex_Sales_Model_Resource_Commit_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('alexsales/commit');
    }
}
