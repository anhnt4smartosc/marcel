<?php

class SM_RMA_Block_Adminhtml_Request_Edit_Tab_Exchangeitem_Type extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        if (!$row->getDone()) {
            $item = Mage::getModel('catalog/product')->load(intval($row->getItemId()));


            $requestTypes = array();
            $requestTypes[] = '<option value=0>No</option>';
            $requestTypes[] = '<option value='.$row->getQtyToExchange().'>Yes</option>';


            return '<select style="width: 100%" id="xrequest_type_' . $item->getId() . '" name="xrequest_type[' .
                    $item->getId() . ']" >' .
                    implode('', $requestTypes) . '</select>';
        } else {
            return $row->getLastLog();
        }
    }

}
