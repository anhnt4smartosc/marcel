<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Admin
 * Date: 7/20/12
 * Time: 1:59 PM
 * To change this template use File | Settings | File Templates.
 */

class SM_Barcode_Model_System_Config_Source_Allattribute {
    public function toOptionArray() {
        $result[] = array('value' => '' ,'label' => '');
        $test = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter();


        if ($test != null && $test->count() > 0):
            foreach ($test as $item):
                $result[] = array('value' => $item->getAttributeId(), 'label' => $item->getAttributeCode());
            endforeach;
        endif;

        return $result;
    }
}