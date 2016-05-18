<?php
/**
 * Date: 1/11/13
 * Time: 3:28 PM
 */

class SM_Barcode_Block_Adminhtml_Stock extends Mage_Adminhtml_Block_Widget_Grid_Container {
    public function __construct() {
        $this->_blockGroup = 'barcode';
        $this->_controller = 'adminhtml_stock';
        parent::__construct();

        $this->setTemplate('sm/barcode/stock.phtml');
    }

    protected function _prepareLayout() {
        $this->setChild('stock_products_grid', $this->getLayout()->createBlock('barcode/adminhtml_stock_grid', 'stock_products_grid'));
        return $this;
    }

    public function getStockProductsGridHtml() {
        return $this->getChildHtml('stock_products_grid');
    }
}