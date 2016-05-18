<?php

class SM_RMA_CustomerController extends Mage_Core_Controller_Front_Action {

    /**
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession() {
        return Mage::getSingleton('customer/session');
    }

    public function indexAction() {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('My RMA Requests'));

        $this->renderLayout();
    }

    public function newAction() {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');

        $data = $this->_getSession()->getCustomerFormData(true);
        $customer = $this->_getSession()->getCustomer();
        if (!empty($data)) {
            $customer->addData($data);
        }

        $this->getLayout()->getBlock('head')->setTitle($this->__('New RMA Request'));
        if ($navigationBlock = $this->getLayout()->getBlock('customer_account_navigation')) {
            $navigationBlock->setActive('rma/customer');
        }


        $this->renderLayout();
    }

    public function saveAction() {
        $session = $this->_getSession();
        if (!$session->isLoggedIn()) {
            $this->_redirect('*/');
            return;
        }

        $session->setEscapeMessages(true); // prevent XSS injection in user input


        $customer = Mage::getModel('customer/customer')
                ->setId($this->_getSession()->getCustomerId())
                ->setWebsiteId($this->_getSession()->getCustomer()->getWebsiteId());

        if ($post = $this->getRequest()->getPost()) {
            $order = Mage::getModel('sales/order')->load(intval($post['order']));
            if (!$order || !$order->getId()) {
                $this->_redirect('*/*/');
                return;
            }


            //verify quantity
            if (isset($post['items']) && count($post['items']) > 0) {
                foreach ($post['items'] as $value) {
                    $_item = Mage::getModel('sales/order_item')->load(intval(intval($value)));
                    if ($_item->getParentItemId()) {
                        $_parent = Mage::getModel('sales/order_item')->load(intval($_item->getParentItemId()));
                        if (($_parent->getQtyRefuned() + intval($post['qtys'][$value])) > $_parent->getQtyShipped()) {
                            $session->setCustomerFormData($post);
                            $session->addError('Qty of item #' . $value . ' is out of qty shipped.');
                            $this->_redirect('*/*/new');
                            return;
                        }
                    } else {
                        if (($_item->getQtyRefuned() + intval($post['qtys'][$value])) > $_item->getQtyShipped()) {
                            $session->setCustomerFormData($post);
                            $session->addError('Qty of item #' . $value . ' is out of qty shipped.');
                            $this->_redirect('*/*/new');
                            return;
                        }
                    }
                }
                $data = array();
                $data['store_id'] = Mage::app()->getStore()->getStoreId();
                $data['order_id'] = $order->getId();
                $data['order_increment_id'] = $order->getIncrementId();
                $data['customer_id'] = $this->_getSession()->getCustomer()->getId();
                $data['customer_name'] = $this->_getSession()->getCustomer()->getName();
                $data['customer_email'] = $this->_getSession()->getCustomer()->getEmail();
                $data['package_opened'] = intval($post['package_opened']);
                $data['request_type'] = intval($post['request_type']);
                $data['status'] = SM_RMA_Model_Request::STATUS_PENDING_APPROVAL;
                $data['created_time'] = Mage::getModel('core/date')->date('Y-m-d H:i:s');
                $request = Mage::getModel('rma/request')->setId(null);

                try {
                    $request->setData($data);
                    $request->save();

                    //save request items
                    $item = Mage::getModel('rma/item')->setId(null);
                    foreach ($post['items'] as $value) {
                        if (intval($post['qtys'][$value]) > 0) {
                            $data2 = array();
                            $data2['rma_id'] = $request->getId();
                            $data2['item_id'] = $value;
                            $data2['qty_to_return'] = intval($post['qtys'][$value]);
                            try {
                                $item->setData($data2);
                                $item->save();
                            } catch (Exception $e) {
                                $session->setCustomer($customer)
                                        ->addError($e->getMessage());
                            }
                        }
                        else{
                            $session->setCustomerFormData($post);
                            $session->addError($this->__('You are about to submit an exchange request without selecting a product. Please search and select a product to exchange.'));
                            $this->_redirect('*/*/new');
                            return;
                        }
                    }
                    // request type is exchange
                    if ($data['request_type'] == 1) {
                        if (isset($post['xitems']) && count($post['xitems']) > 0) {
                            //save exchange items
                            $item = Mage::getModel('rma/exchangeitem')->setId(null);
                            foreach ($post['xitems'] as $key => $value) {
                                if (intval($value) > 0) {
                                    $data = array();
                                    $data['rma_id'] = $request->getId();
                                    $data['item_id'] = $key;
                                    $data['qty_to_exchange'] = intval($value);
                                    try {
                                        $item->setData($data);
                                        $item->save();
                                    } catch (Exception $e) {
                                        $session->setCustomer($customer)
                                                ->addError($e->getMessage());
                                    }
                                }
                                else{
                                    $session->setCustomerFormData($post);
                                    $session->addError($this->__('You are about to submit an exchange request without selecting a product. Please search and select a product to exchange.'));
                                    $this->_redirect('*/*/new');
                                    return;
                                }
                            }
                        }
                        else{
                            $session->setCustomerFormData($post);
                            $session->addError($this->__('No specific product is selected for exchange, please confirm if you wish to submit request.'));
                            $this->_redirect('*/*/new');
                            return;
                        }
                    }
                    if (isset($post['comment']) && $post['comment'] != '') {
                        //update comments
                        $msg_data = array();
                        $msg_data['rma_id'] = $request->getId();
                        $msg_data['customer_id'] = $this->_getSession()->getCustomer()->getId();
                        $msg_data['customer_name'] = $this->_getSession()->getCustomer()->getName();
                        $msg_data['customer_email'] = $this->_getSession()->getCustomer()->getEmail();
                        $msg_data['content'] = $post['comment'];
                        $msg_data['created_time'] = Mage::getModel('core/date')->date('Y-m-d H:i:s');
                        $comment = Mage::getModel('rma/comment');
                        try {
                            $comment->setData($msg_data);
                            $comment->setId(null)->save();
                        } catch (Exception $e) {
                            $session->setCustomerFormData($post);
                            $session->addError($e->getMessage());
                            $this->_redirect('*/*/new');
                            return;
                        }
                    }
                    $session->setCustomer($customer)
                            ->addSuccess('Your request has been submited successful.');
                } catch (Exception $e) {
                    $session->setCustomer($customer)
                            ->addError($e->getMessage());
                }

                $this->_redirect('*/*/');
                return;
            } else {
                $session->setCustomerFormData($post);
                $session->addError($this->__('Please, select at least one item.'));
                $this->_redirect('*/*/new');
                return;
            }
        }
    }

    public function viewAction() {
        if (!Mage::getSingleton('customer/session')->getCustomerId()) {
            Mage::getSingleton('customer/session')->authenticate($this);
            return;
        }

        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('RMA Information'));
        if ($navigationBlock = $this->getLayout()->getBlock('customer_account_navigation')) {
            $navigationBlock->setActive('rma/customer');
        }

        $this->renderLayout();
    }

}

