<?php
/**
 * Date: 2/5/13
 * Time: 9:56 AM
 */

class SM_Barcode_Block_Adminhtml_Catalog_Product_Render_Print extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row) {
        $product = Mage::getModel('catalog/product')->load($row->getData('entity_id'));
        if(isset($product)) {
            // $product->getData('entity_id');
            $print_one_url = Mage::helper("adminhtml")->getUrl("adminhtml/barcode_print/oneproduct", array('id'=>$product->getData('entity_id')));
            //http://xbarcode.local/index.php/admin/barcode_print/oneproduct/popup/1/id/157/key/67881434634622a754a2156eb38fa571/
            $pid = $product->getData('entity_id');
            $onclick = "sm_print_popup($pid, '{$print_one_url}')";
            $html = '<button type="button" class="scalable task" onclick="'.$onclick.'" ><span>Print Barcode</span></button>';
            return $html;
        }
    }
}