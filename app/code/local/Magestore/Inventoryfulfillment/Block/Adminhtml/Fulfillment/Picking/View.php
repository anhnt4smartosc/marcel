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
class Magestore_Inventoryfulfillment_Block_Adminhtml_Fulfillment_Picking_View extends Magestore_Inventoryfulfillment_Block_Adminhtml_Order_View
{   
    protected function _prepareChilds(){
        $this->setStep(Magestore_Inventoryfulfillment_Model_Step::FFM_STEP_PICKING);
        $barcode = $this->getBarcodeBlock();
        $orderStatus = $this->getOrderStatusBlock();
        $comment = $this->getCommentBlock();
        $emailNotification = $this->getNotificationEmailBlock();
        $orderId = $this->getOrderId();
        $addCommentUrl = Mage::helper('adminhtml')->getUrl('adminhtml/inf_orderpacking/addComment', array('order_id' => $orderId));

        $this->addBottomLeftChild($comment);
        $this->addBottomRightChild($barcode);
        $this->addBottomRightChild($orderStatus);
        $this->addBelowButtonChild($emailNotification);
        
        $this->_addButton('pack', array(
            'label'        => Mage::helper('adminhtml')->__('Pack it!'),
            'onclick'    => 'fulfillmentObj.packaction(this, \'' . $this->getOrderId() . '\')',
            'class'        => 'pack',
        ), 0, 1 , 'bottom_right');
        
        $this->_addButton('submit', array(
            'label'        => Mage::helper('adminhtml')->__('Submit'),
            'onclick'    => 'submitAndReloadArea($(\'order_history_block\'), \'' . $addCommentUrl . '\')',
            'class'        => 'submit',
        ), 0, 10 , 'bottom_right');
    }

    public function getOrderStatusBlock() {
        return $this->getLayout()->createBlock('inventoryfulfillment/adminhtml_fulfillment_picking_orderstatus');
    }

    public function getItemsBlock() {
        return $this->getLayout()->createBlock('inventoryfulfillment/adminhtml_fulfillment_picking_items');
    }
}