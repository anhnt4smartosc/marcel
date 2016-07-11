<?php

class Alex_Sales_Block_Adminhtml_Commit_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('form_tabs');
        $this->setDestElementId('edit_form'); // this should be same as the form id define above
        $this->setTitle(Mage::helper('alexsales')->__('Commit Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('general_section', array(
            'label'     => Mage::helper('alexsales')->__('General'),
            'title'     => Mage::helper('alexsales')->__('Commit Information'),
            'content'   => $this->getLayout()->createBlock('alexsales/adminhtml_commit_edit_tab_form')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }
}