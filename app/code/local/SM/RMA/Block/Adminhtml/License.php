<?php

class SM_RMA_Block_Adminhtml_License extends Mage_Adminhtml_Block_Template {

    public function __construct() {
        $this->_blockGroup = 'rma';
        $this->_controller = 'adminhtml_license';

        parent::__construct();
    }

    /**
     * Prepare html output
     *
     * @return string
     */
    protected function _toHtml() {
        echo "<style type='text/css'>
            div.rma-license {
                background: none repeat scroll 0 0 #FDFAA4;
                border-bottom: 1px solid #988753;
                bottom: 0;
                display: block;
                height: 300px;
                opacity: 0.85;
                position: fixed;
                right: 0;
                width: 300px;
                z-index: 100;
            }
            div.rma-license-content {
                font-weight: bold;
                margin: 30px auto;
                width: 90%;
            }
              </style>      
        <div class='rma-license'><div class='rma-license-content'>Your license of the RMA extension is not valid or expired. Please, renew your license by <a href='" . Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit/section/rma') . "'>click here.</a></div></div>";
        return parent::_toHtml();
    }

}
