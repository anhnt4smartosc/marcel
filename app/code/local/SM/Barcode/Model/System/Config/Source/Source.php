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
class SM_Barcode_Model_System_Config_Source_Source {

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        $result = null;
        $test = Mage::getResourceModel('catalog/product_attribute_collection')
                ->addVisibleFilter();
        

        if ($test != null && $test->count() > 0):
            foreach ($test as $item):
                if ($item['is_unique'] == 1)
                    $result[] = array('value' => $item->getAttributeId(), 'label' => Mage::helper('barcode')->__($item->getAttributeCode()));
            endforeach;
        endif;

        return $result;
    }

}
