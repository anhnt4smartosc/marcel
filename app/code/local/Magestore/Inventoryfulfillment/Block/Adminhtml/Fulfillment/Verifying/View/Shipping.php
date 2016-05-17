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
class Magestore_Inventoryfulfillment_Block_Adminhtml_Fulfillment_Verifying_View_Shipping extends Magestore_Inventoryfulfillment_Block_Adminhtml_Order_View_Abstract
{   

    protected function _prepareLayout() {
        parent::_prepareLayout();
        //$this->setTemplate('inventoryfulfillment/verifying/view/shipping.phtml');
        $this->setTemplate('inventoryfulfillment/order/view/shipping.phtml');
    }
    

    /**
     * Retrieve customer model object
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        if(!$this->hasData('customer')){
            $order = $this->getParentBlock()->getOrder();
            if($order){
                $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
                $this->setData('customer', $customer);
            }
        }
        return $this->getData('customer');
    }
    
    /**
     * Retrieve customer identifier
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->getCustomer()->getId();
    } 
    
    /**
     * Retrieve array of shipping rates groups
     *
     * @return array
     */
    public function getShippingRates()
    {
        if (empty($this->_rates)) {
            $groups = $this->getAddress()->getGroupedAllShippingRates();
            /*
            if (!empty($groups)) {

                $ratesFilter = new Varien_Filter_Object_Grid();
                $ratesFilter->addFilter($this->getStore()->getPriceFilter(), 'price');

                foreach ($groups as $code => $groupItems) {
                    $groups[$code] = $ratesFilter->filter($groupItems);
                }
            }
            */
            return $this->_rates = $groups;
        }
        return $this->_rates;
    }    
    
}