<?php

class SM_RMA_Block_Adminhtml_Request_Edit_Tab_Totals extends Mage_Adminhtml_Block_Template
{
	protected $_totals;
    protected $_order = null;
    
    public function __construct(){
      parent::__construct();
      $this->setId('rmaTabTotals');
      $this->setTemplate('sm/rma/edit/totals.phtml');
      $this->_label = Mage::helper('rma')->__("Totals");
      $this->_headLabel = Mage::helper('rma')->__("Totals");
    }

    
    public function getOrder(){
        $rma = Mage::getModel('rma/request')->load(intval($this->getRequest()->getParam('id')));
        return Mage::getModel('sales/order')->load(intval($rma->getOrderId()));
    }
    
    public function getTotals()
    {
        $source = $this->getOrder();
        
        $this->_totals = array();
        $this->_totals['subtotal'] = new Varien_Object(array(
            'code'  => 'subtotal',
            'value' => $source->getSubtotal(),
            'label' => $this->__('Subtotal')
        ));


        /**
         * Add shipping
         */
        if (!$source->getIsVirtual() && ((float) $source->getShippingAmount() || $source->getShippingDescription()))
        {
            $this->_totals['shipping'] = new Varien_Object(array(
                'code'  => 'shipping',
                'field' => 'shipping_amount',
                'value' => $source->getShippingAmount(),
                'label' => $this->__('Shipping & Handling')
            ));
        }

        /**
         * Tax Amount
         */
        $this->_totals['tax_amount'] = new Varien_Object(array(
            'code'  => 'tax_amount',
            'value' => $source->getTaxAmount(),
            'label' => $this->__('Tax Amount'),
        ));

        /**
         * Add discount
         */
        if (((float)$source->getDiscountAmount()) != 0) {
            if ($source->getDiscountDescription()) {
                $discountLabel = $this->__('Discount (%s)', $source->getDiscountDescription());
            } else {
                $discountLabel = $this->__('Discount');
            }
            $this->_totals['discount'] = new Varien_Object(array(
                'code'  => 'discount',
                'field' => 'discount_amount',
                'value' => $source->getDiscountAmount(),
                'label' => $discountLabel
            ));
        }

        

        /**
         * Giftcert Amount
         */
        if ($source->getGiftcertCode() != 0) {
            $this->_totals['giftcert_amount'] = new Varien_Object(array(
                'code'  => 'giftcert_amount',
                'value' => '-' . $source->getGiftcertAmount(),
                'label' => $this->__('Gift Certificates (' . $source->getGiftcertCode() . ')' ),
            ));
        }


        $this->_totals['grand_total'] = new Varien_Object(array(
            'code'  => 'grand_total',
            'field'  => 'grand_total',
            'strong'=> true,
            'value' => $source->getGrandTotal(),
            'label' => $this->__('Grand Total'),
            'is_formated' => true,
        ));

        /**
         * Base grandtotal
         */
        if ($source->isCurrencyDifferent()) {
            $this->_totals['base_grandtotal'] = new Varien_Object(array(
                'code'  => 'base_grandtotal',
                'value' => $source->formatBasePrice($source->getBaseGrandTotal()),
                'label' => $this->__('Grand Total to be Charged'),
                'is_formated' => true,
            ));
        }

        /**
         * Total Paid
         */
        $this->_totals['total_paid'] = new Varien_Object(array(
            'code'  => 'total_paid',
            'value' => $source->getTotalPaid(),
            'label' => $this->__('Total Paid'),
            'is_formated' => true,
        ));

        /**
         * Total Refunded
         */
        if ($source->getTotalDue() != 0) {
            $this->_totals['total_refunded'] = new Varien_Object(array(
                'code'  => 'total_refunded',
                'value' => $source->getTotalRefunded(),
                'label' => $this->__('Total Refunded'),
                'is_formated' => true,
            ));
        }

        /**
         * Total Due
         */
        if ($source->getTotalDue() != 0) {
            $this->_totals['total_due'] = new Varien_Object(array(
                'code'  => 'total_due',
                'value' => $source->getTotalDue(),
                'label' => $this->__('Total Due'),
                'is_formated' => true,
            ));
        }

        /**
         * Gift Certificates Credited
         */
        if ($source->getGiftcertAmountCredited() != 0) {
            $this->_totals['giftcert_amount_credited'] = new Varien_Object(array(
                'code'  => 'giftcert_amount_credited',
                'value' => $source->getGiftcertAmountCredited(),
                'label' => $this->__('Gift Certificates Invoiced'),
                'is_formated' => true,
            ));
        }
        /**
         * Gift Certificates Invoiced
         */
        if ($source->getGiftcertAmountInvoiced() != 0) {
            $this->_totals['giftcert_amount_invoiced'] = new Varien_Object(array(
                'code'  => 'giftcert_amount_invoiced',
                'value' => $source->getGiftcertAmountInvoiced(),
                'label' => $this->__('Gift Certificates Invoiced'),
                'is_formated' => true,
            ));
        }

        return $this->_totals;

    }

}
