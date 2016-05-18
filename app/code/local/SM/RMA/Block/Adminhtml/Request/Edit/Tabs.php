<?php

class SM_RMA_Block_Adminhtml_Request_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('request_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('rma')->__('Request Information'));
    }

    protected function _beforeToHtml() {
        $this->addTab('main', array(
            'label' => Mage::helper('rma')->__('Request Information'),
            'title' => Mage::helper('rma')->__('Request Information'),
            'content' => $this->getLayout()->createBlock('rma/adminhtml_request_edit_tab_main')->toHtml() .
            $this->getLayout()->createBlock('rma/adminhtml_request_edit_tab_payment')->toHtml() .
            $this->getLayout()->createBlock('rma/adminhtml_request_edit_tab_totals')->toHtml() .
            $this->getLayout()->createBlock('rma/adminhtml_request_edit_tab_sub')->toHtml() .
                    $this->getLayout()->createBlock('rma/adminhtml_request_edit_tab_comments')
                    ->setCollection(Mage::getModel('rma/comment')
                            ->getCollection()
                            ->addFieldToFilter('rma_id', intval($this->getRequest()->getParam('id')))
                            ->orderBy('created_time DESC')
                            ->load()
                    )
                    ->toHtml(),
        ));

        $this->addTab('items', array(
            'label' => Mage::helper('rma')->__('Request Items'),
            'title' => Mage::helper('rma')->__('Request Items'),
            'content' => $this->getLayout()->createBlock('rma/adminhtml_request_edit_tab_status')->toHtml() .
            $this->getLayout()->createBlock('rma/adminhtml_request_edit_tab_totals')->toHtml() .
            "<h3>Return items</h3>" .            
            $this->getLayout()->createBlock('rma/adminhtml_request_edit_tab_items')->toHtml() .
            "<div id='exchange-items' style='display: none;'><h3>Exchange items</h3>".            
            $this->getLayout()->createBlock('rma/adminhtml_request_edit_tab_autocomplete')->toHtml() .
            $this->getLayout()->createBlock('rma/adminhtml_request_edit_tab_exchangeitems')->toHtml() .
            "</div>",            
        ));

        return parent::_beforeToHtml();
    }

}
