<?php
/**
 * Date: 2/5/13
 * Time: 9:56 AM
 */

class SM_Barcode_Block_Adminhtml_Catalog_Product_Render_Barcode extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row) {
        $product = Mage::getModel('catalog/product')->load($row->getData('entity_id'));
        if(isset($product)) {
            return '<span>'.$product->getData('sm_barcode').'</span>';
        }
    }
}