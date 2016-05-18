<?php

class SM_RMA_Block_Adminhtml_Approve_Grid_Renderer_Scanned extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $parentItem = $row->getData('parent_item_id');
        if (!empty($parentItem)) {
            $item = Mage::getModel('sales/order_item')->load($parentItem);
            if ($item->getData('product_type') == 'configurable') {
                return '<div id="qty-scanned-' . $row->getProductId() . '" style="text-align:center;display:none;">0</div><input type="hidden" id="qty-scanned-hidden-' . $row->getProductId() . '" />';
            }
        }
        $item = Mage::getModel('sales/order_item')->load(intval($row->getItemId()));
        if($item->getData('parent_item_id')){
            $parent_item = Mage::getModel('sales/order_item')->load($item->getData('parent_item_id'));
            $product = Mage::getModel('catalog/product')->load($parent_item->getProductId());
           // echo $parent_item->getData('product_type');
            if($parent_item->getData('product_type')=="configurable"){
                    return '<div class="qty-scanned-' . $row->getProductId() . '" id="qty-scanned-' . $row->getProductId() . '" style="text-align:center;">0</div><input type="hidden" class = "qty-scanned-hidden-' . $row->getProductId() . '" id="qty-scanned-hidden-' . $row->getProductId() . '"  value="0" />';

            }else{
                if($product['price_type']=="0"){
                    return '<div class="qty-scanned-' . $row->getProductId() . '" id="qty-scanned-' . $row->getProductId() . '" style="text-align:center;">0</div><input type="hidden" class = "qty-scanned-hidden-' . $row->getProductId() . '" id="qty-scanned-hidden-' . $row->getProductId() . '" name="items[' . $row->getId() . ']" value="0" />';
                }

                else{
                    return '<div  style="text-align:center;display:none;">0</div><input type="hidden" class = "qty-scanned-hidden-' . $row->getProductId() . '" id="qty-scanned-hidden-' . $row->getProductId() . '" name="items[' . $row->getId() . ']" value="0" />';
                }
            }

        }
        else{
            if($item->getProductType()=="bundle"){
                //  echo "bundle";
                $item = Mage::getModel('sales/order_item')->load(intval($row->getItemId()));
                $product = Mage::getModel('catalog/product')->load($item->getProductId());
                if($product['price_type']=="0"){
                    return '<div  style="text-align:center;display:none;">0</div><input type="hidden" class = "qty-scanned-hidden-' . $row->getProductId() . '" id="qty-scanned-hidden-' . $row->getProductId() . '" name="items[' . $row->getId() . ']" value="0" />';
                }
                else{
                    return '<div class="qty-scanned-' . $row->getProductId() . '" id="qty-scanned-' . $row->getProductId() . '" style="text-align:center;">0</div><input type="hidden" class = "qty-scanned-hidden-' . $row->getProductId() . '" id="qty-scanned-hidden-' . $row->getProductId() . '" name="items[' . $row->getId() . ']" value="0" />';
                }
            }
            else{

                //XBAR-726 - tungdt2
                return '<script>
                    var quantityLocal = localStorage.getItem("qty-scanned-' . $row->getProductId().'");
                    if(quantityLocal){
                        document.getElementById("qty-scanned-' . $row->getProductId().'").innerHTML = quantityLocal;
                        document.getElementById("qty-scanned-hidden-' . $row->getProductId() . '").value = quantityLocal;
                    }else{
                        document.getElementById("qty-scanned-' . $row->getProductId().'").innerHTML = 0;
                    }
                    </script>
                    <div class="qty-scanned-' . $row->getProductId() . '" id="qty-scanned-' . $row->getProductId() . '" style="text-align:center;">0</div>

                <input type="hidden" class = "qty-scanned-hidden-' . $row->getProductId() . '" id="qty-scanned-hidden-' . $row->getProductId() . '" name="items[' . $row->getId() . ']" value="0" />';
            }
        }
//        else
//        return '<div class="qty-scanned-' . $row->getProductId() . '" id="qty-scanned-' . $row->getProductId() . '" style="text-align:center;">0</div><input type="hidden" class = "qty-scanned-hidden-' . $row->getProductId() . '" id="qty-scanned-hidden-' . $row->getProductId() . '" name="items[' . $row->getId() . ']" value="0" />';
    }

}
