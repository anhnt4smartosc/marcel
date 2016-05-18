<?php

class SM_RMA_Helper_Data extends Mage_Core_Helper_Abstract {

    public function isEnableModule() {
        return Mage::getStoreConfig('barcode/general/enabled');
    }

}
 
