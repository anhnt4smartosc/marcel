<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ADMIN
 * Date: 6/3/13
 * Time: 5:55 PM
 * To change this template use File | Settings | File Templates.
 */

class SM_Barcode_Block_Adminhtml_Countinventory extends Mage_Adminhtml_Block_Widget_Grid_Container {
    public function __construct(){
        $this->_blockGroup = 'barcode';
        $this->_controller = 'adminhtml_countinventory';
        parent::__construct();

        $this->setTemplate('sm/barcode/countinventory.phtml');
    }

    protected function _prepareLayout() {
        $this->setChild('barcode_countinventory_grid', $this->getLayout()->createBlock('barcode/adminhtml_countinventory_grid', 'countinventory.grid'));
        return $this;
    }

    public function getProductInforGridHtml() {
        return $this->_getChildHtml('barcode_countinventory_grid');
    }
}