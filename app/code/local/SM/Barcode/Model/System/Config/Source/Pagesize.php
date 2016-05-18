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
 * @version    2.4
 * @author     thangnv@smartosc.com
 * @copyright  Copyright (c) 2010-2011 SmartOSC Co. (http://www.smartosc.com)
 */
class SM_Barcode_Model_System_Config_Source_Pagesize {
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'A4', 'label'=>Mage::helper('barcode')->__('A4 (210mm × 297mm)')),
            array('value' => 'A5', 'label'=>Mage::helper('barcode')->__('A5 (148mm × 210mm)')),
            array('value' => 'Custom', 'label'=>Mage::helper('barcode')->__('Custom size')),
        );
    }
}