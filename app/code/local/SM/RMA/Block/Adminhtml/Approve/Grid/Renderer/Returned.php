<?php

class SM_RMA_Block_Adminhtml_Approve_Grid_Renderer_Returned extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
//        $resource = Mage::getModel('catalog/product')->getResource();
//        $request = Mage::getModel('rma/request')->getCollection()
//                ->addFieldToFilter('main_table.status', array('neq' => SM_RMA_Model_Request::STATUS_PENDING_APPROVAL))
//        ;
//        $request->getSelect()->join(array('items' => $resource->getTable('rma/item')), 'items.rma_id=main_table.id')
//                ->where('items.item_id=' . $row->getId())
//        ;

        $qty_returned = intval($row->getQtyRefunded());
//        if ($request->getSize()) {
//            foreach ($request as $value) {
//                $qty_returned += intval($value->getQtyToReturn());
//            }
//        }
        $item = Mage::getModel('sales/order_item')->load(intval($row->getItemId()));
        if($item->getData('parent_item_id')){
            $parent_item = Mage::getModel('sales/order_item')->load($item->getData('parent_item_id'));
            $product = Mage::getModel('catalog/product')->load($parent_item->getProductId());
            if($product['price_type']=="0"){
                return '<div class="qty-returned-' . $row->getProductId() . '" id="qty-returned-' . $row->getProductId() . '" style="text-align:center;">' . $qty_returned . '</div>';
            }
            else{
                return '<div   style="text-align:center;display:none;">' . $qty_returned . '</div>';
            }

        }
        else{
            if($item->getProductType()=="bundle"){
                //  echo "bundle";
                $item = Mage::getModel('sales/order_item')->load(intval($row->getItemId()));
                $product = Mage::getModel('catalog/product')->load($item->getProductId());
                if($product['price_type']=="0"){
                    return '<div   style="text-align:center;display:none;">' . $qty_returned . '</div>';
                }
                else{
                    return '<div class="qty-returned-' . $row->getProductId() . '" id="qty-returned-' . $row->getProductId() . '" style="text-align:center;">' . $qty_returned . '</div>';
                }
            }
            else{
                return '<div class="qty-returned-' . $row->getProductId() . '" id="qty-returned-' . $row->getProductId() . '" style="text-align:center;">' . $qty_returned . '</div>';
            }
        }


        //else
       // return '<div class="qty-returned-' . $row->getProductId() . '" id="qty-returned-' . $row->getProductId() . '" style="text-align:center;">' . $qty_returned . '</div>';
    }

}
