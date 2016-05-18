<?php

class SM_RMA_Adminhtml_Rma_ApproveController extends SM_Barcode_Controller_Adminhtml_Action {

    public function gridAction() {
        $this->loadLayout(false)
                ->renderLayout();
    }

    public function indexAction() {
        if ($this->_validated) {
            $this->_title($this->__('RMA'))
                    ->_title($this->__('Approve Request'));

            $this->loadLayout()
                    ->_setActiveMenu('smartosc/rma_approve')
                    ->renderLayout();
        }
    }

}

