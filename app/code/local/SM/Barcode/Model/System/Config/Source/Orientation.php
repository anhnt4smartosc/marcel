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
 * @copyright  Copyright (c) 2010-2013 SmartOSC Co. (http://www.smartosc.com)
 */
class SM_Barcode_Model_System_Config_Source_Orientation
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'P', 'label'=>Mage::helper('barcode')->__('Portrait')),
            array('value' => 'L', 'label'=>Mage::helper('barcode')->__('Landscape')),
        );
    }

}
