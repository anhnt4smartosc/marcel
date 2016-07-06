<?php

class Alex_Sales_Adminhtml_TransactionController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        
        $this->renderLayout();
    }

    public function reportAction()
    {
        $this->loadLayout();
        
        $this->renderLayout();
    }
}