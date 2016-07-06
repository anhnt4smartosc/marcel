<?php

class Alex_Sales_Block_Adminhtml_Config_Form_Field_Payment
    extends Mage_Core_Block_Html_Select
{
    public function _toHtml()
    {
        $options = Mage::getModel('payment/config')->getActiveMethods();

        foreach ($options as $key => $option) {
            $this->addOption($key, $option->getId());
        }

        return parent::_toHtml();
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }
}