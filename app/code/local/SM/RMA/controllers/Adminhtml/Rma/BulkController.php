<?php

class SM_RMA_Adminhtml_Rma_BulkController extends SM_Barcode_Controller_Adminhtml_Action {

    private $_isBackToStock = false;
    private $_numOfRefundSs;
    public function gridAction() {
        $this->loadLayout(false)
            ->renderLayout();
    }

    public function indexAction() {
        if ($this->_validated) {
            $this->_title($this->__('RMA'))
                ->_title($this->__('Bulk - Manage order'));

            $this->loadLayout()
                ->_setActiveMenu('smartosc/rma_bulk')
                ->renderLayout();
        }
    }
    protected function _initOrder($orderId)
    {
//        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);

        if (!$order->getId()) {
            $this->_getSession()->addError($this->__('This order no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        Mage::register('sales_order', $order);
        Mage::register('current_order', $order);
        return $order;
    }



    public function cancelAction()
    {
       // $this->getResponse()->setBody("fdkajkgj");
        $id = $this->getRequest()->getParam('order_id');
        if ($order = $this->_initOrder($id)) {
            if($order->canCancel()){
                try {
                    $order->cancel()
                        ->save();
                    $this->_getSession()->addSuccess(
                        $this->__('The order has been cancelled.')
                    );
                }
                catch (Mage_Core_Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
                catch (Exception $e) {
                    $this->_getSession()->addError($this->__('The order has not been cancelled.'));
                    Mage::logException($e);
                }
            }
        }
    }

    /**
     * Cancel selected orders
     */
    public function massCancelAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $countCancelOrder = 0;
        $countNonCancelOrder = 0;
        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->canCancel()) {
                $order->cancel()
                    ->save();
                $countCancelOrder++;
            } else {
                $countNonCancelOrder++;
            }
        }
        if ($countNonCancelOrder) {
            if ($countCancelOrder) {
                $this->_getSession()->addError($this->__('%s order(s) cannot be canceled', $countNonCancelOrder));
            } else {
                $this->_getSession()->addError($this->__('The order(s) cannot be canceled'));
            }
        }
        if ($countCancelOrder) {
            $this->_getSession()->addSuccess($this->__('%s order(s) have been canceled.', $countCancelOrder));
        }
        $this->_redirect('*/*/');
    }


    // invoice order
    public function invoiceAction()
    {
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->loadByIncrementId($id);
        if($order->canInvoice()){
            $items = $order->getItemsCollection();

            $qtys = array(); //this will be used for processing the invoice

            foreach($items as $item){

                $qty_to_invoice = $item->getQtyOrdered(); //where x is the amount you wish to invoice

                $qtys[$item->getId()] = $qty_to_invoice;

            }

            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($qtys);

            $amount = $invoice->getGrandTotal();
            $invoice->register()->pay();
            $invoice->getOrder()->setIsInProcess(true);

            $history = $invoice->getOrder()->addStatusHistoryComment(
                'Partial amount of $' . $amount . ' captured automatically.', false
            );

            $history->setIsCustomerNotified(true);

            $order->save();

            Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder())
                ->save();
            $invoice->save();
            $invoice->sendEmail(true, ''); //set this to false to not send the invoice vi

        }

    }


    /**
     * Atempt to void the order payment
     */
    public function voidPaymentAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $countInvoiceOrder =0;
        $countNonInvoiceOrder =0;
        foreach($orderIds as $id){
            $order = Mage::getModel('sales/order')->load($id);
            if($order->canInvoice()){
                $items = $order->getItemsCollection();

                $qtys = array(); //this will be used for processing the invoice

                foreach($items as $item){

                    $qty_to_invoice = $item->getQtyOrdered(); //where x is the amount you wish to invoice

                    $qtys[$item->getId()] = $qty_to_invoice;

                }

                $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($qtys);

                $amount = $invoice->getGrandTotal();
                $invoice->register()->pay();
                $invoice->getOrder()->setIsInProcess(true);

                $history = $invoice->getOrder()->addStatusHistoryComment(
                    'Partial amount of $' . $amount . ' captured automatically.', false
                );

                $history->setIsCustomerNotified(true);

                $order->save();

                Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder())
                    ->save();
                $invoice->save();
                $invoice->sendEmail(true, ''); //set this to false to not send the invoice vi

            }
        }


//        if ($countNonInvoiceOrder) {
//            if ($countInvoiceOrder) {
//                $this->_getSession()->addError($this->__('%s order(s) cannot be voided', $countNonInvoiceOrder));
//            } else {
//                $this->_getSession()->addError($this->__('The order(s) cannot be voided'));
//            }
//        }
//        if ($countInvoiceOrder) {
//            $this->_getSession()->addSuccess($this->__('%s order(s) have been voided.', $countInvoiceOrder));
//        }
        $this->_redirect('*/*/');




       // $this->_redirect('*/*/view', array('order_id' => $order->getId()));
    }

    /**
     * Generate shipments grid for ajax request
     */
    public function shipAction()
    {
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($id);
        if($order->canShip()){
            $itemQty =  $order->getItemsCollection()->count();
            $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($itemQty);
            $shipment = new Mage_Sales_Model_Order_Shipment_Api();
            $shipmentId = $shipment->create( $order->getIncrementId(), array(), 'Shipment created through ShipMailInvoice', true, true);

//add tracking info
            $shipment_collection = Mage::getResourceModel('sales/order_shipment_collection');
            $shipment_collection->addAttributeToFilter('order_id', $id);
            foreach($shipment_collection as $sc)
            {
                $shipment = Mage::getModel('sales/order_shipment');
                $shipment->load($sc->getId());
                if($shipment->getId() != '')
                {
                    try
                    {
                        Mage::getModel('sales/order_shipment_track')
                            ->setShipment($shipment)
                            ->setData('title', 'carrier')
                            ->setData('number', "track_info")
                            ->setData('carrier_code', 'custom')
                            ->setData('order_id', $shipment->getData('order_id'))
                            ->save();

                    }catch (Exception $e)
                    {
                        Mage::getSingleton('core/session')->addError('order id '.$id.' no found');
                    }
                }
            }
// change order status to complete
            //$order->addStatusToHistory(Mage_Sales_Model_Order::STATE_COMPLETE);
            //$order->setData('state', Mage_Sales_Model_Order::STATE_COMPLETE);
            $order->save();
        }
    }


    public function massShipOrderAction()
    {
        //Get orderids
        //$orderIds = $this->getRequest()->getPost('order_ids');
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        if (!empty($orderIds)) {
            foreach ($orderIds as $orderId) {
                $order = Mage::getModel('sales/order')->load($orderId);
                if($order->canShip()){
                    $itemQty =  $order->getItemsCollection()->count();
                    $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($itemQty);
                    $shipment = new Mage_Sales_Model_Order_Shipment_Api();
                    $shipmentId = $shipment->create( $order->getIncrementId(), array(), 'Shipment created through ShipMailInvoice', true, true);

//add tracking info
                    $shipment_collection = Mage::getResourceModel('sales/order_shipment_collection');
                    $shipment_collection->addAttributeToFilter('order_id', $orderId);
                    foreach($shipment_collection as $sc)
                    {
                        $shipment = Mage::getModel('sales/order_shipment');
                        $shipment->load($sc->getId());
                        if($shipment->getId() != '')
                        {
                            try
                            {
                                Mage::getModel('sales/order_shipment_track')
                                    ->setShipment($shipment)
                                    ->setData('title', 'carrier')
                                    ->setData('number', "track_info")
                                    ->setData('carrier_code', 'custom')
                                    ->setData('order_id', $shipment->getData('order_id'))
                                    ->save();

                            }catch (Exception $e)
                            {
                                Mage::getSingleton('core/session')->addError('order id '.$orderId.' no found');
                            }
                        }
                    }
// change order status to complete
                    //$order->addStatusToHistory(Mage_Sales_Model_Order::STATE_COMPLETE);
                    //$order->setData('state', Mage_Sales_Model_Order::STATE_COMPLETE);
                    $order->save();
                }

//                if ($order->canShip()) {
//                    $itemQty = $order->getItemsCollection()->count();
//
//                    $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($itemQty);
//                    $shipment = new Mage_Sales_Model_Order_Shipment_Api();
//                    //$shipmentId = $shipment->create($orderId, array(), 'Shipment created through ShipMailInvoice', true, true);
//                    $shipmentId = $shipment->create($order->getorderIncrementID, $itemQty, 'Shipment created through ShipMailInvoice', true, true);
//                }
            }
        }
        $this->_redirect('*/*/');
    }


    // Refund action
    public function refundAction()
    {
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($id);
        foreach ($order->getInvoiceCollection() as $invoice) {
            if ($invoice->canRefund()) {
                $data= array();
                foreach($order->getAllItems() as $item){
                    $data[]= array(
                        'qtys' => array(
                            $item->getProductId()=>1,
                        ),
                    );
                }
                $service = Mage::getModel('sales/service_order', $order);
                $creditMemo = $service->prepareCreditmemo($data);
                $creditMemo->refund();
                $creditMemo->save();

//                    $state = 'closed';
//                    $status = 'my_closed_status';
//                    $comment = 'Changing state to closed and status to My closed Status';
//                    $isCustomerNotified = false;
//                    $order->setState($state, $status, $comment, $isCustomerNotified);
                $order->setData('status','closed');
                $order->setData('state',Mage_Sales_Model_Order::STATE_CLOSED);
                $order->save();
            }
        }
    }


    // Mass refund action
    public function massRefundOrderAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            foreach ($order->getInvoiceCollection() as $invoice) {
                if ($invoice->canRefund()) {
                    $data= array();
                    foreach($order->getAllItems() as $item){
                        $ship = $item;
                        $shipped = $item->getQtyShipped();
                        $refunded = $item->getQtyRefunded();
                        $data[]= array(
                            'qtys' => array(
                            $item->getProductId()=>1,
                            ),
                        );
                    }
                    $service = Mage::getModel('sales/service_order', $order);
                    $creditMemo = $service->prepareCreditmemo($data);
                    $creditMemo->refund();
                    $creditMemo->save();

//                    $state = 'closed';
//                    $status = 'my_closed_status';
//                    $comment = 'Changing state to closed and status to My closed Status';
//                    $isCustomerNotified = false;
//                    $order->setState($state, $status, $comment, $isCustomerNotified);
                    $order->setData('status','closed');
                    $order->setData('state',Mage_Sales_Model_Order::STATE_CLOSED);
                    $order->save();
                }
            }

//            $invoices = array();
//            foreach ($order->getInvoiceCollection() as $invoice) {
//                if ($invoice->canRefund()) {
//                    $invoices[] = $invoice;
//                }
//            }
//            $service = Mage::getModel('sales/service_order', $order);
//            foreach ($invoices as $invoice) {
//
//                $creditmemo = $service->prepareInvoiceCreditmemo($invoice);
//                $creditmemo->refund();
//                $creditmemo->save();
//               // $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_COMPLETE);
//
//               // $order->setData('state',Mage_Sales_Model_Order::STATE_CLOSED);
//                $order=Mage::getModel('sales/order')->loadByIncrementId($orderId);
//                $order->setState(Mage_Sales_Model_Order::STATE_CLOSED, true)->save();
//                $order->save();
//                $this->_getSession()->addSuccess($this->__(' order %s have been refund.'));
//            }
        }
        $this->_getSession()->addSuccess('Refund successfully!');
    }

    /*Mass refund action:
     Fix :
    - has option back to stock or not
    - show items refunded in order
    */

    public function massRefundOrderNotReturnAction()
    {
        $this->_isBackToStock = false;
        $this->_numOfRefundSs = 0;
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        foreach ($orderIds as $orderId) {
            $this->refundOrder($orderId);
        }
        $this->_getSession()->addSuccess('Refund '.$this->_numOfRefundSs .' order successfully!');
    }

    public function massRefundOrderReturnAction()
    {
        $this->_isBackToStock = true;
        $this->_numOfRefundSs = 0;
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        foreach ($orderIds as $orderId) {
            $this->refundOrder($orderId);
        }
        $this->_getSession()->addSuccess('Refund '.$this->_numOfRefundSs .' order successfully!');
    }

    private function refundOrder($orderId)
    {
        $creditmemo = $this->_initCreditmemoForRefund($orderId,true);
        if ($creditmemo) {
            if (($creditmemo->getGrandTotal() <= 0) && (!$creditmemo->getAllowZeroGrandTotal())) {
                Mage::throwException(
                    $this->__('Credit memo\'s total must be positive.')
                );
            }

            $creditmemo->register();

            $this->_saveCreditmemo($creditmemo);
            $this->_numOfRefundSs += 1;
        }
        /*
        return;
        try {
            $creditmemo = $this->_initCreditmemoForRefund($orderId,true);
            if ($creditmemo) {
                if (($creditmemo->getGrandTotal() <= 0) && (!$creditmemo->getAllowZeroGrandTotal())) {
                    Mage::throwException(
                        $this->__('Credit memo\'s total must be positive.')
                    );
                }


                $creditmemo->register();

                $this->_saveCreditmemo($creditmemo);
                $this->_numOfRefundSs += 1;
                return;
            }
//            else {
//                $this->_forward('noRoute');
//                return;
//            }
        } catch (Mage_Core_Exception $e) {
//            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
//            Mage::logException($e);
//            $this->_getSession()->addError($this->__('Cannot save the credit memo.'));
        }
        */
    }

    private function _initCreditmemoForRefund($orderId,$isBackToStock)
    {
        $isBackToStock = $this->_isBackToStock;
        $order = Mage::getModel('sales/order')->load($orderId);
        if (!$this->_canCreditmemo($order)) {
            return false;
        }

        $data = array();
        $backToStock = array();
        foreach ($order->getAllItems() as $item) {
            $shipped = $item->getQtyShipped();
            $refunded = $item->getQtyRefunded();
            $qtyToRf = $shipped - $refunded;
            $data[] = array(
                'qtys' => array(
                    $item->getProductId() => $qtyToRf,
                ),
            );
            if ($isBackToStock) {
                $orderItemId = $item->getProductId();
                $backToStock[$orderItemId] = true;
            }
        }

        $service = Mage::getModel('sales/service_order', $order);
        $creditmemo = $service->prepareCreditmemo($data);

        if ($isBackToStock) {
            /**
             * Process back to stock flags
             */
            foreach ($creditmemo->getAllItems() as $creditmemoItem) {
                $orderItem = $creditmemoItem->getOrderItem();
                $creditmemoItem->setBackToStock(true);
            }
        }

        $args = array('creditmemo' => $creditmemo, 'request' => $this->getRequest());
        Mage::dispatchEvent('adminhtml_sales_order_creditmemo_register_before', $args);

        Mage::unregister('current_creditmemo');
        Mage::register('current_creditmemo', $creditmemo);
        return $creditmemo;
    }
    private function _canCreditmemo($order)
    {
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
     *
     * @param array $dif array that contains my product information with qty and product_id and item_id
     * @param type $info array("order_increment_id" => $order->getIncrementId(), "invoice_id" => $invoiceId);
     * @return type
     */
    protected function creaDevolucio($dif, $info){
        $qtys = array();

        foreach ($dif as $item) {
            if (isset($item['qty'])) {
                $qtys[$item['order_item_id']] = array("qty"=> $item['qty']);
            }
            if (isset($item['back_to_stock'])) {
                $backToStock[$item['order_item_id']] = true;
            }
        }

        $data = array(
            "items" => $qtys,
            "do_offline" => "1",
            "comment_text" => "",
            "shipping_amount" => "0",
            "adjustment_positive" => "0",
            "adjustment_negative" => "0",
        );
        if (!empty($data['comment_text'])) {
            Mage::getSingleton('adminhtml/session')->setCommentText($data['comment_text']);
        }

        try {
            $creditmemo = $this->_initCreditmemo($data, $info);
            if ($creditmemo) {
                if (($creditmemo->getGrandTotal() <=0) && (!$creditmemo->getAllowZeroGrandTotal())) {
                    Mage::throwException(
                        $this->__('Credit memo\'s total must be positive.')
                    );
                }

                $comment = '';
                if (!empty($data['comment_text'])) {
                    $creditmemo->addComment(
                        $data['comment_text'],
                        isset($data['comment_customer_notify']),
                        isset($data['is_visible_on_front'])
                    );
                    if (isset($data['comment_customer_notify'])) {
                        $comment = $data['comment_text'];
                    }
                }

                if (isset($data['do_refund'])) {
                    $creditmemo->setRefundRequested(true);
                }
                if (isset($data['do_offline'])) {
                    $creditmemo->setOfflineRequested((bool)(int)$data['do_offline']);
                }

                $creditmemo->register();
                if (!empty($data['send_email'])) {
                    $creditmemo->setEmailSent(true);
                }

                $creditmemo->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                $this->_saveCreditmemo($creditmemo);
                $creditmemo->sendEmail(!empty($data['send_email']), $comment);
                echo '<br>The credit memo has been created.';
                Mage::getSingleton('adminhtml/session')->getCommentText(true);
                return;
            } else {
                //$this->_forward('noRoute');
                //return;
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            Mage::getSingleton('adminhtml/session')->setFormData($data);
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($this->__('Cannot save the credit memo.'));
        }
    }



    /**
     *
     * @param type $data contains products info to refund
     * @param type $info array("order_increment_id" => $order->getIncrementId(), "invoice_id" => $invoiceId);
     * @param type $update
     * @return boolean
     */
    protected function _initCreditmemo($data, $info, $update = false)
    {
        $creditmemo = false;
        $invoice=false;
        $creditmemoId = null;//$this->getRequest()->getParam('creditmemo_id');
        $orderId = $info['order_increment_id'];//$this->getRequest()->getParam('order_id');
        $invoiceId = $data['invoice_id'];
        echo "<br>abans if. OrderId: ".$orderId;
        if ($creditmemoId) {
            $creditmemo = Mage::getModel('sales/order_creditmemo')->load($creditmemoId);
        } elseif ($orderId) {
            $order  = Mage::getModel('sales/order')->loadByIncrementId($orderId);
            if ($invoiceId) {
                $invoice = Mage::getModel('sales/order_invoice')
                    ->load($invoiceId)
                    ->setOrder($order);
                echo '<br>loaded_invoice_number: '.$invoice->getId();
            }

            if (!$order->canCreditmemo()) {
                echo '<br>cannot create credit memo';
                if(!$order->isPaymentReview())
                {
                    echo '<br>cannot credit memo Payment is in review';
                }
                if(!$order->canUnhold())
                {
                    echo '<br>cannot credit memo Order is on hold';
                }
                if(abs($order->getTotalPaid()-$order->getTotalRefunded())<.0001)
                {
                    echo '<br>cannot credit memo Amount Paid is equal or less than amount refunded';
                }
                if($order->getActionFlag('edit') === false)
                {
                    echo '<br>cannot credit memo Action Flag of Edit not set';
                }
                if ($order->hasForcedCanCreditmemo()) {
                    echo '<br>cannot credit memo Can Credit Memo has been forced set';
                }
                return false;
            }

            $savedData = array();
            if (isset($data['items'])) {
                $savedData = $data['items'];
            } else {
                $savedData = array();
            }

            $qtys = array();
            $backToStock = array();
            foreach ($savedData as $orderItemId =>$itemData) {
                if (isset($itemData['qty'])) {
                    $qtys[$orderItemId] = $itemData['qty'];
                }
                if (isset($itemData['back_to_stock'])) {
                    $backToStock[$orderItemId] = true;
                }
            }
            $data['qtys'] = $qtys;

            $service = Mage::getModel('sales/service_order', $order);
            if ($invoice) {
                $creditmemo = $service->prepareInvoiceCreditmemo($invoice, $data);
            } else {
                $creditmemo = $service->prepareCreditmemo($data);
            }

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

        return $creditmemo;
    }




    /**
     * Save creditmemo and related order, invoice in one transaction
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     */
    protected function _saveCreditmemo($creditmemo)
    {
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($creditmemo)
            ->addObject($creditmemo->getOrder());
        if ($creditmemo->getInvoice()) {
            $transactionSave->addObject($creditmemo->getInvoice());
        }
        $transactionSave->save();

        return $this;
    }


}

