<?php

class SM_Barcode_Adminhtml_Barcode_LabelController extends SM_Barcode_Controller_Adminhtml_Action {

    protected function _initAction() {
        $this->loadLayout()
            ->_setActiveMenu('label/items')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Barcode Label'), Mage::helper('adminhtml')->__('Barcode Label'));
        return $this;
    }



    public function indexAction() {
        $this->_initAction()
            ->renderLayout();
    }


    /**
     * Product grid for AJAX request
     */
    public function gridAction()
    {
        $this->_forward('index');

    }

}