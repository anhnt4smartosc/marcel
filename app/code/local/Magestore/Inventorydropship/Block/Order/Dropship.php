<?php

class Magestore_Inventorydropship_Block_Order_Dropship extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('inventorydropship/order/dropship.phtml');
    }

    protected function _prepareLayout()
    {
        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle($this->__('Order # %s', $this->getOrder()->getRealOrderId()));
        }
        $this->setChild(
            'payment_info',
            $this->helper('payment')->getInfoBlock($this->getOrder()->getPayment())
        );
    }

    public function getPaymentInfoHtml()
    {
        return $this->getChildHtml('payment_info');
    }

    /**
     * Retrieve current order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    /**
     * Return back url for logged in and guest users
     *
     * @return string
     */
    public function getBackUrl()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            return Mage::getUrl('sales/order/history');
        }
        return Mage::getUrl('sales/order/form');
    }

    /**
     * Return back title for logged in and guest users
     *
     * @return string
     */
    public function getBackTitle()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            return Mage::helper('sales')->__('Back to My Orders');
        }
        return Mage::helper('sales')->__('View Another Order');
    }

    public function getInvoiceUrl($order)
    {
        return Mage::getUrl('sales/order/invoice', array('order_id' => $order->getId()));
    }

    public function getViewUrl($order)
    {
        return Mage::getUrl('sales/order/view', array('order_id' => $order->getId()));
    }

    public function getCreditmemoUrl($order)
    {
        return Mage::getUrl('sales/order/creditmemo', array('order_id' => $order->getId()));
    }
}