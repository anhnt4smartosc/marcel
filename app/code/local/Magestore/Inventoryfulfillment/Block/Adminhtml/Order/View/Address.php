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
class Magestore_Inventoryfulfillment_Block_Adminhtml_Order_View_Address
    extends Magestore_Inventoryfulfillment_Block_Adminhtml_Order_View_Abstract
{
    const ADDRESS_TYPE_BILLING = 'billing_address';
    const ADDRESS_TYPE_SHIPPING = 'shipping_address';
    
    protected function _prepareLayout() {
        parent::_prepareLayout();
        $this->setTemplate('inventoryfulfillment/order/view/address.phtml');
    }
    
    public function getAddressType(){
        if($this->getData('address_type'))
            return $this->getData('address_type');
        return self::ADDRESS_TYPE_BILLING;
    }
    
    public function getAddress(){
        if(!$this->getOrder())
            return null;
        switch ($this->getAddressType()){
            case self::ADDRESS_TYPE_BILLING :
                return $this->getOrder()->getBillingAddress();
            case self::ADDRESS_TYPE_SHIPPING :
                return $this->getOrder()->getShippingAddress();
        }
    }
    
    public function getFormattedAddress(){
        if(!$this->getAddress()){
            return null;
        }
        return $this->getAddress()->getFormated(true);
    }
    
    public function getTitle(){
        switch ($this->getAddressType()){
            case self::ADDRESS_TYPE_BILLING :
                return $this->__('Billing Address');
            case self::ADDRESS_TYPE_SHIPPING :
                return $this->__('Shipping Address');
        }        
    }
}