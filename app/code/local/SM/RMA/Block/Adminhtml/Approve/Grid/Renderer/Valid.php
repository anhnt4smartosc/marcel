<?php

class SM_RMA_Block_Adminhtml_Approve_Grid_Renderer_Valid extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $resource = Mage::getModel('catalog/product')->getResource();
        $request = Mage::getModel('rma/request')->getCollection()
                ->addFieldToFilter('main_table.status', array('neq' => SM_RMA_Model_Request::STATUS_PENDING_APPROVAL))
        ;
        $request->getSelect()->join(array('items' => $resource->getTable('rma/item')), 'items.rma_id=main_table.id')
                ->where('items.item_id=' . $row->getId())
        ;

        $qty_returned = 0;
        if ($request->getSize()) {
            foreach ($request as $value) {
                $qty_returned += intval($value->getQtyToReturn());
            }
        }

        if ($row->getParentItemId()) {
            $product = Mage::getModel('sales/order_item')->load($row->getParentItemId());
        } else {
            $product = Mage::getModel('sales/order_item')->load($row->getItemId());
        }

        $img_valid = '';

        if ($qty_returned === intval($product->getQtyShipped())) {
            $img_valid = '<img src="' . $this->getSkinUrl('images/ico_success.gif') . '" width="16px" height="16px" alt="Valid" />';
        }
        $item = Mage::getModel('sales/order_item')->load(intval($row->getItemId()));
        if($item->getData('parent_item_id')){

            $parent_item = Mage::getModel('sales/order_item')->load($item->getData('parent_item_id'));
            $product = Mage::getModel('catalog/product')->load($parent_item->getProductId());
            if($product['price_type']=="0"){
                return '<div class="qty-valid-' . $row->getProductId() . '" id="qty-valid-' . $row->getProductId() . '" style="text-align:center;">' . $img_valid . '</div>';
            }
            else{
                return '<div  style="text-align:center;">' . $img_valid . '</div>';
            }
        }
        else{
            if($item->getProductType()=="bundle"){
                //  echo "bundle";
                $item = Mage::getModel('sales/order_item')->load(intval($row->getItemId()));
                $product = Mage::getModel('catalog/product')->load($item->getProductId());
                if($product['price_type']=="0"){
                    return '<div  style="text-align:center;">' . $img_valid . '</div>';
                }
                else{
                    return '<div class="qty-valid-' . $row->getProductId() . '" id="qty-valid-' . $row->getProductId() . '" style="text-align:center;">' . $img_valid . '</div>';
                }
            }
            else{
                return '<script>
                var imageCheck = localStorage.getItem("qty-valid-'. $row->getProductId() .'");
                if(imageCheck){
                 document.getElementById("qty-valid-'. $row->getProductId() .'").innerHTML = imageCheck;
                 }else{

                 }
                </script><div class="qty-valid-' . $row->getProductId() . '" id="qty-valid-' . $row->getProductId() . '" style="text-align:center;">' . $img_valid . '</div>';
            }
        }
     //   else
      //  return '<div class="qty-valid-' . $row->getProductId() . '" id="qty-valid-' . $row->getProductId() . '" style="text-align:center;">' . $img_valid . '</div>';
    }

}
