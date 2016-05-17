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
class Magestore_Inventoryfulfillment_Block_Adminhtml_Fulfillment_Shipping_View extends Magestore_Inventoryfulfillment_Block_Adminhtml_Order_View {

    protected function _prepareChilds() {
        $this->setStep(Magestore_Inventoryfulfillment_Model_Step::FFM_STEP_SHIPPING);
        $accountInfo = $this->getAccountBlock();
        $billingAddresss = $this->getAddressBlock('billing_address');
        $shippingAddresss = $this->getAddressBlock('shipping_address');
        $payment = $this->getPaymentBlock();
        $shipping = $this->getShippingBlock();

        $this->addTopChild($accountInfo);
        $this->addTopChild($shippingAddresss);
        $this->addTopChild($payment);
        $this->addTopChild($shipping);
    }
    
    public function getBlockTitle() {
        $shipment = $this->getShipment();
        $order = $this->getOrder();
        $title = $this->__('Shipment #%s - Order #%s', $shipment->getIncrementId(), $order->getIncrementId());
        return $title;
    }
    
    public function getItemsBlock() {
        return $this->getLayout()->createBlock('inventoryfulfillment/adminhtml_fulfillment_shipping_view_items');
    }    
    
    /**
     * Get shipment
     * 
     * @return \Mage_Sales_Model_Order_Shipment
     */
    public function getShipment() {
        return Mage::registry('current_shipment');
    }

}
