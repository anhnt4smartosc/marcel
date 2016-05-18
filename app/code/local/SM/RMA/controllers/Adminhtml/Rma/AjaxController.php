<?php

class SM_RMA_Adminhtml_Rma_AjaxController extends SM_Barcode_Controller_Adminhtml_Action
{

    public function resetAllBulkAction()
    {
        $session = Mage::getSingleton("adminhtml/session");
        // echo "fkjakfk";
        //  var_dump($session->getData("lstNewId"));
        $new = array();
        $new[] = 0;
        $session->unsetData("lstId");
        $session->unsetData("lstNewId");

        $session->setData("lstId", $new);
        $session->setData("lstNewId", $new);
        $result = array();
        //  $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

    }

    public function loadReturnBarcodeImageAction()
    {
        $result = array();

        $product_id = $this->getRequest()->getPost('order_product_id');
        if (in_array(intval(Mage::getStoreConfig("barcode/general/symbology")), array(7))) {
            if (strlen($product_id) > 11) {
                $attr_val = substr($product_id, 0, -1);
            }else{
                $attr_val = $this->getRequest()->getPost('order_product_id');
            }
        } else {
            $attr_val = $this->getRequest()->getPost('order_product_id');
        }

        $product_id_nam = $this->getRequest()->getPost('product_id_nam');

//        $product_id = (int) substr($this->getRequest()->getPost('order_product_id'), 0, -1);
//        if ($this->getRequest()->getPost('scanned'))
//            foreach ($this->getRequest()->getPost('scanned') as $k => $v) {
//                $product_id = (int) $k;
//            }
//        $result['product_id'] = $product_id;
//        $product = Mage::getModel('catalog/product')->load($product_id);
        //  if ($this->getRequest()->getPost('product_id_nam')) {
//            foreach ($this->getRequest()->getPost('scanned') as $k => $v) {
//                $product_id = (int) $k;
//                $product = Mage::getModel('catalog/product')->load($product_id);
//            }
        if ($product_id_nam != -1) {
            $product = Mage::getModel('catalog/product')->load($product_id_nam);
        }
        else {
            if (intval(Mage::getStoreConfig("barcode/product/conversion") == 1)): // convert on
                switch (intval(Mage::getStoreConfig('barcode/product/barcode_field'))) {
                    case 0: // convert from product id
                        $product = Mage::getModel('catalog/product')->load($attr_val);
                        $result['product_id'] = $product->getId();
                        break;
                    case 1: // convert from sku
//                        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
//
////                        if (intval(Mage::getStoreConfig("barcode/general/symbology")) == 0)
////                            $sku = substr(trim($this->getRequest()->getPost('order_product_id')), 0, -1);
////                        else
//                            $sku = trim($this->getRequest()->getPost('order_product_id'));
//
//                        $readresult = $write->query("SELECT `e`.* FROM `catalog_product_entity` AS `e` WHERE (SUBSTRING(CONV(SUBSTRING(CAST(MD5(`e`.`sku`) AS CHAR),1,16),16,10),1,12) = '" . $sku . "')");
//                        $row = $readresult->fetch();
//                        $product_id = $row['entity_id'];
//                        //$product = Mage::getModel('catalog/product')->loadByAttribute('sku', substr(trim($this->getRequest()->getPost('order_product_id')), 0, -1));
//                        $product = Mage::getModel('catalog/product')->load($product_id);
//                        $product_id = $product->getId();
//                        $result['product_id'] = $product_id;


                        $products = Mage::getModel('catalog/product')->getCollection();
                        // $products->addAttributeToSelect('id');
                        $products->addFieldToFilter(array(
                            array('attribute' => 'sm_barcode', 'like' => $attr_val . "%"),
                        ));

                        if (count($products) > 0):
                            foreach ($products as $product):
                                $product = Mage::getModel('catalog/product')->load($product->getId());
                                $result['product_id'] = $product->getId();
                                break;
                            endforeach;
                        endif;


                        break;
                }
            else:

//                $write = Mage::getSingleton('core/resource')->getConnection('core_write');
//                $attr = Mage::getStoreConfig("barcode/product/barcode_value");
//                //$attr_val = $this->getRequest()->getPost('order_product_id');
//                if (intval(Mage::getStoreConfig("barcode/general/symbology")) == 0){
//                    $attr_val = substr($this->getRequest()->getPost('order_product_id'), 0, -1);
//                    if(Mage::getStoreConfig("barcode/product/conversion")==0){//OFF
//                        $attr_val = doubleval($attr_val);//remove 0.
//
//                    }
//                }
//                else
//                    $attr_val = $this->getRequest()->getPost('order_product_id');
//
//
//                $attributeInfo = Mage::getModel('eav/entity_attribute')->load($attr);
//                $sku_code = $attributeInfo->getAttributeCode();
//
//                if ($sku_code != "sku") {
//                    $readresult = $write->query("SELECT `e`.*, `at_name`.`value` AS `name` FROM `catalog_product_entity` AS `e` INNER JOIN `catalog_product_entity_varchar` AS `at_name` ON (`at_name`.`entity_id` = `e`.`entity_id`) AND (`at_name`.`attribute_id` = '" . $attr . "') AND (`at_name`.`store_id` = 0) WHERE at_name.value = '" . $attr_val . "'");
//                    $row = $readresult->fetch();
//                    $product_id = $row['entity_id'];
//                    $product = Mage::getModel('catalog/product')->load($product_id);
//                } else {
//                    $product = Mage::getModel('catalog/product')->loadByAttribute('sku', trim($attr_val));
//                }

                $products = Mage::getModel('catalog/product')->getCollection();
                // $products->addAttributeToSelect('id');
                $products->addFieldToFilter(array(
                    array('attribute' => 'sm_barcode', 'like' => $attr_val . "%"),
                ));

                if (count($products) > 0):
                    foreach ($products as $product):
                        $product = Mage::getModel('catalog/product')->load($product->getId());
                        $result['product_id'] = $product->getId();
                        break;
                    endforeach;
                endif;

                if ($product) {
                    $result['product_id'] = $product->getId();
                } else {
                    $result['message'] = 'Product not found';
                    $result['error'] = true;
                }
            endif;
        }

//        if(strlen($this->getRequest()->getPost('order_id')) == 13) // EAN13
//        {
//            $order_id = doubleval(substr($this->getRequest()->getPost('order_id'), 0, -1));
//        }
//        else
//        {
//            $order_id = doubleval($this->getRequest()->getPost('order_id'));
//        }

//        if (intval(Mage::getStoreConfig("barcode/product/conversion") == 1))
//        {
//        if (in_array(intval(Mage::getStoreConfig("barcode/general/symbology")), array(0, 7)))
//            $order_id = intval(substr($this->getRequest()->getPost('order_id'), 0, -1));
//        else

        /*XBA-XBAR-743*/
        $order_id = $this->processOrderIdBySymbology();

//        }
//        else
//        {
//            $order_id = intval($this->getRequest()->getPost('order_id'));
//        }

        if (strlen($order_id) > 9)
            $order_id = substr($order_id, 0, -1) . "-" . substr($order_id, strlen($order_id) - 1, 1);

        $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
        $items = Mage::getModel('sales/order_item')->getCollection()
            //  ->addFieldToSelect('product_id')
            //  ->addFieldToSelect('parent_item_id')
            ->addFieldToFilter('main_table.order_id', $order->getId())
            ->addFieldToFilter('qty_ordered', array('gt' => 0))
            ->addFieldToFilter('qty_shipped', array('gt' => 0))
//            ->addFieldToFilter('product_type', 'simple')
            //->addFieldToFilter('qty_shipped',array('gt' => 0))
        ;
        $product_qty = array();
        $product_ids = array();
//        foreach ($items as $item) {
//            echo $item->getProductId();
//        }
//      die();
        foreach ($items as $item) {
            if ($item->getData('parent_item_id')) {
                $parent_item = Mage::getModel('sales/order_item')->load($item->getData('parent_item_id'));
                $product1 = Mage::getModel('catalog/product')->load($parent_item->getProductId());
                if ($product1['price_type'] == "0") {
                    $product_qty[$item->getProductId()] = $item->getQtyOrdered();
                    $product_ids[] = (int)$item->getProductId();
                } else {

                }
            } else {
                if ($item->getProductType() == "bundle") {
                    $product1 = Mage::getModel('catalog/product')->load($item->getProductId());
                    if ($product1['price_type'] == "0") {

                    } else {
                        $product_qty[$item->getProductId()] = $item->getQtyOrdered();
                        $product_ids[] = (int)$item->getProductId();
                    }
                } else {
                    $product_qty[$item->getProductId()] = $item->getQtyOrdered();
                    $product_ids[] = (int)$item->getProductId();
                }
            }


            // bat dau cong id
//            if($item->getData('parent_item_id')==NULL){
//                $product_qty[$item->getProductId()] = $item->getQtyOrdered();
//                $product_ids[] = (int) $item->getProductId();
//            }

        }
        $result['product_ids'] = $product_ids;
        if (!$product || !$product->getId()) {
            $result['error'] = Mage::helper('barcode')->__('Product ID is not valid.');
        } else {
            //gen barcode
            Mage::helper('barcode/barcode')->createProductBarcode($product->getId());
            $result['product_id'] = $product->getId();

            //check product in items list
            if (in_array($product->getId(), $product_ids)) {
                $result['product_in_order'] = 1;

                $count = 0;
                foreach ($product_ids as $id) {
                    if ($product->getId() == $id)
                        $count++;
                }
                $result['count'] = $count;

            } else {
                $result['product_in_order'] = 0;
            }
            $result['image_name'] = $product->getId();
            $result['error'] = '';
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

    }

    public function saveAction()
    {

        $post = $this->getRequest()->getPost();
        $numOfQtyReturn = 0;
        foreach ($post['items'] as $key => $value) {
            $numOfQtyReturn += $value;
        }
        if ($numOfQtyReturn == 0) {
            $result['message'] = 'Must have qty to refund';
            $result['error'] = true;
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            return false;
        }
        $result = array();
        $action = $this->getRequest()->getPost('save_action');

//        if(strlen($this->getRequest()->getPost('order_id')) == 13) // EAN13
//        {
//            $order_id = doubleval(substr($this->getRequest()->getPost('order_id'), 0, -1));
//        }
//        else
//        {
//            $order_id = doubleval($this->getRequest()->getPost('order_id'));
//        }

//        if (intval(Mage::getStoreConfig("barcode/product/conversion") == 1))
//        {
//        if (in_array(intval(Mage::getStoreConfig("barcode/general/symbology")), array(0, 7)))
//            $order_id = intval(substr($this->getRequest()->getPost('order_id'), 0, -1));
//        else
//            $order_id = $this->getRequest()->getPost('order_id');
//        }
//        else
//        {
//            $order_id = intval($this->getRequest()->getPost('order_id'));
//        }

        /*XBA-XBAR-743*/
        $order_id = $this->processOrderIdBySymbology();
        if (strlen($order_id) > 9)
            $order_id = substr($order_id, 0, -1) . "-" . substr($order_id, strlen($order_id) - 1, 1);



        $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
        if (!$order || !$order->getId()) {
            $result['message'] = 'Order #' . $order_id . ' not found';
            $result['error'] = true;
        }

        if ($action == 'create_rma') {
            $data = array();
            $data['store_id'] = $order->getStoreId();
            $data['order_id'] = $order->getId();
            $data['order_increment_id'] = $order->getIncrementId();
            $data['customer_id'] = $order->getCustomerIsGuest() ? 0 : $order->getCustomerId();
            $data['customer_name'] = $order->getCustomerIsGuest() ? 'Guest' : $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
            $data['customer_email'] = $order->getCustomerEmail();
            $data['package_opened'] = 1;
            $data['request_type'] = 2;
            $data['status'] = SM_RMA_Model_Request::STATUS_PENDING_APPROVAL;
            $data['created_time'] = Mage::getModel('core/date')->date('Y-m-d H:i:s');
            $data['created_by'] = 'admin';
            $request = Mage::getModel('rma/request')->setId(null);
            try {
                $request->setData($data);
                $request->save();

                //save request items
                $item = Mage::getModel('rma/item')->setId(null);
                foreach ($post['items'] as $key => $value) {
                    //$row->getParentItemId();
                    $product = Mage::getModel('sales/order_item')->load($key);
//                    if($key==494){
//                        echo "<pre>";
//                        var_dump($product);die;
//                    }
//echo "<pre>"; var_dump($post['items']);
                    if ($value > 0) {
                        if ($product->getParentItemId() == NULL) {
                            $data = array();
                            $data['rma_id'] = $request->getId();
                            $data['item_id'] = $key;
                            $data['qty_to_return'] = intval($value);
                            // $data['amount'] = $product1->getData('row_total_incl_tax');
                            try {
                                $item->setData($data);
                                $item->save();
                            } catch (Exception $e) {
                                die($e->getMessage());
                            }
                            foreach ($post['items'] as $key1 => $value1) {
                                $product1 = Mage::getModel('sales/order_item')->load($key1);
                                $parent_id = $product1->getParentItemId();
                                $parent_item = Mage::getModel('sales/order_item')->load($parent_id);
                                if ($parent_id == $key) {
                                    $data = array();
                                    $data['rma_id'] = $request->getId();
                                    $data['item_id'] = $key1;
                                    $data['qty_to_return'] = intval($value);
                                    $data['amount'] = $product1->getData('row_total_incl_tax');
                                    try {
                                        $item->setData($data);
                                        $item->save();
                                    } catch (Exception $e) {
                                        die($e->getMessage());
                                    }
                                }

                            }
                        } else {
                            $data = array();
                            $data['rma_id'] = $request->getId();
                            $data['item_id'] = $key;
                            $data['qty_to_return'] = intval($value);
                            $data['amount'] = $product->getData('row_total_incl_tax');
                            try {
                                $item->setData($data);
                                $item->save();
                            } catch (Exception $e) {
                                die($e->getMessage());
                            }
                        }

                    }
                }

                //add comment for RMA
                $msg_data = array();
                $msg_data['rma_id'] = $request->getId();
                $msg_data['customer_id'] = Mage::getSingleton('admin/session')->getUser()->getId();
                $msg_data['customer_name'] = Mage::getSingleton('admin/session')->getUser()->getName();
                $msg_data['customer_email'] = Mage::getSingleton('admin/session')->getUser()->getEmail();
                $msg_data['content'] = $this->__('Your request has been approved successful.');
                $msg_data['created_time'] = Mage::getModel('core/date')->date('Y-m-d H:i:s');
                $comment = Mage::getModel('rma/comment');
                try {
                    $comment->setData($msg_data);
                    $comment->setId(null)->save();

                    $result['message'] = 'Created new RMA request for order #' . $order_id . ' successful.';
                    $result['error'] = false;
                } catch (Exception $e) {
                    $result['message'] = $e->getMessage();
                    $result['error'] = true;
                }
            } catch (Exception $e) {
                $result['message'] = 'Fatal Error: ' . $e->getMessage();
                $result['error'] = true;
            }
        }
        //echo "fdajfk";die;
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function checkReturnOrderAction()
    {
        $result = array();

////        if(strlen($this->getRequest()->getPost('order_id')) == 13) // EAN13
////        {
////            $order_id = doubleval(substr($this->getRequest()->getPost('order_id'), 0, -1));
////        }
////        else
////        {
////            $order_id = doubleval($this->getRequest()->getPost('order_id'));
////        }
//
////        if (intval(Mage::getStoreConfig("barcode/product/conversion") == 1))
////        {
//        if (in_array(intval(Mage::getStoreConfig("barcode/general/symbology")), array(0, 7)))
//            $order_id = $this->getRequest()->getPost('order_id');
//        else
//            $order_id = $this->getRequest()->getPost('order_id');
////        }
////        else
////        {
////            $order_id = intval($this->getRequest()->getPost('order_id'));
////        }

        /*XBA-XBAR-745*/
        $order_id = $this->processOrderIdBySymbology();

        if (strlen($order_id) > 9){
            $order_id=str_replace('-','',$order_id);
            $order_id = substr($order_id, 0, 9) . "-" . substr($order_id,9);
        }

        $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
        if (!$order || !$order->getId()) {
            $result['message'] = 'Order #' . $order_id . ' not found';
            $result['error'] = true;
        } else {
            $shipments = $order->getShipmentsCollection();
            $orderStatus = $order->getStatus();

            if (count($shipments->getData()) === 0) {
                $result['message'] = 'Order #' . $order_id . ' does not have any shipments.';
                $result['error'] = true;
            } else if ($orderStatus == 'closed') {
                $result['message'] = 'Order #' . $order_id . ' has been closed.';
                $result['error'] = true;
            } else if (!$order->canCreditmemo()) {
                $result['message'] = 'Order #' . $order_id . ' can not be returned.';
                $result['error'] = true;
            } else {
                $result['error'] = false;
                $result['order_id'] = $order_id;
                $items = Mage::getModel('sales/order_item')->getCollection()
                    ->addFieldToSelect('product_id')
                    ->addFieldToFilter('order_id', $order->getId())
                    ->addFieldToFilter('qty_ordered', array('gt' => 0))
                    ->addFieldToFilter('qty_shipped', array('gt' => 0))
//                        ->addFieldToFilter('product_type', 'simple')
                    //->addFieldToFilter('qty_shipped',array('gt' => 0))
                ;
                $product_ids = array();
                foreach ($items as $item) {
                    $product_qty[$item->getProductId()] = $item->getQtyOrdered();
                    $product_ids[] = (int)$item->getProductId();
                }
                $result['product_ids'] = $product_ids;
            }
        }

        // check valid duration
        $result['valid_duration'] = true;

        $shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')
            ->setOrderFilter($order)
            ->load();
        $latestShipmentDate = null;
        foreach ($shipmentCollection as $shipment) {
            $tmpDate = strtotime($shipment->getUpdatedAt());
            if ($latestShipmentDate == null)
                $latestShipmentDate = $tmpDate;
            if ($tmpDate > $latestShipmentDate)
                $latestShipmentDate = $tmpDate;
        }
        $validDuration = Mage::getStoreConfig('barcode/rma/valid_duration'); //get valid duration from config
        $validDuration = $validDuration * 24 * 60 * 60; // convert to seconds
        if ($latestShipmentDate != null) {
            $latestShipmentDate = (date('m/d/y h:i:s', Mage::getModel('core/date')->timestamp($latestShipmentDate)));
            $validDate = date('m/d/y h:i:s', Mage::getModel('core/date')->timestamp(time() - $validDuration));
            if (strtotime($latestShipmentDate) < strtotime($validDate)) {
                // order is valid for RMA => add to array+
                $result['valid_duration'] = false;
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    protected function createShipment($orderId, $data)
    {
        if ($shipment = $this->_initShipment($orderId, $data)) {
            $shipment->register();
            $shipment->setEmailSent(false);
            $shipment->getOrder()->setCustomerNoteNotify(false);
            $this->_saveShipment($shipment);
            return true;
        } else {
            return false;
        }
    }

    /**
     * FUnction to return order to bulk- manage order
     * Author: NamLX
     */
    public function checkReturnOrderBulkAction()
    {
        $result = array();

        /*XBA-XBAR-743*/
        $order_id = $this->processOrderIdBySymbology();

        if (strlen($order_id) > 9){
            $order_id = str_replace('-','',$order_id);
            $order_id = substr($order_id, 0, 9) . "-" . substr($order_id,9);
        }

        $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
        if (!$order || !$order->getId()) {
            $result['message'] = 'Order #' . $order_id . ' not found';
            $result['error'] = true;
        } else {
            $shipments = $order->getShipmentsCollection();
//            if (count($shipments->getData()) === 0) {
//                $result['message'] = 'Order #' . $order_id . ' hasn\'t any shipments.';
//                $result['error'] = true;
//            } else {
            $result['error'] = false;
            $result['order_id'] = $order_id;
            $items = Mage::getModel('sales/order_item')->getCollection()
                ->addFieldToSelect('product_id')
                ->addFieldToFilter('order_id', $order->getId())
                ->addFieldToFilter('product_type', 'simple')//->addFieldToFilter('qty_shipped',array('gt' => 0))
            ;
            $product_ids = array();
            foreach ($items as $item) {
                $product_qty[$item->getProductId()] = $item->getQtyOrdered();
                $product_ids[] = (int)$item->getProductId();
            }
            $result['product_ids'] = $product_ids;
            //  }
        }

        // check valid duration
        $result['valid_duration'] = true;

        $shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')
            ->setOrderFilter($order)
            ->load();
        $latestShipmentDate = null;
        foreach ($shipmentCollection as $shipment) {
            $tmpDate = strtotime($shipment->getUpdatedAt());
            if ($latestShipmentDate == null)
                $latestShipmentDate = $tmpDate;
            if ($tmpDate > $latestShipmentDate)
                $latestShipmentDate = $tmpDate;
        }
        $validDuration = Mage::getStoreConfig('barcode/rma/valid_duration'); //get valid duration from config
        $validDuration = $validDuration * 24 * 60 * 60; // convert to seconds
        if ($latestShipmentDate != null) {
            $latestShipmentDate = (date('m/d/y h:i:s', Mage::getModel('core/date')->timestamp($latestShipmentDate)));
            $validDate = date('m/d/y h:i:s', Mage::getModel('core/date')->timestamp(time() - $validDuration));
            if ($latestShipmentDate < $validDate) {
                // order is valid for RMA => add to array+
                $result['valid_duration'] = false;
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Save shipment and order in one transaction
     * @param Mage_Sales_Model_Order_Shipment $shipment
     */
    protected function _saveShipment($shipment)
    {
        $shipment->getOrder()->setIsInProcess(true);
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($shipment)
            ->addObject($shipment->getOrder())
            ->save();

        return $this;
    }
    /**
     * check symbology
     */
    public function processOrderIdBySymbology(){

        /*XBAR-743*/
        /*EAN13-UPC-B*/
        if (intval(Mage::getStoreConfig("barcode/product/conversion") == 1)) {
            if (in_array(intval(Mage::getStoreConfig("barcode/general/symbology")), array(0, 7))) {
                $order_id = $this->getRequest()->getPost('order_id');
                if (strlen($order_id) > 11) {
                    $order_id = substr($this->getRequest()->getPost('order_id'), 0, -1);
                    if ($order_id[1] > 0) {
                        $order_id = substr($order_id, 1);
                    } else {
                        $order_id = substr($order_id, 2);
                    }
                }
            } else {
                $order_id = $this->getRequest()->getPost('order_id');
            }

            /*CODE128-CODE39-I125*/
            if (in_array(intval(Mage::getStoreConfig("barcode/general/symbology")), array(1, 2, 3, 4, 5))) {
                if (strlen($this->getRequest()->getPost('order_id')) > 11) {
                    $key = $this->getRequest()->getPost('order_id');
                    if ($key[2] > 0) {
                        $order_id = substr($this->getRequest()->getPost('order_id'), 2);
                    } else {
                        $order_id = substr($this->getRequest()->getPost('order_id'), 3);
                    }
                }
            }
            //if (strlen($order_id) > 9)
            if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $order_id)) {
                $order_id = str_replace('-', '', $order_id);
            }
            if (strlen($order_id) > 9)
                $order_id = substr($order_id, 0, 9) . "-" . substr($order_id, 9);

        }
        return $order_id;

    }






    /**
     * Initialize shipment model instance
     *
     * @return Mage_Sales_Model_Order_Shipment
     */
    protected function _initShipment($orderId, $data)
    {
        $shipment = false;
        $order = Mage::getModel('sales/order')->load($orderId);

        /**
         * Check order existing
         */
        if (!$order->getId()) {
            return false;
        }
        /**
         * Check shipment is available to create separate from invoice
         */
        if ($order->getForcedDoShipmentWithInvoice()) {
            return false;
        }
        /**
         * Check shipment create availability
         */
        if (!$order->canShip()) {
            return false;
        }
        $savedQtys = $this->_getItemQtys($data);
        $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($savedQtys);

        return $shipment;
    }

    public function submitCommentAction()
    {
        $result = array();

        if ($data = $this->getRequest()->getPost()) {
            $rma_id = intval($data['id']);
            $request = Mage::getModel('rma/request')->load($rma_id);

            $msg_data = array();
            $msg_data['rma_id'] = $request->getId();
            $msg_data['customer_id'] = Mage::getSingleton('admin/session')->getUser()->getId();
            $msg_data['customer_name'] = Mage::getSingleton('admin/session')->getUser()->getName();
            $msg_data['customer_email'] = Mage::getSingleton('admin/session')->getUser()->getEmail();
            $msg_data['content'] = $data['comment'];
            $msg_data['created_time'] = Mage::getModel('core/date')->date('Y-m-d H:i:s');
            $comment = Mage::getModel('rma/comment');
            try {
                $comment->setData($msg_data);
                $comment->setId(null)->save();

                $result['error'] = false;
                $result['content'] = $data['comment'];
                $result['customer_name'] = $msg_data['customer_name'];
                $result['created_time'] = Mage::helper('core')->formatDate($msg_data['created_time'], 'medium', true);
            } catch (Exception $e) {
                $result['error'] = $e->getMessage();
            }
        } else {
            $result['error'] = 'UNKNOW ERROR';
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Initialize shipment items QTY
     */
    protected function _getItemQtys($data)
    {
        if (isset($data['items'])) {
            $qtys = $data['items'];
        } else {
            $qtys = array();
        }
        return $qtys;
    }

}