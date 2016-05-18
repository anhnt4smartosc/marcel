<?php

class SM_RMA_Block_Adminhtml_Request_Edit_Tab_Exchangeitem_Qty extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        if (!$row->getDone()) {
            return '<input type="text" name="rma_exchangeitemsqty['.$row->getItemId().']" value="'.$row->getQtyToExchange().'" />';
        } else {
            return $row->getQtyToExchange();
        }
    }

}
