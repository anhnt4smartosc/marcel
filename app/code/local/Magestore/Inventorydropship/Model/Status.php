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
 * @package     Magestore_Inventorydropship
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorydropship Status Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventorydropship
 * @author      Magestore Developer
 */
class Magestore_Inventorydropship_Model_Status extends Varien_Object
{
    const STATUS_AWAITING_SUPPLIER_CONFIRMATION = 1;
    const STATUS_AWAITING_ADMIN_APPROVAL = 2;
    const STATUS_AWAITING_SUPPLIER_SHIPMENT = 3;
    const STATUS_PARTIALLY_SHIPPED = 4;
    const STATUS_CANCELED = 5;
    const STATUS_COMPLETE = 6;

    /**
     * get model option as array
     *
     * @return array
     */
    static public function getOptionArray()
    {
        return array(
            self::STATUS_AWAITING_SUPPLIER_CONFIRMATION    => Mage::helper('inventorydropship')->__('Awaiting supplier\'s confirmation'),
            self::STATUS_AWAITING_ADMIN_APPROVAL   => Mage::helper('inventorydropship')->__('Awaiting admin\'s approval'),
            self::STATUS_AWAITING_SUPPLIER_SHIPMENT   => Mage::helper('inventorydropship')->__('Awaiting supplier\'s shipment'),
            self::STATUS_PARTIALLY_SHIPPED   => Mage::helper('inventorydropship')->__('Partially shipped'),
            self::STATUS_CANCELED   => Mage::helper('inventorydropship')->__('Canceled'),
            self::STATUS_COMPLETE   => Mage::helper('inventorydropship')->__('Complete')
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