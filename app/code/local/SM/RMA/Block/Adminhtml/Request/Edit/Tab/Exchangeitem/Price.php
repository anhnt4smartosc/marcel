<?php
class SM_RMA_Block_Adminhtml_Request_Edit_Tab_Exchangeitem_Price extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        if (!$row->getDone()) {
            $item = Mage::getModel('catalog/product')->load(intval($row->getItemId()));

            $product_price = $item->getPrice();

            return '<input type="text" name="rma_exchangeitemsprice['.$row->getItemId().']" value="'.$product_price.'" />';
        } else {
            return $row->getAmount();
        }
    }
}
