<?php
class SM_RMA_Block_Adminhtml_Approve_Grid_Renderer_Shipped extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        if ($row->getParentItemId()) {
            $product = Mage::getModel('sales/order_item')->load($row->getParentItemId());
        } else {
            $product = Mage::getModel('sales/order_item')->load($row->getItemId());
        }
        $item = Mage::getModel('sales/order_item')->load(intval($row->getItemId()));
        if($item->getData('parent_item_id')){
            $parent_item = Mage::getModel('sales/order_item')->load($item->getData('parent_item_id'));
           // echo "<pre>"; var_dump($parent_item->getData());
            $product = Mage::getModel('catalog/product')->load($parent_item->getProductId());
            if($product['price_type']=="0"){
                return '<div class = "qty-shipped-'.$row->getProductId().'" id="qty-shipped-'.$row->getProductId().'" style="text-align:center;">'.intval($parent_item->getQtyShipped()).'</div>';
            }
            else{
                return '<div   style="text-align:center;display:none;">'.intval($parent_item->getQtyShipped()).'</div>';
            }
        }
        else{
            if($item->getProductType()=="bundle"){
                //  echo "bundle";
                $item = Mage::getModel('sales/order_item')->load(intval($row->getItemId()));
                $product = Mage::getModel('catalog/product')->load($item->getProductId());
                if($product['price_type']=="0"){
                    return '<div   style="text-align:center;display:none;">'.intval($item->getQtyShipped()).'</div>';
                }
                else{
                    return '<div class = "qty-shipped-'.$row->getProductId().'" id="qty-shipped-'.$row->getProductId().'" style="text-align:center;">'.intval($item->getQtyOrdered()).'</div>';
                }
            }
            else{
                return '<div class = "qty-shipped-'.$row->getProductId().'" id="qty-shipped-'.$row->getProductId().'" style="text-align:center;">'.intval($item->getQtyShipped()).'</div>';
            }
        }
       // else
        //return '<div class = "qty-shipped-'.$row->getProductId().'" id="qty-shipped-'.$row->getProductId().'" style="text-align:center;">'.intval($product->getQtyOrdered()).'</div>';
    }
}

