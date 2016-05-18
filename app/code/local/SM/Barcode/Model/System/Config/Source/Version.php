<?php
/**
 * SmartOSC Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * 
 * @category   SM
 * @package    SM_Barcode
 * @version    2.0
 * @author     hoadx@smartosc.com
 * @copyright  Copyright (c) 2010-2011 SmartOSC Co. (http://www.smartosc.com)
 */
class SM_Barcode_Model_System_Config_Source_Version
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => '0', 'label'=>Mage::helper('barcode')->__('')),
            array('value' => '13', 'label'=>Mage::helper('barcode')->__('1.3.x')),
            array('value' => '14', 'label'=>Mage::helper('barcode')->__('1.4.x')),
            array('value' => '15', 'label'=>Mage::helper('barcode')->__('1.5.x')),
            array('value' => '16', 'label'=>Mage::helper('barcode')->__('1.6.x')),
        );
    }

}
