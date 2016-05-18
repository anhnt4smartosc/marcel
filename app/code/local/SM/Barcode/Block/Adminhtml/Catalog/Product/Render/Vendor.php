<?php
/**
 * Date: 1/30/13
 * Time: 2:53 PM
 */

class SM_Barcode_Block_Adminhtml_Catalog_Product_Render_Vendor extends Varien_Data_Form_Element_Abstract {
    public function getElementHtml() {
        $product = $this->getProduct();
        if($this->getProduct()){
            $barcode = $product->getData('sm_barcode');
            if (isset($barcode))
                return '<input type="text" value="'.$barcode.'" readonly="true">';
        } else{
            return '<input type="text" value="..." disabled="disable">';
        }
    }
    
    public function getProduct(){
        if (isset($_REQUEST['product'])) {
            if(is_array($_REQUEST['product'])) return false;
            $product = Mage::getModel('catalog/product')->load($_REQUEST['product']);
            return $product;
        }
        return Mage::registry('current_product');
    }
}