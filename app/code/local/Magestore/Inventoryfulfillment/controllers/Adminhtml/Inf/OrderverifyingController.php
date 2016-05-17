<?php

/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryfulfillment
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventoryfulfillment Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryfulfillment
 * @author      Magestore Developer
 */
class Magestore_Inventoryfulfillment_Adminhtml_Inf_OrderverifyingController extends Magestore_Inventoryfulfillment_Controller_Fulfillment {

    /**
     * Show verifying step
     * 
     */
    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Show verifying step
     * 
     */
    public function listAction() {
        $this->loadLayout();
        $this->_setActiveMenu('inventoryplus/fulfillment/verify');
        $this->_title($this->__('Inventory Management'))
                ->_title($this->__('Order & Stock Availability Verification'));
        $this->getLayout()->getBlock('header_title')->setTitle($this->_helper()->__('Order Fulfillment - Order & Stock Availability Verification'));
        $this->renderLayout();
    }

    /**
     * Grid action
     */
    public function gridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('inventoryfulfillment/adminhtml_fulfillment_verifying')->toHtml()
        );
    }

    public function orderdetailsAction() {
        $orderId = $this->getRequest()->getParam('order_id');
        $this->_initOrder($orderId);
        $this->_initSession();
        $blockHtml = $this->getLayout()->createBlock('inventoryfulfillment/adminhtml_fulfillment_verifying_view', '', array('order_id' => $orderId))->toHtml();
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('return_html' => $blockHtml)));
    }

    /**
     * Cancel order
     * 
     */
    public function cancelAction() {
        $orderId = $this->getRequest()->getParam('order_id');
        if ($order = $this->_initOrder($orderId)) {
            try {
                $order->cancel()
                        ->save();
            } catch (Mage_Core_Exception $e) {
                $return = array('message' => $e->getMessage(), 'error' => 1);
            } catch (Exception $e) {
                $return = array('message' => $this->__('The order has not been cancelled.'), 'error' => 1);
                Mage::logException($e);
            }
            $return = array('message' => $this->__('The order has been cancelled.'));
        } else {
            $return = array('message' => $this->__('Order not found.'), 'error' => 1);
        }
        
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($return));
    }

    /**
     * Verify order & pick items
     * 
     */
    public function pickAction() {
        $orderId = $this->getRequest()->getParam('order_id');
        $isNotifyEmail = $this->getRequest()->getParam('is_notify_email');
        $remainQty = $this->getRequest()->getParam('remainqty');
        $selectedwarehouses = $this->getRequest()->getParam('selectedwarehouses');
        parse_str($selectedwarehouses, $selectedwarehouses);

        try {
            $order = $this->_initOrder($orderId);

            Mage::dispatchEvent('inventoryfulfillment_order_pick_before', array('order' => $order, 'is_notify_email' => $isNotifyEmail));

            /* create fulfill packages */
            $package = Mage::getModel('inventoryfulfillment/package');
            if (count($selectedwarehouses)) {
                foreach ($order->getAllItems() as $item) {
                    if (isset($selectedwarehouses[$item->getId()])) {
                        $whStocks = explode('-', $selectedwarehouses[$item->getId()]);
                        if (isset($whStocks[0]) && $whStocks[0] && isset($whStocks[1]) && $whStocks[1])
                            $package->addItem($item, $whStocks[1], $whStocks[0]);
                    } elseif ($item->getParentItemId()) {
                        if (isset($selectedwarehouses[$item->getParentItemId()])) {
                            $whStocks = explode('-', $selectedwarehouses[$item->getId()]);
                            if (isset($whStocks[0]) && $whStocks[0] && isset($whStocks[1]) && $whStocks[1])
                                $package->addItem($item, $whStocks[1], $whStocks[0]);
                        }
                    }
                }
            }
            if ($order->getFulfillmentStatus() == 0) {
                $return = array('message' => $this->_helper()->__('The order has been verified successfully!'));
            } else {
                $return = array('message' => $this->_helper()->__('The picking item request has been submitted!'));
            }
            /* update order status */
            if ($remainQty == 0) {
                $order->setFulfillmentStatus(Magestore_Inventoryfulfillment_Model_Order_Fulfillmentstatus::STATUS_PICKED);
                $order->save();
            } elseif ($order->getFulfillmentStatus() == 0) {
                $order->setFulfillmentStatus(Magestore_Inventoryfulfillment_Model_Order_Fulfillmentstatus::STATUS_VERIFIED);
                $order->save();
            }
            Mage::dispatchEvent('inventoryfulfillment_order_pick_after', array('order' => $order, 'is_notify_email' => $isNotifyEmail));

            /* reload view order screen in verify list */
            if ($remainQty > 0) {
                $return['reload'] = 1;
            }
        } catch (Exception $ex) {
            $return = array('message' => $ex->getMessage(), 'error' => 1);
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($return));
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('inventoryplus/fulfillment/verify');
    }

    /**
     * Submit order action
     */
    public function postorderAction() {
        $orderId = $this->getRequest()->getParam('order_id');
        $comment = $this->getRequest()->getParam('comment');
        $selectedMethod = $this->getRequest()->getParam('selectedmethod');
        if ($order = $this->_initOrder($orderId)) {
            try {
                $response = array();
                $data = array('comment' => $comment, 'status' => $order->getStatus());
                $notify = isset($data['is_customer_notified']) ? $data['is_customer_notified'] : false;
                $visible = isset($data['is_visible_on_front']) ? $data['is_visible_on_front'] : false;
                if ($data['comment']) {
                    $order->addStatusHistoryComment($data['comment'], $data['status'])
                            ->setIsVisibleOnFront($visible)
                            ->setIsCustomerNotified($notify);
                }
                if ($order->getShippingMethod() != $selectedMethod) {
                    $order->setShippingMethod($selectedMethod);
                    $order->setShippingDescription($this->_helper()->getShippingDescription($selectedMethod));
                }
                $order->save();

                $comment = trim(strip_tags($data['comment']));
                $order->sendOrderUpdateEmail($notify, $comment);

                $this->loadLayout('empty');
                $response = array('orderHistory' => $this->getLayout()->getBlock('order_comment_history')->toHtml(),
                    'message' => $this->_helper()->__('Order has been updated successfully!'));
            } catch (Mage_Core_Exception $e) {
                $response = array(
                    'error' => true,
                    'message' => $e->getMessage(),
                );
            } catch (Exception $e) {
                $response = array(
                    'error' => true,
                    'message' => $this->_helper()->__('Cannot add order history.')
                );
            }
            if (is_array($response)) {
                $response = Mage::helper('core')->jsonEncode($response);
                $this->getResponse()->setBody($response);
            }
        }
    }

    /**
     * Initialize order creation session data
     *
     */
    protected function _initSession() {
        $this->_getSession()->setQuoteId($this->_getOrderCreateModel()->getQuoteId());
        $this->_getSession()->setCustomerId($this->_getOrderCreateModel()->getCustomerId());
        $this->_getSession()->setStoreId($this->_getOrderCreateModel()->getStoreId());
        $this->_getQuote()->setShippingMethod($this->_getOrderCreateModel()->getShippingMethod());
        $this->_getQuote()->getShippingAddress()->setShippingMethod($this->_getOrderCreateModel()->getShippingMethod());
        return $this;
    }

    /**
     * Retrieve session object
     *
     * @return Mage_Adminhtml_Model_Session_Quote
     */
    protected function _getSession() {
        return Mage::getSingleton('adminhtml/session_quote');
    }

    /**
     * Retrieve order create model
     *
     * @return Mage_Adminhtml_Model_Sales_Order_Create
     */
    protected function _getOrderCreateModel() {
        return Mage::registry('current_order');
    }

    /**
     * Retrieve quote object
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote() {
        return $this->_getSession()->getQuote();
    }

}
