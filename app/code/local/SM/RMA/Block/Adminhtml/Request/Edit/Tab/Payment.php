<?php

class SM_RMA_Block_Adminhtml_Request_Edit_Tab_Payment extends Mage_Adminhtml_Block_Template
{
    protected $_html = "";
	
    public function __construct(){
      parent::__construct();
      $this->setId('rmaTabPayment');
      $this->setTemplate('sm/rma/edit/payment.phtml');
      $this->_label = Mage::helper('rma')->__("Payment Information");
      $this->_headLabel = Mage::helper('rma')->__("Payment Information");
    }
    
    protected function _prepareLayout()
    {        
        $this->setChild('order_payment', $this->getLayout()->createBlock('adminhtml/sales_order_payment', 'order_payment'));
        return $this;
    }
    
    public function getOrder(){
        $rma = Mage::getModel('rma/request')->load(intval($this->getRequest()->getParam('id')));
        return Mage::getModel('sales/order')->load(intval($rma->getOrderId()));
    }
    
    public function setHtml($html)
    {
        $this->_html = $html;
    }
    
    public function getPaymentHtml()
    {
        Mage::register('sm_rma_payment_html', $this);
        //The Observers should get this object, check the payment method then call setHtml.
        Mage::dispatchEvent('sm_rma_append_payment_html');
        Mage::unregister('sm_rma_payment_html');
        return $this->_html;
    }
}
