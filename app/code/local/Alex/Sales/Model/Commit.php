<?php

class Alex_Sales_Model_Commit extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('alexsales/commit');
    }

    public function getByUser($xposUserId) {
        //get current commit of xpos-user
        $commit = Mage::getModel('alexsales/commit')->getCollection()
            ->setOrder('time', 'desc')
            ->addFieldToFilter('xpos_user_id', $xposUserId)
            ->getFirstItem();

        if($commit && $commit->getId()) {
            return $commit;
        }

        Mage::throwException("Not found needed commit for user $xposUserId");
    }
}