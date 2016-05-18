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
class SM_Barcode_Adminhtml_Barcode_PrintController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        die("Please check your configuration / Input values OR contact your administrator for more details.");
    }

    public function oneproductAction()
    {
        $product_ids = array();
        $data = array();
        if (is_numeric($_GET['qty']) && $_GET['qty'] > 0) {
            $product_ids[$this->getRequest()->getParam('id')] = $_GET['qty'];
            //Create product barcode
            $data['product_ids']['request'] = $product_ids;
            Mage::getSingleton('sales/order_pdf_product')->getPdf($data);
        } else {
            Mage::getSingleton('adminhtml/session')->addError('Please insert quantity is greater than 0.');
            $this->_redirect('*/barcode_product');
        }
    }

    public function multiAction()
    {

        $data = $this->getRequest()->getPost();
        $product_ids = array();
        foreach ($data as $key => $value) {
            if (preg_match("/product_/i", $key)) {
                $product_id = intval(substr($key, 8));
                $qty = intval($value);
                if ($qty > 0) {
                    $product_ids[$product_id] = $qty;
                }

            }
        }
        //end for
        if (count($product_ids) > 0) {
            $data['product_ids']['request'] = $product_ids;
            //Create product barcode
            Mage::getSingleton('sales/order_pdf_product')->getPdf($data);
        } else {
            Mage::getSingleton('catalog/session')->addSuccess($this->__('Can not generate product barcode.'));
            $this->_redirect('*/*/');
        }

    }

    public function showAction()
    {
        $this->loadLayout()->renderLayout();
    }

    public function previewAction()
    {
        $data = $_GET;
        $data['isDebugEnabled'] = 'yes';
        $data['type'] = 'preview';
        $data['product_ids']['request'][$data['id']] = $data['qty'];
        Mage::getSingleton('sales/order_pdf_product')->getPdf($data);
    }

    public function printOrderBarcodeAction()
    {
        $order_id = isset($_GET['order_id']) ? ($_GET['order_id']) : 0;

        /*XBAR-743*/
        /*EAN13 UPC-B*/
        if (intval(Mage::getStoreConfig("barcode/product/conversion") == 1)) {
            if (in_array(intval(Mage::getStoreConfig("barcode/general/symbology")), array(0, 7))) {
                if (strlen($order_id) > 11) {
                    $order_id = substr($order_id, 0, -1);
                    if ($order_id[1] > 0) {
                        $order_id = substr($order_id, 1);
                    } else {
                        $order_id = substr($order_id, 2);
                    }
                }
            } else {
                $order_id = $order_id;
            }

            /*CODE128-CODE39-I125*/
            if (in_array(intval(Mage::getStoreConfig("barcode/general/symbology")), array(1, 2, 3, 4, 5))) {
                if (strlen($order_id) > 11) {
                    $key = $order_id;
                    if ($key[2] > 0) {
                        $order_id = substr($order_id, 2);
                    } else {
                        $order_id = substr($order_id, 3);
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

        $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
        $items = Mage::getModel('sales/order_item')->getCollection()
            ->addFieldToFilter('order_id', $order->getId())
            ->addFieldToFilter('product_type', 'simple');
        $data = array();
        foreach ($items as $item) {
            $data['product_ids']['request'][$item->getProductId()] = $item->getQtyOrdered();
        }
        $data['isDebugEnabled'] = 'no';
        $data['type'] = '';
        Mage::getSingleton('sales/order_pdf_product')->getPdf($data);
    }

    public function orderBarcodeAction(){
        $data = $_GET;
        $data['isDebugEnabled'] = 'yes';
        $data['type'] = 'preview';
        Mage::getModel('barcode/order_pdf_order')->getPdf($data);


    }

}
