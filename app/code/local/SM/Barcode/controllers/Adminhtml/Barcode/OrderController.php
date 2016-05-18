<?php

/**
 * SmartOSC Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 *
 * @category   SM
 * @package    SM_Barcode
 * @version    2.0
 * @author     hoadx@smartosc.com
 * @copyright  Copyright (c) 2010-2011 SmartOSC Co. (http://www.smartosc.com)
 */
class SM_Barcode_Adminhtml_Barcode_OrderController extends SM_Barcode_Controller_Adminhtml_Action {

    public function indexAction() {
        if ($this->_validated) {
            $this->loadLayout()
                    ->_setActiveMenu('smartosc/barcode_manage')
                    ->renderLayout();
        }
    }

    public function gridAction() {
        $this->loadLayout(false);
        $this->renderLayout();
    }


    // Generate barcode image of each items in order
    public function loadbarcodeimageAction() {
//        if (intval(Mage::getStoreConfig("barcode/product/conversion") == 1))
//        {
            if (in_array(intval(Mage::getStoreConfig("barcode/general/symbology")), array(0, 7)))
                $orderId = intval(substr($this->getRequest()->getPost('order_id'), 0, -1));
            else
                $orderId = intval($this->getRequest()->getPost('order_id'));
//        }
//        else
//        {
//            $orderId = intval($this->getRequest()->getPost('order_id'));
//        }
        $order = Mage::getModel('sales/order')->loadByIncrementID($orderId);
        $result = array();

        if($order->getId()){
            $orderId = intval($order->getId());
        }
        else{
            $orderId = 99999999;
        }
        $collection = Mage::getModel('sales/order_item')->getCollection()
            ->addFieldToFilter('order_id', $orderId);

        $print_arr = array();
        if (count($collection) <= 0) {
            $result['message'] = 'Order does not have any item';
            $result['error'] = true;
        } else {
            $totals = 0;
            foreach($collection as $item) {
                $productId = $item['product_id'];
                $qty = $item['qty_invoiced'];
                $totals += $qty;
                if ($qty > 0)
                $print_arr[$productId] = $qty;

                $errorID = "";
                // Generate barcode image for this product
                if (!Mage::helper('barcode/barcode')->createProductBarcode($productId)) {
                    $errorID = $productId .", ";
                }
                else {
                    $successProducts[] = $productId;
                }
            }

            if ($totals <=0) {
                $result['message'] = 'Order items quantity should be greater than zero';
                $result['error'] = true;
            }

            if(strlen($errorID) > 0)
            {
                $result['message'] = 'Cannot generate barcode for product(s): '. $errorID;
                $result['error'] = true;
            }
        }

        Mage::getSingleton('core/session')->setPrintArr($print_arr);
        $this->getResponse()->setBody(Mage::helper('barcode')->jsonEncode($result));

    }


    /**
     * Print invoices for selected orders
     */
    public function pdfinvoicesAction(){
        $orderIds = $this->getRequest()->getPost('order_ids');
        $flag = false;

        if (!empty($orderIds)) {
            Mage::getModel('barcode/order_pdf_order')->getPdf($orderIds);return;
            foreach ($orderIds as $orderId) {
                $invoices = Mage::getResourceModel('sales/order_invoice_collection')
                    ->setOrderFilter($orderId)
                    ->load();

                if ($invoices->getSize() > 0) {
                    $flag = true;
                    if (!isset($pdf)){
                        $pdf = Mage::getModel('barcode/order_pdf_invoice')->getPdf($invoices);
                    } else {
                        $pages = Mage::getModel('barcode/order_pdf_invoice')->getPdf($invoices);
                        $pdf->pages = array_merge ($pdf->pages, $pages->pages);
                    }
                }
            }
            if ($flag) {
                return $this->_prepareDownloadResponse(
                    'invoice'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(),
                    'application/pdf'
                );
            } else {
                $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
                $this->_redirect('*/sales_order/');
            }
        }
        $this->_redirect('*/sales_order/');
    }


    /**
     * Print shipments for selected orders
     */
    public function pdfshipmentsAction(){
        $orderIds = $this->getRequest()->getPost('order_ids');
        $flag = false;
        if (!empty($orderIds)) {
             Mage::getModel('barcode/order_pdf_packingslips')->getPdf($orderIds);return;
            foreach ($orderIds as $orderId) {
                $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                    ->setOrderFilter($orderId)
                    ->load();
                if ($shipments->getSize()) {
                    $flag = true;
                    if (!isset($pdf)){
                        $pdf = Mage::getModel('barcode/order_pdf_shipment')->getPdf($shipments);
                    } else {
                        $pages = Mage::getModel('barcode/order_pdf_shipment')->getPdf($shipments);
                        $pdf->pages = array_merge ($pdf->pages, $pages->pages);
                    }
                }
            }
            if ($flag) {
                return $this->_prepareDownloadResponse(
                    'packingslip'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(),
                    'application/pdf'
                );
            } else {
                $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
                $this->_redirect('*/sales_order/');
            }
        }
        $this->_redirect('*/sales_order/');
    }

    /**
     * Print creditmemos for selected orders
     */
    public function pdfcreditmemosAction(){
        $orderIds = $this->getRequest()->getPost('order_ids');
        $flag = false;
        if (!empty($orderIds)) {
            foreach ($orderIds as $orderId) {
                $creditmemos = Mage::getResourceModel('sales/order_creditmemo_collection')
                    ->setOrderFilter($orderId)
                    ->load();
                if ($creditmemos->getSize()) {
                    $flag = true;
                    if (!isset($pdf)){
                        $pdf = Mage::getModel('barcode/order_pdf_creditmemo')->getPdf($creditmemos);
                    } else {
                        $pages = Mage::getModel('barcode/order_pdf_creditmemo')->getPdf($creditmemos);
                        $pdf->pages = array_merge ($pdf->pages, $pages->pages);
                    }
                }
            }
            if ($flag) {
                return $this->_prepareDownloadResponse(
                    'creditmemo'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(),
                    'application/pdf'
                );
            } else {
                $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
                $this->_redirect('*/sales_order/');
            }
        }
        $this->_redirect('*/sales_order/');
    }

    /**
     * Print invoices for selected orders
     */
    public function pdforderpickinglistAction(){
        $orderIds = $this->getRequest()->getPost('order_ids');
        $flag = false;
        if (!empty($orderIds)) {
            Mage::getModel('barcode/order_pdf_pickinglist')->getPdf($orderIds);return;
            foreach ($orderIds as $orderId) {
                $invoices = Mage::getResourceModel('sales/order_invoice_collection')
                    ->setOrderFilter($orderId)
                    ->load();

                if ($invoices->getSize() > 0) {
                    $flag = true;
                    if (!isset($pdf)){
                        $pdf = Mage::getModel('barcode/order_pdf_invoice')->getPdf($invoices);
                    } else {
                        $pages = Mage::getModel('barcode/order_pdf_invoice')->getPdf($invoices);
                        $pdf->pages = array_merge ($pdf->pages, $pages->pages);
                    }
                }
            }
            if ($flag) {
                return $this->_prepareDownloadResponse(
                    'invoice'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(),
                    'application/pdf'
                );
            } else {
                $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
                $this->_redirect('*/sales_order/');
            }
        }
        $this->_redirect('*/sales_order/');

    }

    public function pdfitempickinglistAction(){
        $orderIds = $this->getRequest()->getPost('order_ids');
        $flag = false;

        if (!empty($orderIds)) {
            Mage::getModel('barcode/Order_Pdf_ItemPickinglist')->getPdf($orderIds);return;
            foreach ($orderIds as $orderId) {
                $invoices = Mage::getResourceModel('sales/order_invoice_collection')
                    ->setOrderFilter($orderId)
                    ->load();

                if ($invoices->getSize() > 0) {
                    $flag = true;
                    if (!isset($pdf)){
                        $pdf = Mage::getModel('barcode/order_pdf_invoice')->getPdf($invoices);
                    } else {
                        $pages = Mage::getModel('barcode/order_pdf_invoice')->getPdf($invoices);
                        $pdf->pages = array_merge ($pdf->pages, $pages->pages);
                    }
                }
            }
            if ($flag) {
                return $this->_prepareDownloadResponse(
                    'invoice'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(),
                    'application/pdf'
                );
            } else {
                $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
                $this->_redirect('*/sales_order/');
            }
        }
        $this->_redirect('*/sales_order/');

    }

}

