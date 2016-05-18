<?php
class SM_RMA_Block_Adminhtml_Return_Grid_Renderer_Qty extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $item = Mage::getModel('sales/order_item')->load(intval($row->getItemId()));
        if($item->getData('parent_item_id')){
            $parent_item = Mage::getModel('sales/order_item')->load($item->getData('parent_item_id'));
            $product = Mage::getModel('catalog/product')->load($parent_item->getProductId());
            if($product['price_type']=="0"){
                return '<input type="text" class= "qty-scanned-'.$row->getProductId().'" id="qty-scanned-'.$row->getProductId().'" name="qty_scanned_'.$row->getProductId().'" value="0" class="input-text" style="text-align:center;" readonly="readonly" />';
            }
            else{
                return '';
            }
        }
        else{
            if($item->getProductType()=="bundle"){
                //  echo "bundle";
                $item = Mage::getModel('sales/order_item')->load(intval($row->getItemId()));
                $product = Mage::getModel('catalog/product')->load($item->getProductId());
                if($product['price_type']=="0"){
                    return '';
                }
                else{
                    return '<input type="text" class= "qty-scanned-'.$row->getProductId().'" id="qty-scanned-'.$row->getProductId().'" name="qty_scanned_'.$row->getProductId().'" value="0" class="input-text" style="text-align:center;" readonly="readonly" />';
                }
            }
            else{
                return '<input type="text" class= "qty-scanned-'.$row->getProductId().'" id="qty-scanned-'.$row->getProductId().'" name="qty_scanned_'.$row->getProductId().'" value="0" class="input-text" style="text-align:center;" readonly="readonly" />';
            }
        }


        //else
        //return '<input type="text" class= "qty-scanned-'.$row->getProductId().'" id="qty-scanned-'.$row->getProductId().'" name="qty_scanned_'.$row->getProductId().'" value="0" class="input-text" style="text-align:center;" readonly="readonly" />';
    }
}
 
