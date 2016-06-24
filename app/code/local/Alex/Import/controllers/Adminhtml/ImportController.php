<?php

class Alex_Import_Adminhtml_ImportController extends Mage_ImportExport_Adminhtml_ImportController
{
    public function startPreviewAction()
    {
        $this->loadLayout('popup');
        $this->renderLayout();
    }
}
