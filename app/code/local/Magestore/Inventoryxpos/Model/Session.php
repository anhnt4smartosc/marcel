<?php

class Magestore_Inventoryxpos_Model_Session extends Mage_Core_Model_Session_Abstract
{

    public function _construct()
    {
        $namespace = 'inventoryxpos';
        $namespace .= '_' . (Mage::app()->getStore()->getWebsite()->getCode());

        $this->init($namespace);
        Mage::dispatchEvent('inventoryxpos_session_init', array('inventoryxpos_session' => $this));
    }

}