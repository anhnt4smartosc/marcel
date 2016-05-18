<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Admin
 * Date: 8/3/12
 * Time: 9:57 AM
 * To change this template use File | Settings | File Templates.
 */

class SM_Barcode_Model_System_Config_Source_Layout {
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label'=>Mage::helper('barcode')->__('Layout by setting size of label')),
            array('value' => 1, 'label'=>Mage::helper('barcode')->__('Layout by setting number of rows and columns label')),
        );
    }
}