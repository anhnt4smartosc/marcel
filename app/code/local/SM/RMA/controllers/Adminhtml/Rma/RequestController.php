<?php

class SM_RMA_Adminhtml_Rma_RequestController extends SM_Barcode_Controller_Adminhtml_Action {

    public function indexAction() {
        if ($this->_validated) {
            $this->_title($this->__('RMA'))
                ->_title($this->__('Request List'));

            $this->loadLayout()
                ->_setActiveMenu('smartosc/rma/rma_requests')
                ->renderLayout();
        }
    }

    public function gridAction() {
        $this->loadLayout(false)
            ->renderLayout();
    }

    public function grideditAction() {
        $this->loadLayout(false)
            ->renderLayout();
    }

    public function gridexchangeAction() {
        $this->loadLayout(false)
            ->renderLayout();
    }

    public function addxitemAction() {
        $data = $this->getRequest()->getPost();
        //var_dump($data);
        if ($data['id']) {

            $item = Mage::getModel('rma/exchangeitem');
            $collection = $item->getCollection()
                ->addFieldToSelect('*')
                ->addFieldToFilter('rma_id', $data['rmaid'])
                ->addFieldToFilter('item_id', $data['id'])
                ->load();
            if ($collection->count()) {
                // update qty to existing record
                foreach ($collection as $item)
                    $item->setQtyToExchange(($item->getQtyToExchange() + 1))->save();
            } else {
                // new record
                $item = Mage::getModel('rma/exchangeitem');
                $item->setItemId($data['id']);
                $item->setRmaId($data['rmaid']);
                $item->setQtyToExchange(1);
                $item->setDone(0);
                $item->setLastLog('hehe');
                $item->save();
            }
        }
    }

    public function approveAction() {
        if ($this->_validated) {
            $this->_title($this->__('RMA'))
                ->_title($this->__('Approve Request'));

            $this->loadLayout()
                ->_setActiveMenu('rma/rma_approve')
                ->renderLayout();
        }
    }

    public function editAction()
    {
        $rmaRequest = Mage::getModel('rma/request')
            ->load($this->getRequest()->getParam('id'));
        if (!$rmaRequest->getId()) {
            $this->_redirect('*/*');
            return;
        }
        $rmaItemCollection = Mage::getModel('rma/item')->getCollection()
            ->addFieldToFilter('rma_id', $this->getRequest()->getParam('id'));
        Mage::register('rma_request_data', $rmaRequest);
        Mage::register('rma_item_collection_data', $rmaItemCollection);
        $this->_prepareCreditmemo($rmaRequest->getOrderId(), $rmaItemCollection);
        $this->_title($this->__('RMA'))->_title($this->__('Process Request'));

        $this->loadLayout()->_setActiveMenu('rma');
        $this->_addContent($this->getLayout()->createBlock('rma/adminhtml_request_edit'))
            ->_addLeft($this->getLayout()->createBlock('rma/adminhtml_request_edit_tabs'));

        $this->renderLayout();
    }

    protected function _prepareCreditmemo($orderId, $rmaItemCollection)
    {

        $order = Mage::getModel('sales/order')->load($orderId);
        $orderService = Mage::getModel('sales/service_order', $order);
        $data = array();
        $data['qtys'] = array();

        foreach ($rmaItemCollection as $item) {
            $_qtyToRefund = $item->getQtyToReturn();
            $_item = Mage::getModel('sales/order_item')->load($item->getId());
            $_allowQtyToRefund = (int)($_item->getQtyOrdered()) - (int)($_item->getQtyRefunded());

            if ($_qtyToRefund < $_allowQtyToRefund) {
                if (!$item->getDone()) {
                    $data['qtys'][$item->getItemId()] = $item->getQtyToReturn();
                }
            }
        }



        $creditmemo = $orderService->prepareCreditmemo($data);

        Mage::register('creditmemo_data', $creditmemo);

    }

    /**
     * Check if creditmeno can be created for order
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    protected function _canCreditmemo($order) {
        /**
         * Check order existing
         */
        if (!$order->getId()) {
            $this->_getSession()->addError($this->__('The order no longer exists.'));
            return false;
        }

        /**
         * Check creditmemo create availability
         */
        if (!$order->canCreditmemo()) {
            $this->_getSession()->addError($this->__('Cannot create credit memo for the order.'));
            return false;
        }
        return true;
    }

    /**
     * Initialize requested invoice instance
     * @param unknown_type $order
     */
//    protected function _initInvoice($order) {
//
//        $collection = Mage::getResourceModel('sales/order_invoice_grid_collection')
//                        ->setOrderFilter($order);
//
//        foreach ($collection->getItems() as $invoice) {
//            $invoice->setOrder($order);
////            var_dump($invoice);die;
//
//            if ($invoice->getId()) {
//                return $invoice;
//            }
//        }
//
//        return false;
//    }

    /**
     * Save creditmemo and related order, invoice in one transaction
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     */
    protected function _saveCreditmemo($creditmemo) {
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($creditmemo)
            ->addObject($creditmemo->getOrder());
        if ($creditmemo->getInvoice()) {
            $transactionSave->addObject($creditmemo->getInvoice());
        }
        $transactionSave->save();

        return $this;
    }

    protected function _initCreditmemo($items, $fix)
    {

        foreach ($items as $item => $value) {
            $itemInfo = Mage::getModel('sales/order_item')->load($item);
            $data['items']["{$itemInfo->getItemId()}"] = array('back_to_stock' => $value['update_stock'],
                'qty' => $value['qty']);
        }
        $creditmemo = false;
        $orderId = $itemInfo->getOrderId();
        if ($orderId) {
//            $data['do_offline'] = 0;
            $data['comment_text'] = 'Online Refund';
//            $data['shipping_amount'] = 0;
//            $data['adjustment_positive'] = 0;//$amount; //0;
            $data['adjustment_negative'] = $fix; //0;
            /*if create creditmemo will go here*/
            $order = Mage::getModel('sales/order')->load($orderId);

            if (!$this->_canCreditmemo($order)) {
                return false;
            }

            if (isset($data['items'])) {
                $qtys = $data['items'];
            } else {
                $qtys = array();
            }

            $savedData = $qtys;

            $qtys = array();
            $backToStock = array();
            foreach ($savedData as $orderItemId => $itemData) {
                if (isset($itemData['qty'])) {
                    $qtys[$orderItemId] = $itemData['qty'];
                }
                if (isset($itemData['back_to_stock'])) {
                    $backToStock[$orderItemId] = true;
                }
            }
            $data['qtys'] = $qtys;

            $service = Mage::getModel('sales/service_order', $order);
            $creditmemo = $service->prepareCreditmemo($data);


            /**
             * Process back to stock flags
             */
            foreach ($creditmemo->getAllItems() as $creditmemoItem) {
                $orderItem = $creditmemoItem->getOrderItem();
                $parentId = $orderItem->getParentItemId();
                if (isset($backToStock[$orderItem->getId()])) {
                    $creditmemoItem->setBackToStock(true);
                } elseif ($orderItem->getParentItem() && isset($backToStock[$parentId]) && $backToStock[$parentId]) {
                    $creditmemoItem->setBackToStock(true);
                } elseif (empty($savedData)) {
                    $creditmemoItem->setBackToStock(Mage::helper('cataloginventory')->isAutoReturnEnabled());
                } else {
                    $creditmemoItem->setBackToStock(false);
                }
            }
        }

        $args = array('creditmemo' => $creditmemo, 'request' => $this->getRequest());
        Mage::dispatchEvent('adminhtml_sales_order_creditmemo_register_before', $args);

        Mage::register('current_creditmemo', $creditmemo);
        return $creditmemo;
    }

    protected function _processRefund($items, $amount, $fix)
    {

        $data['do_offline'] = 0;
        $data['comment_text'] = 'Online Refund';
        $data['shipping_amount'] = 0;
        $data['adjustment_positive'] = 0;//$amount; //0;
        $data['adjustment_negative'] = $fix; //0;
        try {
            $creditmemo = $this->_initCreditmemo($items, $fix);
            $data['do_offline'] = 0;
            $data['comment_text'] = 'Online Refund';
            $data['shipping_amount'] = 0;
            $data['adjustment_positive'] = 0;//$amount; //0;
            $data['adjustment_negative'] = $fix; //0;

            if ($creditmemo) {
                if (($creditmemo->getGrandTotal() <= 0) && (!$creditmemo->getAllowZeroGrandTotal())) {
                    Mage::throwException(
                        $this->__('Credit memo\'s total must be positive.')
                    );
                }

                $comment = '';
                if (!empty($data['comment_text'])) {
                    $creditmemo->addComment($data['comment_text'], isset($data['comment_customer_notify']));
                    if (isset($data['comment_customer_notify'])) {
                        $comment = $data['comment_text'];
                    }
                }

//                if (isset($data['do_refund'])) {
//                    $creditmemo->setRefundRequested(true);
//                }
//                if (isset($data['do_offline'])) {
//                    $creditmemo->setOfflineRequested((bool) (int) $data['do_offline']);
//                }


                $payment = $creditmemo->getOrder()->getPayment();
                Mage::dispatchEvent('sm_rma_process_refund_at_gateway', array('payment' => $payment, 'amount' => $amount));

                $creditmemo->register();
                if (!empty($data['send_email'])) {
                    $creditmemo->setEmailSent(true);
                }

                $creditmemo->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                $this->_saveCreditmemo($creditmemo);
                $creditmemo->sendEmail(!empty($data['send_email']), $comment);
//                $this->_getSession()->addSuccess($this->__('Your payment has been refunded (offline).'));
                Mage::getSingleton('adminhtml/session')->getCommentText(true);
                return true;
            } else {
                $this->_getSession()->addError($this->__('Cannot create credit memo.'));
                return false;
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($this->__('Cannot save the credit memo.'));
        }
        return false;
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {

            $handling_fee_default = Mage::getStoreConfig("barcode/rma/handling_fee_default");
            // echo "<pre>";
            //  var_dump($data);
            $rma = Mage::getModel('rma/request')->load(intval($data['id']));
            $order = Mage::getModel('sales/order')->load(intval($rma->getOrderId()));
            $storeId = $order->getStoreId();

            $statusOrder = $data['status'];

            if (isset($data['set_status_only']) && isset($data['status']))
            {
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The status has been changed successfully.'));
            }
            elseif (isset($data['request_type']) && count($data['request_type']) > 0)
            {
                if($statusOrder == "approved" || $statusOrder == "resolved_refund" || $statusOrder == "resolved_exchange_refund")
                {
                    $request_value = $data['request_value'];

                    $gift_amount = 0;
                    $refundAmount = 0;
                    $refundFix = 0;

                    $refundIds = array();
                    $giftIds = array();
                    $giftProductIds = array();
                    $isExchange = false;
                    $adjusment_fee =0;

                    if ($data['request_type']) // request item type reject 0, refund 1 or exchange 3
                        foreach ($data['request_type'] as $item_id => $type) {
                            $rmaItemId = intval($data['rma_items'][$item_id]);
                            $rma_item = Mage::getModel('rma/item')->load($rmaItemId);
                            $qty = intval($rma_item->getQtyToReturn());

                            $item = Mage::getModel('sales/order_item')->load(intval($item_id));
                            // $product_type  = $item->getProductType();

                            $old_product = Mage::getModel('catalog/product')->load(intval($item->getProductId()));
                            if (($type == 1 || $type == 3)) { // REFUND OPTION  , 3 = + exchange
                                $handling_fee = array_key_exists('handling_fee', $request_value[$item_id]) ? floatval($request_value[$item_id]['handling_fee']) : $handling_fee_default;
                                $refundItems[$item_id]['update_stock'] = intval(@$data['update_stock'][$item_id]);
                                $refundItems[$item_id]['qty'] = $qty;
                                $refundItems[$item_id]['updated_qty'] = $old_product->getStockItem()->getQty() + $qty;
                                $refundAmount += floatval($request_value[$item_id][1]);
                                $refundFix += floatval($item->getPriceInclTax()) * $qty;
                                $adjusment_fee+=$handling_fee*$qty;
                                $refundIds[$item_id]['item_id'] = $rmaItemId;
                                $refundIds[$item_id]['type'] = $type;
                                $refundIds[$item_id]['update_stock'] = intval(@$data['update_stock'][$item_id]);
                                $refundIds[$item_id]['amount'] = floatval($request_value[$item_id][1]);
                            }
                            if ($type == 3) { // REFUND + EXCHANGE OPTION
                                // process refund
                                $isExchange = true;
                            } elseif ($type == 0) { // REJECT
                                $rejectItems[] = $rmaItemId;
                            }
                        } // END FOREACH ITEM




                    if ($gift_amount > 0) { //generate gift certificate
                        $config = Mage::getStoreConfig('ugiftcert/default');
                        $autoSend = Mage::getStoreConfig('ugiftcert/email/auto_send', $storeId);
                        $changeStatus = Mage::getStoreConfig('ugiftcert/default/active_on_payment', $storeId);

                        $cert = Mage::getModel('ugiftcert/cert')
                            ->setId(null)
                            ->setStatus('A')
                            ->setBalance($gift_amount)
                            ->setCurrencyCode($order->getOrderCurrencyCode())
                            ->setStoreId($storeId)
                            ->setRecipientEmail($order->getCustomerEmail());
                        //->setRecipientEmail('thaiht@smartosc.com');
                        if ($order->getCustomerIsGuest()) {
                            $cert->setRecipientName('Guest');
                        } else {
                            $cert->setRecipientName($order->getCustomerFirstname() . ' ' . $order->getCustomerLastname());
                        }
                        if ($config['auto_cert_number']) {
                            $cert->setCertNumber($config['cert_number']);
                        }
                        if ($config['auto_pin']) {
                            $cert->setPin($config['pin']);
                        }
                        if (($days = intval($config['expire_timespan']))) {
                            $cert->setExpireAt(date('Y-m-d', time() + $days * 86400));
                        }

                        $history_data = array(
                            'user_id' => Mage::getSingleton('admin/session')->getUser()->getId(),
                            'username' => Mage::getSingleton('admin/session')->getUser()->getUsername(),
                            'ts' => now(),
                            'amount' => $gift_amount,
                            'currency_code' => $order->getOrderCurrencyCode(),
                            'status' => 'A',
                            'comments' => 'Gift Certificate for RMA',
                            'action_code' => 'create',
                        );
                        $history_data['order_increment_id'] = $order->getIncrementId();
                        $history_data['order_id'] = $order->getId();
                        $history_data['customer_id'] = $order->getCustomerId();
                        $history_data['customer_email'] = $order->getCustomerEmail();

                        try {
                            $cert->save();
                            $cert->addHistory($history_data);
                            //send email to customer
                            Mage::helper('ugiftcert')->sendRefundEmail($cert->getId(), $order);

                            //update rma item status
                            if (isset($giftIds) && count($giftIds) > 0) {
                                foreach ($giftIds as $id) {
                                    $this->updateRMAItemStatus($id, 'Created Gift Certificate', 1, 0); // update gift
                                }
                            }

                            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Gift certificate for RMA was successfully saved'));
                        } catch (Exception $e) {
                            die($e->getMessage());
                        }
                    } // END generate gift certificate
                    echo "<pre>";

                    if ($refundAmount > 0 && count($refundItems) > 0) {  // Refund process
                        //if ($this->_processRefund($refundItems, $refundAmount, $refundFix)) {
                        if ($this->_processRefund($refundItems, $refundAmount, $adjusment_fee)) {
                            foreach ($refundIds as $id => $itemData) {
                                $this->updateRMAItemStatus($itemData['item_id'], Mage::getModel('rma/request')->getRequestItemTypeName($itemData['type']), $itemData['update_stock'], $itemData['amount']);
                            }
                        }

                        /*it's fixed below*/
                        //Work around because the credit memo does not return qty properly.
//                        foreach ($refundItems as $itemId => $itemData) {
//                            $item = Mage::getModel('sales/order_item')->load(intval($item_id));
//                            $product = Mage::getModel('catalog/product')->load(intval($item->getProductId()));
//
//                            if ($itemData['update_stock']) {
//                                $stockData['qty'] = $itemData['updated_qty'];
//                                $stockData['is_in_stock'] = ($stockData['qty'] > 0) ? 1 : 0;
//                                $product->setStockData($stockData);
//                                $product->save();
//                            }
//                        }
                    }

                    //update rma item status
                    if (isset($rejectItems) && count($rejectItems) > 0) {
                        foreach ($rejectItems as $id) {
                            $this->updateRMAItemStatus($id, Mage::getModel('rma/request')->getRequestItemTypeName(0), 0, 0);
                        }
                    }

                    // process exchange
                    if ($isExchange && $data['xrequest_type']) {
                        $ids = array();
                        foreach ($data['xrequest_type'] as $item_id => $qty) {
                            //var_dump($item_id); die;
                            $msg = "Rejected";
                            if ($data['rma_exchangeitemsqty'][$item_id]) {
                                $xqty = $data['rma_exchangeitemsqty'][$item_id];
                            } else {
                                $xqty = $qty;
                            }

                            if ($xqty > 10000) {
                                $message = $this->__('The maximum allowed quantity for purchase is 10000.');
                                Mage::getSingleton('core/session')->addError($message);
                                $this->_redirect('*/*/edit', array(
                                    'id' => $rma->getId(),
                                    '_current' => true
                                ));
                                return;
                            }

                            if ($qty > 0) {
                                $msg = "Accepted";
                                $ids[$item_id] = $xqty;
                            }

                            $exchange_amount = floatval($data['rma_exchangeitemsprice'][$item_id]) * $xqty;

                            //var_dump(); die;

                            // update record
                            $xitemid = $data['rma_exchangeitems'][$item_id];
                            $xitem = Mage::getModel('rma/exchangeitem')->load($xitemid);
                            //var_dump($xitem); die;
                            $xitem->setQtyToExchange($xqty);
                            $xitem->setDone(1);
                            $xitem->setLastLog($msg);
                            $xitem->setAmount($exchange_amount);
                            $xitem->save();
                        }
                        // create exchange order
                        if (count($ids) > 0)
                            $this->createExchangeOrder($order, $ids, $rma);
                    }
                }
            }
            else
            {
                //die('No items selected');
            }

            /* it's fixed below*/
            //set status for order

//            if($statusOrder == "approved" || $statusOrder == "resolved_refund" || $statusOrder == "resolved_exchange_refund")
//            {
//                $orderedItems = $order->getAllItems();
//
//                $full_refuned = 0;
//
//                foreach ($orderedItems as $orderedItem) {
//                    if($orderedItem->getProductType()=="bundle" || $orderedItem->getProductType()=="configurable"){
//                        if($orderedItem->getQtyShipped()!=$orderedItem->getQtyRefunded()){
//                            $full_refuned++;
//                        }
//                    }
//                    else{
//                        if($orderedItem->getParentItemId()){
//
//                        }
//                        else{
//                            if($orderedItem->getQtyShipped()!=$orderedItem->getQtyRefunded()){
//                                $full_refuned++;
//                            }
//                        }
//                    }
//
//
////                    if($orderedItem->getQtyRefunded() != $orderedItem->getQtyShipped())
////                    {
////                        $full_refuned++;
////                    }
//                }
//
//                if($full_refuned == 0)
//                {
//                    $order->setStatus("closed");
//                    $order->setData('state', "closed");
//                    $order->save();
//                }
//            }

            //update RMA status
            $rma->setStatus($data['status']);
            try {
                $rma->save();
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/edit', array(
            'id' => $rma->getId(),
            '_current' => true
        ));
    }

    public function createExchangeOrder($order, $ids, $rma) {
        $quote = Mage::getModel('sales/quote')
            ->setStoreId($order->getStoreId());
        $billAdd = Mage::getModel('sales/order_address')->load($order->getBillingAddressId());
        $shipAdd = Mage::getModel('sales/order_address')->load($order->getShippingAddressId());
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId(1)
            ->loadByEmail($order->getCustomerEmail());
        $quote->assignCustomer($customer);

// add product(s)
        foreach ($ids as $key => $value) {
            $product = Mage::getModel('catalog/product')->load($key);
            $buyInfo = array(
                'qty' => $value,
                // custom option id => value id
// or
// configurable attribute id => value id
            );
            $quote->addProduct($product, $value);
        }

        $billingAddress = $quote->getBillingAddress()->addData($billAdd->getData());
        $shippingAddress = $quote->getShippingAddress()->addData($shipAdd->getData());

        $shippingAddress->setCollectShippingRates(true)->collectShippingRates()
            ->setShippingMethod($order->getShippingMethod())
            ->setPaymentMethod($order->getPayment()->getMethodInstance()->getCode());

        $quote->getPayment()->importData(array('method' => $order->getPayment()->getMethodInstance()->getCode()));

        $quote->collectTotals()->save();

        $service = Mage::getModel('sales/service_quote', $quote);
        $service->submitAll();
        $orderExchange = $service->getOrder();
        // add comment
        $comment = 'Order created to exchange products from order #' . $order->getIncrementId() . ' based on RMA request #' . $rma->getId() . '.';
        $orderExchange->addStatusHistoryComment($comment);
        $orderExchange->save();
        // send order email 
        $orderExchange->sendOrderUpdateEmail(true, $comment);

        return true;
    }

    public function updateRMAItemStatus($rmaItemId, $msg, $update_stock, $amount) {
        $rma_item = Mage::getModel('rma/item')->load($rmaItemId);
        $rma_item->setDone(1);
        $rma_item->setLastLog($msg);
        $rma_item->setUpdateStock($update_stock);
        $rma_item->setAmount($amount);
        try {
            $rma_item->save();
        } catch (Exception $e) {
            die($e->getMessage());
        }
        return true;
    }

    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore(4);
    }

    protected function _updateProductStock($_product, $type = 'INC', $qty = 0) {
        if ($_product->getId()) {
            $stockData = $_product->getStockData();
            if ($type == 'INC') {
                $newQty = intval($_product->getStockItem()->getQty()) + $qty;
            } else {
                $newQty = intval($_product->getStockItem()->getQty()) - $qty;
            }

            $stockData['qty'] = $newQty;
            if ($newQty > 0) {
                $stockData['is_in_stock'] = 1;
            } else {
                $stockData['is_in_stock'] = 0;
            }

            $_product->setStockData($stockData);
            try {
                $_product->save();
            } catch (Exception $e) {
                die($e->getMessage());
            }
        }
    }

}
