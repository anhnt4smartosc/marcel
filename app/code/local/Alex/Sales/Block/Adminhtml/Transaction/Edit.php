<?php

class Alex_Sales_Block_Adminhtml_Transaction_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_transaction';
        $this->_blockGroup = 'alexsales';

        $this->_updateButton('save', 'label', Mage::helper('alexsales')->__('Save Transaction'));
        $this->_removeButton('delete');
    }

    public function getHeaderText()
    {
        if($this->_isEditing()) {
            return Mage::helper('alexsales')->__('Edit Transaction "%s"', $this->escapeHtml(Mage::registry('current_transaction')->getId()));
        } else {
            return Mage::helper('alexsales')->__('New Transaction');
        }
    }

    protected function _isEditing()
    {
        return Mage::registry('current_transaction') && Mage::registry('current_transaction')->getId();
    }
}
