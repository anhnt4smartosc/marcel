<?php
/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryfulfillment
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventoryfulfillment Status Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryfulfillment
 * @author      Magestore Developer
 */
class Magestore_Inventoryfulfillment_Model_Step extends Varien_Object
{
    /**
     * Fulfillment steps
     * 
     */
    const FFM_STEP_VERIFYING = 'verify';
    const FFM_STEP_PICKING = 'pick';
    const FFM_STEP_PACKING = 'pack';
    const FFM_STEP_SHIPPING = 'ship';
    
    /**
     * get model option as array
     *
     * @return array
     */
    static public function getOptionArray()
    {
        return array(
            self::FFM_STEP_VERIFYING    => Mage::helper('inventoryfulfillment')->__('Verifying'),
            self::FFM_STEP_PICKING   => Mage::helper('inventoryfulfillment')->__('Picking'),
            self::FFM_STEP_PACKING   => Mage::helper('inventoryfulfillment')->__('Packing'),
            self::FFM_STEP_SHIPPING   => Mage::helper('inventoryfulfillment')->__('Shipping')
        );
    }
    
    /**
     * get model option hash as array
     *
     * @return array
     */
    static public function getOptionHash()
    {
        $options = array();
        foreach (self::getOptionArray() as $value => $label) {
            $options[] = array(
                'value'    => $value,
                'label'    => $label
            );
        }
        return $options;
    }
}