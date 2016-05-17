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
class Magestore_Inventoryfulfillment_Block_Adminhtml_Fulfillment_Shipping_Packagegroup extends Mage_Adminhtml_Block_Widget_Form_Container {

    protected function _prepareLayout() {
        parent::_prepareLayout();
        $this->setTemplate('inventoryfulfillment/shipping/packagegroup.phtml');
    }

    protected function _beforeToHtml() {
        
        $this->_addButton('print_list_order', array(
            'label' => Mage::helper('adminhtml')->__('Print Shipments'),
            'onclick' => 'fulfillmentObj.printListShipment(this, \'' . $this->getGroupName() . '\')',
            'class' => 'pick',
                ), 0, 1, 'bottom_right');
        
        
        $this->_addButton('ship_order', array(
            'label' => Mage::helper('adminhtml')->__('Commit Ship'),
            'onclick' => 'fulfillmentObj.shipOrders(this, \'' . $this->getGroupName() . '\')',
            'class' => 'save',
                ), 0, 10, 'bottom_right');
        
        $packageGrid = $this->getLayout()
                ->createBlock('inventoryfulfillment/adminhtml_fulfillment_shipping_packagegrid', 'package_grid' . $this->getGroupName(), array('group_name' => $this->getGroupName())
        );
        $this->setChild('package_grid', $packageGrid);
        return parent::_beforeToHtml();
    }

    /**
     * Get package grid
     * 
     * @return string
     */
    public function getPackageGrid() {
        return $this->getChildHtml('package_grid');
    }

    /**
     * 
     * @return string
     */
    public function getGroupTitle() {
        $carrier = Mage::getSingleton('shipping/config')->getCarrierInstance($this->getGroupName());
        if ($carrier) {
            return $carrier->getConfigData('title');
        } elseif($this->getGroupName() == 'no_shipping') {
            return $this->__('No Shipping Method');
        }
        return null;
    }

    /**
     * Get total packages
     * 
     * @return int
     */
    public function getTotalPackages() {
        if ($this->getChild('package_grid')) {
            return $this->getChild('package_grid')->getCollection()->getSize();
        }
        return 0;
    }

}
