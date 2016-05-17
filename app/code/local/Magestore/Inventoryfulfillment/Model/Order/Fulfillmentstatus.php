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
class Magestore_Inventoryfulfillment_Model_Order_Fulfillmentstatus extends Varien_Object
{
    const STATUS_VERIFIED = 1;
    const STATUS_PICKED = 2;
    const STATUS_PACKED = 3;
    const STATUS_SHIPPING = 4;

    /**
     * get model option as array
     *
     * @return array
     */
    static public function getOptionArray()
    {
        return array(
            self::STATUS_VERIFIED => Mage::helper('inventoryfulfillment')->__('Verified'),
            self::STATUS_PICKED => Mage::helper('inventoryfulfillment')->__('Picked'),
            self::STATUS_PACKED => Mage::helper('inventoryfulfillment')->__('Packed'),
            self::STATUS_SHIPPING => Mage::helper('inventoryfulfillment')->__('Shipping')
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
                'value' => $value,
                'label' => $label
            );
        }
        return $options;
    }
}