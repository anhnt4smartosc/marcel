<?php

class SM_RMA_Block_Adminhtml_Request_Edit_Tab_Item_Shipped extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $item = Mage::getModel('sales/order_item')->load(intval($row->getItemId()));
        if ($item->getParentItemId()) {
            $parent = Mage::getModel('sales/order_item')->load($item->getParentItemId());
            return '<div style="text-align:center;">' . intval($parent->getQtyShipped()) . '</div>';
        } else {
            return '<div style="text-align:center;">' . intval($item->getQtyShipped()) . '</div>';
        }
    }

}
