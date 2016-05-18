<?php

class SM_RMA_Block_Adminhtml_Request_Edit_Tab_Item_Type extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {

        if (!$row->getDone()) {
            $item = Mage::getModel('sales/order_item')->load(intval($row->getItemId()));
            echo "<pre>";
            //var_dump($item->getData());
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            if($product['price_type']=="0"){

            }
            else{
                //            if ($item->getParentItemId()) {
//                return;
////                $childProduct = Mage::getModel('catalog/product')->load($item->getProductId());
////                $parentItem = Mage::getModel('sales/order_item')->load(intval($item->getParentItemId()));
////                $options = Mage::helper('rma/item')->getOptions($parentItem);
////                $parentProduct = Mage::getModel('catalog/product')->load($parentItem->getProductId());
////                $optionCode = Mage::helper('rma/item')->getOptionCode($parentProduct);
////                $currentOption = $childProduct->getResource()->getAttribute($optionCode)->getSource()->getOptionText($childProduct->getData($optionCode));
//            }

                $requestTypes = array();
                $requestTypes[] = '<option value=0>Reject</option>';
                $requestTypes[] = '<option value=1>Refund</option>';
//            if (Mage::getModel('ugiftcert/cert'))   // Enable convert to Gift Certificate if the extension is enable
//                $requestTypes[] = '<option value=2>Gift Certificate</option>';
//            if (isset($options)) {
                $requestTypes[] = '<option value=3>Refund + Exchange</option>';
//
//                      }
                $request_id = $this->getRequest()->getParam('id');
                $request = Mage::getSingleton('rma/request')->load($request_id);
                $type = 0;
                if ($request->getRequestType() == 1) {
                    // refund
                    $type = 2;
                } else {
                    // exchange
                    $type = 1;
                }
                return '<select style="width: 100%" id="request_type_' . $item->getId() . '" name="request_type[' .
                $item->getId() . ']" onchange="chooseRequestValue(this, ' . $item->getId() . ');">' .
                implode('', $requestTypes) . '</select>'
                . "<script>
                             initType = function (){
                            var options = $$('select#request_type_" . $item->getId() . " option');
                            options[" . $type . "].selected = true;
                            chooseRequestValue($('request_type_" . $item->getId() . "')," . $item->getId() . ");

                            }
                    Event.observe(window, 'load', initType, false); </script>";
            }



        } else {
            return $row->getLastLog() . "<script>
                                        initExchange = function (){
                                        $('exchange-items').show();
                                        $('product-search-area').hide();
                                        }
                                        Event.observe(window, 'load', initExchange, false); </script>";
        }
    }

}
