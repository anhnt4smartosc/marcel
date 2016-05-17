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
class Magestore_Inventoryfulfillment_Block_Adminhtml_Fulfillment_Verifying_View extends Magestore_Inventoryfulfillment_Block_Adminhtml_Order_View {

    /**
     * Add childs to view block
     * 
     */
    protected function _prepareChilds() {
        $this->setStep(Magestore_Inventoryfulfillment_Model_Step::FFM_STEP_VERIFYING);
        $accountInfo = $this->getAccountBlock();
        $billingAddresss = $this->getAddressBlock('billing_address');
        $shippingAddresss = $this->getAddressBlock('shipping_address');
        $payment = $this->getPaymentBlock();
        $shipping = $this->getShippingBlock();
        $comment = $this->getCommentBlock();
        $emailNotification = $this->getNotificationEmailBlock();

        $this->addTopChild($accountInfo);
        $this->addTopChild($payment);
        $this->addTopChild($billingAddresss);
        $this->addTopChild($shippingAddresss);
        $this->addBottomLeftChild($comment);
        $this->addBottomRightChild($shipping);
        $this->addBelowButtonChild($emailNotification);

        $submitOrderUrl = Mage::helper('adminhtml')->getUrl('adminhtml/inf_orderverifying/postorder', array('order_id' => $this->getOrder()->getId()));

        $this->_addButton('submit', array(
            'label' => Mage::helper('adminhtml')->__('Submit Comment'),
            'onclick' => 'fulfillmentObj.submitVerifyOrder(this, \'' . $this->getOrder()->getId() . '\',\'' . $submitOrderUrl . '\')',
            'class' => 'submit',
                ), 0, 1, 'bottom_right');

        if ($this->getOrder()->getFulfillmentStatus() != 0) {
            $this->_addButton('pick', array(
                'label' => Mage::helper('adminhtml')->__('Pick Items'),
                'onclick' => 'fulfillmentObj.verifypickaction(this, \'' . $this->getOrderId() . '\')',
                'class' => 'save',
                    ), 0, 10, 'bottom_right');
        } else {
            $this->_addButton('verify_pick', array(
                'label' => Mage::helper('adminhtml')->__('Verify & Pick'),
                'onclick' => 'fulfillmentObj.verifypickaction(this, \'' . $this->getOrderId() . '\')',
                'class' => 'save',
                    ), 0, 20, 'bottom_right');
        }
        /*
          $this->_addButton('verify', array(
          'label'        => Mage::helper('adminhtml')->__('Commit Verify'),
          'onclick'    => 'fulfillmentObj.verifyaction(this, \'' . $this->getOrderId() . '\')',
          'class'        => 'save',
          ), 0, 30 , 'bottom_right');
         */
        if ($this->_isAllowedAction('cancel') && $this->getOrder()->canCancel()
                && $this->getOrder()->getFulfillmentStatus() == '0') {
            $this->_addButton('cancel', array(
                'label' => Mage::helper('adminhtml')->__('Cancel Order'),
                'onclick' => 'fulfillmentObj.cancelaction(this, \'' . $this->getOrderId() . '\')',
                'class' => 'delete',
                    ), 0, 40, 'bottom_right');
        }
    }

    /**
     * 
     * @return Magestore_Inventoryfulfillment_Block_Adminhtml_Fulfillment_Verifying_View_Shipping
     */
    public function getShippingBlock() {
        $shippingBlock = $this->getLayout()->createBlock('inventoryfulfillment/adminhtml_fulfillment_verifying_view_shipping');
        return $shippingBlock;
    }

    public function getItemsBlock() {
        return $this->getLayout()->createBlock('inventoryfulfillment/adminhtml_fulfillment_verifying_view_items');
    }

}
