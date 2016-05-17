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
class Magestore_Inventoryfulfillment_Block_Adminhtml_Fulfillment_Dashboard extends Magestore_Inventoryfulfillment_Block_Adminhtml_Container
{
    /**
     * Add childs to view block
     *
     */
    protected function _prepareChilds(){
        $verifying = $this->getDashboardChildBlock('verifying');
        $picking = $this->getDashboardChildBlock('picking');
        $packing = $this->getDashboardChildBlock('packing');
        $readyToShip = $this->getDashboardChildBlock('readytoship');
        $shippingStatus = $this->getDashboardChildBlock('shippingstatus');

        $this->addBottomLeftChild($verifying);
        $this->addBottomRightChild($picking);
        $this->addBottomLeftChild($packing);
        $this->addBottomRightChild($readyToShip);
        $this->addBottomChild($shippingStatus);
    }

    /**
     * Get child blocks of dashboard by name
     *
     * @param $blockName
     * @return Magestore_Inventoryfulfillment_Block_Adminhtml_Fulfillment_Dashboard_Template
     */
    public function getDashboardChildBlock($blockName = null)
    {
        $block = $this->getLayout()->createBlock('inventoryfulfillment/adminhtml_fulfillment_dashboard_' . $blockName)
            ->setData('block_name', $blockName);
        return $block;
    }
}