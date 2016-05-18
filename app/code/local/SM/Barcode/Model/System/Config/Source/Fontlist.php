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
 * @author     truongnq@smartosc.com
 * @copyright  Copyright (c) 2012-2013 SmartOSC Co. (http://www.smartosc.com)
 */
class SM_Barcode_Model_System_Config_Source_Fontlist
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'helvetica', 'label'=>Mage::helper('barcode')->__('Default')),
            array('value' => 'times', 'label'=>Mage::helper('barcode')->__('Times')),
            array('value' => 'timesb', 'label'=>Mage::helper('barcode')->__('Times Bold')),
            array('value' => 'timesi', 'label'=>Mage::helper('barcode')->__('Times Italic')),
            array('value' => 'timesbi', 'label'=>Mage::helper('barcode')->__('Times Bold Italic')),
            array('value' => 'courier', 'label'=>Mage::helper('barcode')->__('Courier')),
            array('value' => 'courierb', 'label'=>Mage::helper('barcode')->__('Courier Bold')),
            array('value' => 'courieri', 'label'=>Mage::helper('barcode')->__('Courier Italic')),
            array('value' => 'courierbi', 'label'=>Mage::helper('barcode')->__('Courier Bold Italic')),
            array('value' => 'freemono', 'label'=>Mage::helper('barcode')->__('Freemono')),
            array('value' => 'freemonob', 'label'=>Mage::helper('barcode')->__('Freemono Bold')),
            array('value' => 'freemonoi', 'label'=>Mage::helper('barcode')->__('Freemono Italic')),
            array('value' => 'freemonobi', 'label'=>Mage::helper('barcode')->__('Freemono Bold Italic')),

        );
    }

}
