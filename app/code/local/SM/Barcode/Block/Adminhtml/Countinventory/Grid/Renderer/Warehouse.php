<?php
/**
 * User: thangnv@smartosc.com
 * Date: 7/25/13
 * Time: 5:59 PM
 * To change this template use File | Settings | File Templates.
 */
class SM_Barcode_Block_Adminhtml_Countinventory_Grid_Renderer_Warehouse extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row){
        // Get warehouse data from session
        $collection = Mage::getSingleton('adminhtml/session')->getWarehouseCollection();
        // Get url for ajax request
        $url = Mage::helper("adminhtml")->getUrl("*/barcode_countinventory/ajaxwarehousequantity");

        // Generate HTML
        $input  = '<select onchange=\'getCurrQtyWarehouseOnchange(this,"'.$url.'", '.$row->getId().');\'>';
        $input .= '<option value="allwarehouse">All warehouse</option>';

        if($collection != null){
            foreach($collection as $val){
                $optionvalue  = $val['warehouseLbl'];
                $optionvalue .= '_'.$val['warehouseId'];
                $input .= '<option value="'.$optionvalue.'">'.$val['warehouseLbl'].'</option>';
            }
        }

        $input .= '</select>';
        return $input;
    }
}