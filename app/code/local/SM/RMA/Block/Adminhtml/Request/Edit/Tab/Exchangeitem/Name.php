<?php
class SM_RMA_Block_Adminhtml_Request_Edit_Tab_Exchangeitem_Name extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        //$item = Mage::getModel('sales/order_item')->load(intval($row->getItemId()));
        $item = Mage::getModel('catalog/product')->load($row->getItemId());        
        $hidden = '<input type="hidden" name="rma_exchangeitems['.$row->getItemId().']" value="'.$row->getId().'" />';
        return $hidden . $item->getName();
    }
}