<?php

require_once 'app/code/core/Mage/ImportExport/controllers/Adminhtml/ImportController.php';
class Alex_Import_Adminhtml_ImportController extends Mage_ImportExport_Adminhtml_ImportController
{
    public function startPreviewAction()
    {
        $this->loadLayout('popup');
        

        $this->renderLayout();
    }
}
