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
 * Inventoryfulfillment Edit Block
 * 
 * @category     Magestore
 * @package     Magestore_Inventoryfulfillment
 * @author      Magestore Developer
 */
class Magestore_Inventoryfulfillment_Block_Adminhtml_Fulfillment_Shipping extends Mage_Adminhtml_Block_Template
{   
    /**
     * 
     * @return \Magestore_Inventoryfulfillment_Block_Adminhtml_Fulfillment_Shipping
     */
    protected function _prepareLayout() {
        parent::_prepareLayout();
        $this->setTemplate('inventoryfulfillment/shipping/list.phtml');
        $this->_addPackageGroups();
        return $this;
    }
    
    /**
     * Add package group to this page
     * 
     */
    protected function _addPackageGroups() {
        $carriers = Mage::helper('inventoryfulfillment')->getAvailableCarriers();
        if(count($carriers)){
            foreach($carriers as $code => $title){
                $packageGroup = $this->getLayout()->createBlock('inventoryfulfillment/adminhtml_fulfillment_shipping_packagegroup');
                $packageGroup->setGroupName($code);
                $this->setChild('package_group_'.$code, $packageGroup);
            }
        }
    }
}