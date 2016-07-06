<?php

class Alex_Sales_Adminhtml_CommitController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('alexsales/adminhtml_commit'));
        $this->renderLayout();
    }

    public function reportAction()
    {
        $this->loadLayout();
        
        $this->renderLayout();
    }
}