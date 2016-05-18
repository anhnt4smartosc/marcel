<?php
/**
 * Created by PhpStorm.
 * User: ter
 * Date: 11/2/2015
 * Time: 6:06 PM
 */
class SM_Barcode_Model_Adminhtml_System_Config_Backend_Datemodified extends Mage_Core_Model_Config_Data{

    protected function _afterSave()
    {
        $date = Mage::getStoreConfig('barcode/general/date_modified');
        $config = new Mage_Core_Model_Config();
        $config->saveConfig('barcode/general/date_modified', $date);
        return parent::_afterSave();
    }

    protected function _beforeSave()
    {
        return parent::_beforeSave();
    }

    public function save()
    {
        return parent::save();
    }


}