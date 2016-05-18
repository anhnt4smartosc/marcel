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
class SM_Barcode_Model_System_Config_Source_Symbology
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 7, 'label'=>Mage::helper('barcode')->__('UPC-B')),
            array('value' => 3, 'label'=>Mage::helper('barcode')->__('Code 128C')),
            array('value' => 2, 'label'=>Mage::helper('barcode')->__('Code 128B')),
            array('value' => 1, 'label'=>Mage::helper('barcode')->__('Code 128A')),
            array('value' => 0, 'label'=>Mage::helper('barcode')->__('EAN13')),
            array('value' => 4, 'label'=>Mage::helper('barcode')->__('Code 39')),
            array('value' => 5, 'label'=>Mage::helper('barcode')->__('i25')),
//            array('value' => 6, 'label'=>Mage::helper('barcode')->__('QR Code')),
        );
    }

}
