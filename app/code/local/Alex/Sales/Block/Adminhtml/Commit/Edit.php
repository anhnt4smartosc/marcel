<?php

class Alex_Sales_Block_Adminhtml_Commit_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_commit';
        $this->_blockGroup = 'alexsales';

        $this->_updateButton('save', 'label', Mage::helper('alexsales')->__('Save Commit'));
        $this->_updateButton('delete', 'label', Mage::helper('alexsales')->__('Delete Commit'));
    }

    public function getHeaderText()
    {
        if($this->_isEditing()) {
            return Mage::helper('alexsales')->__('Edit Commit "%s"', $this->escapeHtml(Mage::registry('current_commit')->getId()));
        } else {
            return Mage::helper('alexsales')->__('New Commit');
        }
    }

    protected function _isEditing()
    {
        return Mage::registry('current_commit') && Mage::registry('current_commit')->getId();
    }
}
