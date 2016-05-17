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
 * Inventoryfulfillment Adminhtml Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryfulfillment
 * @author      Magestore Developer
 */
class Magestore_Inventoryfulfillment_Block_Adminhtml_Order_View extends Mage_Adminhtml_Block_Sales_Order_View {

    const POSITION_TOP = 'top';
    const POSITION_LEFT = 'left';
    const POSITION_RIGHT = 'right';
    const POSITION_BOTTOM_LEFT = 'bottom_left';
    const POSITION_BOTTOM_RIGHT = 'bottom_right';
    const POSITION_BELOW_BUTTON = 'below_button';

    protected $_childs = array();

    protected function _prepareLayout() {
        parent::_prepareLayout();
        $this->_prepareChilds();
        $this->setTemplate('inventoryfulfillment/order/view.phtml');
    }

    /**
     * Add childs
     * 
     * @return \Magestore_Inventoryfulfillment_Block_Adminhtml_Order_View
     */
    protected function _prepareChilds() {
        return $this;
    }
    
    public function addTopChild($block, $priority = 0) {
        return $this->_addChild($block, self::POSITION_TOP, $priority);
    }
    
    public function addLeftChild($block, $priority = 0) {
        return $this->_addChild($block, self::POSITION_LEFT, $priority);
    }

    public function addRightChild($block, $priority = 0) {
        return $this->_addChild($block, self::POSITION_RIGHT, $priority);
    }

    public function addBottomLeftChild($block, $priority = 0) {
        return $this->_addChild($block, self::POSITION_BOTTOM_LEFT, $priority);
    }

    public function addBottomRightChild($block, $priority = 0) {
        return $this->_addChild($block, self::POSITION_BOTTOM_RIGHT, $priority);
    }

    public function addBelowButtonChild($block, $priority = 0) {
        return $this->_addChild($block, self::POSITION_BELOW_BUTTON, $priority);
    }

    protected function _addChild($block, $position, $priority = 0) {
        if ($block) {
            $block->setParentBlock($this);
            if ($priority) {
                $this->_childs[$position][$priority] = $block;
            } else {
                $this->_childs[$position][] = $block;
            }
        }
        return $this;
    }
    
    public function getTopChilds() {
        return $this->_getChildsHtml(self::POSITION_TOP);
    }

    public function getLeftChilds() {
        return $this->_getChildsHtml(self::POSITION_LEFT);
    }

    public function getRightChilds() {
        return $this->_getChildsHtml(self::POSITION_RIGHT);
    }

    public function getBottomLeftChilds() {
        return $this->_getChildsHtml(self::POSITION_BOTTOM_LEFT);
    }

    public function getBottomRightChilds() {
        return $this->_getChildsHtml(self::POSITION_BOTTOM_RIGHT);
    }

    public function getBelowButtonChilds() {
        return $this->_getChildsHtml(self::POSITION_BELOW_BUTTON);
    }

    protected function _getChildsHtml($position) {
        if (!isset($this->_childs[$position]) || !count($this->_childs[$position]))
            return null;

        $html = '';
        $beforeHtml = '';
        $afterHtml = '';
        $i=0;
        foreach ($this->_childs[$position] as $child) {
            $i++;
            if($position == self::POSITION_TOP){
                if($i%2 == 1){
                    $beforeHtml = '<div class="box-left">';
                    $afterHtml = '</div>';
                } else {
                    $beforeHtml = '<div class="box-right">';
                    $afterHtml = '</div><div class="clear fix"></div>';
                }
            }
                    if($position == self::POSITION_BOTTOM_RIGHT && 0){
                        //var_dump(get_class($child));
        } else {
        
            $html .= $beforeHtml . $child->toHtml() . $afterHtml;
        }
        }
       // if($position == self::POSITION_BOTTOM_RIGHT) die();
        return $html;
    }

    public function getAccountBlock() {
        return $this->getLayout()->createBlock('inventoryfulfillment/adminhtml_order_view_account');
    }

    /**
     * Get address block
     * 
     * @param string $addressType
     */
    public function getAddressBlock($addressType = null) {
        return $this->getLayout()->createBlock('inventoryfulfillment/adminhtml_order_view_address')
                        ->setAddressType($addressType);
    }

    public function getItemsBlock() {
        return $this->getLayout()->createBlock('inventoryfulfillment/adminhtml_order_view_items');
    }

    public function getPaymentBlock() {
        return $this->getLayout()->createBlock('inventoryfulfillment/adminhtml_order_view_payment');
    }
    
    public function getCommentBlock() {
        return $this->getLayout()->createBlock('inventoryfulfillment/adminhtml_order_view_comment');
    }    
    
    public function getShippingBlock() {
        $shippingBlock = $this->getLayout()->createBlock('inventoryfulfillment/adminhtml_order_view_shipping');
        return $shippingBlock;
    }

    public function getBarcodeBlock() {
        $barcodeBlock = $this->getLayout()->createBlock('inventoryfulfillment/adminhtml_order_view_barcode');
        return $barcodeBlock;
    }
    
    public function getOrderItems(){
        $itemBlock = $this->getItemsBlock();
        $this->_addChild($itemBlock, 'order_item');
        return $itemBlock->toHtml();
    }    
    
    public function setStep($step){
        if(!Mage::registry('fulfillment_step')){
            Mage::register('fulfillment_step', $step);
        }
    }

    public function getNotificationEmailBlock() {
        return $this->getLayout()->createBlock('inventoryfulfillment/adminhtml_order_view_emailnotification');
    }
}
