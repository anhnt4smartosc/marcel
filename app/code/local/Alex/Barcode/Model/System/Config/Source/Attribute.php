<?php

/**
 * Created by PhpStorm.
 * User: tuananh
 * Date: 5/29/2016
 * Time: 4:17 PM
 */
class Alex_Barcode_Model_System_Config_Source_Attribute extends SM_Barcode_Model_System_Config_Source_Attribute
{
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label'=>Mage::helper('barcode')->__('Product ID')),
            array('value' => 1, 'label'=>Mage::helper('barcode')->__('SKU')),
            array('value' => 3, 'label'=>Mage::helper('barcode')->__('Expired Date + SKU')),
            array('value' => 2, 'label'=>Mage::helper('barcode')->__('Custom Attribute'))
        );
    }
}