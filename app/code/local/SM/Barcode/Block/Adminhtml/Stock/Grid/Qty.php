<?php
/**
 * Date: 1/14/13
 * Time: 3:06 PM
 */

class SM_Barcode_Block_Adminhtml_Stock_Grid_Qty extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row) {
        $value = intval($row->getData('qty'));
        $id = intval($row->getData('entity_id'));
        $input = '<input type="text" id="new-qty" name="new-qty" value="'.$value.'" />';
        $input .= '<input type="hidden" name="product_id" value="'.$id.'" />';
        $input .= '<script type="text/javascript">';
        $input .= 'Event.observe($("new-qty"),\'change\', function(){
                    enableButton("barcode-button-complete");
                    })';
        $input .= '</script>';
        return $input;
    }
}