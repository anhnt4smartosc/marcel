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
class SM_Barcode_Adminhtml_Barcode_AjaxController extends SM_Barcode_Controller_Adminhtml_Action
{

    public function loadBarcodeImageAction()
    {
        $result = array();
        $product = array();
        //get product barcode
//        $product_id = $this->getRequest()->getPost('order_product_id');

//        if (in_array(intval(Mage::getStoreConfig("barcode/general/symbology")), array(0, 7))) {
//            $product_id = $this->getRequest()->getPost('order_product_id');
//            if (strlen($product_id) > 12) {
//                $product_id = substr($product_id, 0, -1);
//            }
//        }else{
        $product_id = $this->getRequest()->getPost('order_product_id');
//        }
//        var_dump($product_id);
//        die();

        if (intval(Mage::getStoreConfig("barcode/product/conversion") == 1)): // get conversation on /off
            $collection = Mage::getModel('catalog/product')->getCollection();
            $collection->addAttributeToFilter('sm_barcode', array('like' => '%' . $product_id . '%'));
            $collection->load();

            foreach ($collection as $pId) {
                $result['product_id'] = $pId->getId();
                $product_id = $result['product_id'];
                $product = Mage::getModel('catalog/product')->load($result['product_id']);
            }

        else:
//            $write = Mage::getSingleton('core/resource')->getConnection('core_write');
            $attr = Mage::getStoreConfig("barcode/product/barcode_value");
            $attr_val = $product_id;
            //$this->getRequest()->getPost('order_product_id');

            $attributeInfo = Mage::getModel('eav/entity_attribute')->load($attr);
            $sku_code = $attributeInfo->getAttributeCode();

//            if ($sku_code != "sku") {
//                $readresult = $write->query("SELECT `e`.*, `at_name`.`value` AS `name` FROM `catalog_product_entity` AS `e` INNER JOIN `catalog_product_entity_varchar` AS `at_name` ON (`at_name`.`entity_id` = `e`.`entity_id`) AND (`at_name`.`attribute_id` = '" . $attr . "') AND (`at_name`.`store_id` = 0) WHERE at_name.value LIKE '%" . $attr_val . "'");
//                $row = $readresult->fetch();
//                $product_id = $row['entity_id'];
//                $product = Mage::getModel('catalog/product')->load($product_id);
//            } else {
//                $product = Mage::getModel('catalog/product')->loadByAttribute('sku', trim($product_id));
//            }

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


//            if ($product && $product->getId())
//                $product_id = $product->getId();
//            $result['product_id'] = $product_id;

        endif;

        // get symbol conversation
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
//            $order_id = substr($this->getRequest()->getPost('order_id'), 0, -1);
//        else
//            $order_id = $this->getRequest()->getPost('order_id');


        /*XBAR-743*/
        /*EAN13-UPC-B*/
        if (in_array(intval(Mage::getStoreConfig("barcode/general/symbology")), array(0, 7))) {
            $order_id = $this->getRequest()->getPost('order_id');
            if(strlen($order_id)>11){
                $order_id = substr($this->getRequest()->getPost('order_id'), 0, -1);
                $order_id = substr($order_id, 1);
            }
        } else {
            $order_id = $this->getRequest()->getPost('order_id');
        }
        /*CODE128-CODE39-I125*/
        if(in_array(intval(Mage::getStoreConfig("barcode/general/symbology")), array(1,2,3,4,5))){
            if(strlen($this->getRequest()->getPost('order_id'))>11){
                $key = $this->getRequest()->getPost('order_id');
                if($key[2]>0){
                    $order_id = substr($this->getRequest()->getPost('order_id'), 2);
                }else{
                    $order_id = substr($this->getRequest()->getPost('order_id'), 3);
                }
            }
        }


//        }
//        else
//        {
//            $order_id = doubleval($this->getRequest()->getPost('order_id'));
//        }

        $order_id = str_replace('-','',$order_id);
        if (strlen($order_id) > 9 )
            $order_id = substr($order_id, 0, 9) . "-" . substr($order_id, 9);

        $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
        $items = Mage::getModel('sales/order_item')->getCollection()
            //->addFieldToSelect('product_id')
            ->addFieldToFilter('order_id', $order->getId())
            ->addFieldToFilter('product_type', 'simple');
        $product_qty = array();
        $product_ids = array();
        foreach ($items as $item) {
            $product_qty[$item->getProductId()] = $item->getQtyOrdered();
            $product_ids[] = (int)$item->getProductId();
        }
        $result['product_ids'] = $product_ids;

        if (!$product || $product->getName() == '') {
            $result['error'] = Mage::helper('barcode')->__('Product ID is not valid.');
        } else {
            //gen barcode
            Mage::helper('barcode/barcode')->createProductBarcode($product_id);
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
            $result['image_name'] = $product->getName();
            $result['error'] = '';

        }

        $this->getResponse()->setBody(Mage::helper('barcode')->jsonEncode($result));
    }

    public function saveAction()
    {

        $result = array();
        $action = $this->getRequest()->getPost('save_action');


        if (intval(Mage::getStoreConfig("barcode/product/conversion") == 1)) {
            Mage::getModel('core/session')->setOrderOrigin($this->getRequest()->getPost('order_id'));
            $orderOrigin = Mage::getModel('core/session')->getOrderOrigin();

            /*XBAR-743*/
            /*EAN13 UPC-B*/
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

        $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);

        if (!$order || !$order->getId()) {
            $result['message'] = 'Order #' . $order_id . ' not found';
            $result['error'] = true;
        }

        if ($action == 'complete') {
            try {
                //create shipment
                $data = $this->getRequest()->getPost('shipment');
//                if (!$this->createShipment($order, $data)) {
//                    die('cannot create shipment');
//                }
                $this->createShipment($order, $data);
                //////////////////////////////////////////////
                // Fix error cannot change protected state - zzz
                //$order->setState(Mage_Sales_Model_Order::STATE_COMPLETE, Mage_Sales_Model_Order::STATE_COMPLETE);
//                $order->setData('state', Mage_Sales_Model_Order::STATE_COMPLETE);
//                $status = $order->getConfig()->getStateDefaultStatus(Mage_Sales_Model_Order::STATE_COMPLETE);
//                $order->setStatus($status);
                ///////////////////////////////////////////////

//                $order->save();
                $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
                $invoice->register();


                $order->setIsInProcess(true);
                /* Complete the order */
                $order
                    ->setTotalPaid($order->getGrandTotal())
                ;

                Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($order)
                    ->save();
                $this->_getSession()->clear();
                $result['message'] = 'Order #' . $order_id . ' has been changed to completed.';
                $result['error'] = false;
            } catch (Exception $e) {
                $result['message'] = 'Fatal Error: ' . $e->getMessage();
                $result['error'] = true;
            }
        } elseif ($action == 'hold') {
            try {
                if ($order->canHold()) {
                    $order->hold()->save();
                    $result['message'] = 'Order #' . $order_id . ' has been put on hold';
                    $result['error'] = false;
                } else {
                    $result['message'] = 'Order #' . $order_id . ' was not put on hold';
                    $result['error'] = true;
                }
            } catch (Exception $e) {
                $result['message'] = $e->getMessage();
            }
        } elseif ($action == 'backorder') {
            try {
                $result['message'] = 'This function is in processing status';
                $result['error'] = true;
            } catch (Exception $e) {
                $result['message'] = $e->getMessage();
                $result['error'] = true;
            }
        } elseif ($action == 'partial') {
            try {
                //create shipment
                $data = $this->getRequest()->getPost('shipment');
//                if (!$this->createShipment($order, $data)) {
//                    die('cannot create shipment');
//                }
                $this->createShipment($order, $data);
                $result['message'] = 'The shipment of this order has been created';
                $result['error'] = false;
            } catch (Exception $e) {
                $result['message'] = $e->getMessage();
                $result['error'] = true;
            }
        }


        $this->getResponse()->setBody(Mage::helper('barcode')->jsonEncode($result));
    }

    public function checkOrderAction()
    {
        $result = array();


        if (intval(Mage::getStoreConfig("barcode/product/conversion") == 1)) {
            Mage::getModel('core/session')->setOrderOrigin($this->getRequest()->getPost('order_id'));
            $orderOrigin = Mage::getModel('core/session')->getOrderOrigin();

            /*XBAR-743*/
            /*EAN13 UPC-B*/
            if (in_array(intval(Mage::getStoreConfig("barcode/general/symbology")), array(0, 7))) {
                $order_id = $this->getRequest()->getPost('order_id');
                if (strlen($order_id) > 11) {
                    $order_id = substr($this->getRequest()->getPost('order_id'), 0, -1);
                    if ($order_id[1] > 0) {
                        $order_id = substr($order_id, 1);
                        $order_id = substr($order_id, 0, 9) . "-" . substr($order_id,9);
                    } else {
                        $order_id = substr($order_id, 2);
                        $order_id = substr($order_id, 0, 9) . "-" . substr($order_id,9);
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


        $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
        if (!$order || !$order->getId()) {
            $result['message'] = $this->__('Order not found for barcode #' . $orderOrigin);
            $result['error'] = true;
        } elseif ($order->getStatus() != Mage_Sales_Model_Order::STATE_PROCESSING) {
            $result['message'] = $this->__('Order #' .$orderOrigin . ' has status Complete, or invoice is not created');
            $result['error'] = true;
        } else {
            if ($order->getStatus() == Mage_Sales_Model_Order::STATE_COMPLETE) {
                $result['message'] = $this->__('Order #' . $orderOrigin . ' was completed');
                $result['error'] = true;
            } else {
                $result['error'] = false;
                $result['order_id'] = $order_id;
                $items = Mage::getModel('sales/order_item')->getCollection()
                    //->addFieldToSelect('product_id')  // fix compatible with 1.3
                    ->addFieldToFilter('order_id', $order->getId())
                    ->addFieldToFilter('product_type', 'simple');
                $product_ids = array();
                foreach ($items as $item) {
                    $product_qty[$item->getProductId()] = $item->getQtyOrdered();
                    $product_ids[] = (int)$item->getProductId();
                }
                $result['product_ids'] = $product_ids;
            }
        }

        $this->getResponse()->setBody(Mage::helper('barcode')->jsonEncode($result));
    }

    public function checkReturnOrderAction()
    {
        $result = array();
        $order_id = intval($this->getRequest()->getPost('order_id'));
        if (strlen($order_id) > 9 && (strpos($order_id, '-') !== false))
            $order_id = substr($order_id, 0, -1) . "-" . substr($order_id, strlen($order_id) - 1, 1);
        $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
        if (!$order || !$order->getId()) {
            $result['message'] = $this->__('Order #' . $order_id . ' not found');
            $result['error'] = true;
        } else {
            $shipments = $order->getShipmentsCollection();
            if (count($shipments->getData()) === 0) {
                $result['message'] = $this->__('Order #' . $order_id . ' hasn\'t any shipments.');
                $result['error'] = true;
            } else {
                $result['error'] = false;
                $result['order_id'] = $order_id;
                $items = Mage::getModel('sales/order_item')->getCollection()
                    //->addFieldToSelect('product_id')
                    ->addFieldToFilter('order_id', $order->getId())
                    ->addFieldToFilter('product_type', 'simple')
                    ->addFieldToFilter('qty_shipped', array('gt' => 0));
                $product_ids = array();
                foreach ($items as $item) {
                    $product_qty[$item->getProductId()] = $item->getQtyOrdered();
                    $product_ids[] = (int)$item->getProductId();
                }
                $result['product_ids'] = $product_ids;
            }
        }

        $this->getResponse()->setBody(Mage::helper('barcode')->jsonEncode($result));
    }

    protected function createShipment($order, $data)
    {
        if ($shipment = $this->_initShipment($order, $data)) {
            $isSendMail = isset($data['send_email']) ? !!$data['send_email'] : false;
            $shipment->register();
            $shipment->setEmailSent($isSendMail);
            $shipment->getOrder()->setCustomerNoteNotify($isSendMail);
            $this->_saveShipment($shipment);
            $shipment->sendEmail($isSendMail);
            return true;
        } else {
            return false;
        }
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
     * Initialize shipment model instance
     *
     * @return Mage_Sales_Model_Order_Shipment
     */
    protected function _initShipment($order, $data)
    {
        $shipment = false;

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

        if (Mage::helper('barcode')->getCompabilityMode() == '13') { // Compability with 1.3.x
            $shipment = Mage::getModel('barcode/sales_service_order', $order)->prepareShipment($savedQtys);
        } else {
            $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($savedQtys);
        }

        return $shipment;
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


    public function ajaxcreatebarcodepreviewAction()
    {
        $html = '';
        Mage::getSingleton('core/session', array('name' => 'adminhtml'));
        //verify if the user is logged in to the backend
        if (Mage::getSingleton('admin/session')->isLoggedIn()) {
            $_GET['symbology'] = isset($_GET['symbology']) ? $_GET['symbology'] : 4;
            $_GET['barcode'] = isset($_GET['barcode']) ? $_GET['barcode'] : null;
            $_GET['unit'] = isset($_GET['unit']) ? $_GET['unit'] : 'mm';
            $output_unit = 'pt';

            switch ($_GET['barcode']) {

                case 'order':
                    if (is_numeric($_GET['width']) && $_GET['width'] > 0) {
                        $width = Mage::helper('barcode/barcode')->unitConverter($_GET['width'], $_GET['unit'], 'px');
                    } else {
                        $width = 172;
                    }

                    if (is_numeric($_GET['height']) && $_GET['height'] > 0) {
                        $height = Mage::helper('barcode/barcode')->unitConverter($_GET['height'], $_GET['unit'], 'px');
                    } else {
                        $height = 26;
                    }

                    if (is_numeric($_GET['padding_top']) && $_GET['padding_top'] > 0) {
                        $padding_top = Mage::helper('barcode/barcode')->unitConverter($_GET['padding_top'], $_GET['unit'], 'px');
                    } else {
                        $padding_top = 30;
                    }

                    if (is_numeric($_GET['padding_left']) && $_GET['padding_left'] > 0) {
                        $padding_left = Mage::helper('barcode/barcode')->unitConverter($_GET['padding_left'], $_GET['unit'], 'px');
                    } else {
                        $padding_left = 30;
                    }

//                    $order_id = is_numeric($_GET['order_id']) ? $_GET['order_id'] : 123456789123;
                    $order_id = $_GET['order_id'];
                    $include_logo = isset($_GET['include_logo']) ? $_GET['include_logo'] : 0;

                    if ($_GET['symbology'] == 0) { //EAN13
                        $order_id_leng = strlen((string)$order_id);
                        if ($order_id_leng != 12) {
                            if ($order_id_leng < 12) { //Add more
                                $count = 12 - $order_id_leng;
                                if ($count > 0) {
                                    $res = '';
                                    for ($i = 0; $i < $count; $i++) {
                                        $res .= "0";
                                    }
                                    $order_id = $res . $order_id;
                                }
                            } else { // More than 12 char
                                die("You selected [EAN13]: ON and your Order ID is greater than 12 char in lenght.<br /> Please switch to others Barcode Symbology, e.g: Code 39");
                            }
                        }
                    }

                    echo '<img style="border-style:solid;border-width:1px; padding: 2px 2px 2px 2px;" src="data:image/png;base64,' .
                        Mage::helper('barcode/barcode')->createOrderBarcodePreview($_GET['symbology'], $order_id, $width, $height, $include_logo, $padding_top, $padding_left, $_GET['unit']) .
                        '" />';
                    break;
                default:
                    $html = "
                    <script>
                    alert('Invalid value. Please recheck and press Preview again.');
                    </script>
                    Invalid value. Please recheck and press Preview again.
                    ";
                    break;
            }

            return $html;
        }
        //End if isLoggedIn()
    }

    public function ajaxsaveconfigAction()
    {
        // Get all params -> Save to config
        //Not care time
        set_time_limit(0);

        if (isset($_GET['enable'])) Mage::getModel('core/config')->saveConfig('barcode/general/enable', $_GET['enable']);
        if (isset($_GET['key'])) Mage::getModel('core/config')->saveConfig('barcode/general/key', $_GET['key']);
        if (isset($_GET['symbology'])) Mage::getModel('core/config')->saveConfig('barcode/general/symbology', $_GET['symbology']);
        if (isset($_GET['unit'])) Mage::getModel('core/config')->saveConfig('barcode/general/input_size_unit', $_GET['unit']);
        if (isset($_GET['conversion'])) Mage::getModel('core/config')->saveConfig('barcode/product/conversion', $_GET['conversion']);
        if (isset($_GET['value'])) Mage::getModel('core/config')->saveConfig('barcode/product/barcode_value', $_GET['value']);
        if (isset($_GET['field'])) Mage::getModel('core/config')->saveConfig('barcode/product/barcode_field', $_GET['field']);
        if (isset($_GET['source'])) Mage::getModel('core/config')->saveConfig('barcode/product/barcode_source', $_GET['source']);
        if (isset($_GET['orientation'])) Mage::getModel('core/config')->saveConfig('barcode/product/orientation', $_GET['orientation']);
        if (isset($_GET['width']) && $_GET['width'] > 0) Mage::getModel('core/config')->saveConfig('barcode/product/width', $_GET['width']);
        if (isset($_GET['height']) && $_GET['height'] > 0) Mage::getModel('core/config')->saveConfig('barcode/product/height', $_GET['height']);

        if (isset($_GET['barcode_width']) && $_GET['barcode_width'] > 0) Mage::getModel('core/config')->saveConfig('barcode/product/barcode_width', $_GET['barcode_width']);
        if (isset($_GET['barcode_height']) && $_GET['barcode_height'] > 0) Mage::getModel('core/config')->saveConfig('barcode/product/barcode_height', $_GET['barcode_height']);
        if (isset($_GET['barcode_settings'])) Mage::getModel('core/config')->saveConfig('barcode/product/barcode_settings', $_GET['barcode_settings']);

        if (isset($_GET['columns_display'])) Mage::getModel('core/config')->saveConfig('barcode/product/columns_display', $_GET['columns_display']);
        if (isset($_GET['rows_display'])) Mage::getModel('core/config')->saveConfig('barcode/product/rows_display', $_GET['rows_display']);

        if (isset($_GET['margin_top']) && $_GET['margin_top'] >= 0) Mage::getModel('core/config')->saveConfig('barcode/product/page_margin_top', $_GET['margin_top']);
        if (isset($_GET['margin_left']) && $_GET['margin_left'] >= 0) Mage::getModel('core/config')->saveConfig('barcode/product/page_margin_left', $_GET['margin_left']);

        if (isset($_GET['label_margin_top']) && $_GET['label_margin_top'] >= 0) Mage::getModel('core/config')->saveConfig('barcode/product/label_margin_top', $_GET['label_margin_top']);
        if (isset($_GET['label_margin_left']) && $_GET['label_margin_left'] >= 0) Mage::getModel('core/config')->saveConfig('barcode/product/label_margin_left', $_GET['label_margin_left']);
        if (isset($_GET['padding_top']) && $_GET['padding_top'] >= 0) Mage::getModel('core/config')->saveConfig('barcode/product/label_padding_top', $_GET['padding_top']);
        if (isset($_GET['padding_bottom']) && $_GET['padding_bottom'] >= 0) Mage::getModel('core/config')->saveConfig('barcode/product/label_padding_bottom', $_GET['padding_bottom']);
        if (isset($_GET['padding_left']) && $_GET['padding_left'] >= 0) Mage::getModel('core/config')->saveConfig('barcode/product/label_padding_left', $_GET['padding_left']);
        if (isset($_GET['padding_right']) && $_GET['padding_right'] >= 0) Mage::getModel('core/config')->saveConfig('barcode/product/label_padding_right', $_GET['padding_right']);

        if (isset($_GET['include_logo'])) Mage::getModel('core/config')->saveConfig('barcode/product/include_logo', $_GET['include_logo']);
        if (isset($_GET['logo_settings'])) Mage::getModel('core/config')->saveConfig('barcode/product/logo_settings', $_GET['logo_settings']);
        if (isset($_GET['logo_width']) && $_GET['logo_width'] > 0) Mage::getModel('core/config')->saveConfig('barcode/product/logo_width', $_GET['logo_width']);
        if (isset($_GET['logo_height']) && $_GET['logo_height'] > 0) Mage::getModel('core/config')->saveConfig('barcode/product/logo_height', $_GET['logo_height']);

        if (isset($_GET['name_visible'])) Mage::getModel('core/config')->saveConfig('barcode/product/name_visible', $_GET['name_visible']);
        if (isset($_GET['product_name_leng']) && $_GET['product_name_leng'] > 0) Mage::getModel('core/config')->saveConfig('barcode/product/product_name_leng', $_GET['product_name_leng']);
        if (isset($_GET['product_name_settings'])) Mage::getModel('core/config')->saveConfig('barcode/product/product_name_settings', $_GET['product_name_settings']);

        if (isset($_GET['price_visible'])) Mage::getModel('core/config')->saveConfig('barcode/product/price_visible', $_GET['price_visible']);
        if (isset($_GET['price_settings'])) Mage::getModel('core/config')->saveConfig('barcode/product/price_settings', $_GET['price_settings']);

        if (isset($_GET['slot1_visible'])) Mage::getModel('core/config')->saveConfig('barcode/product/slot1_visible', $_GET['slot1_visible']);
        if (isset($_GET['slot1'])) Mage::getModel('core/config')->saveConfig('barcode/product/slot1', $_GET['slot1']);
        if (isset($_GET['slot1_settings'])) Mage::getModel('core/config')->saveConfig('barcode/product/slot1_settings', $_GET['slot1_settings']);

        if (isset($_GET['slot2_visible'])) Mage::getModel('core/config')->saveConfig('barcode/product/slot2_visible', $_GET['slot2_visible']);
        if (isset($_GET['slot2'])) Mage::getModel('core/config')->saveConfig('barcode/product/slot2', $_GET['slot1']);
        if (isset($_GET['slot2_settings'])) Mage::getModel('core/config')->saveConfig('barcode/product/slot2_settings', $_GET['slot2_settings']);

        if (isset($_GET['slot3_visible'])) Mage::getModel('core/config')->saveConfig('barcode/product/slot3_visible', $_GET['slot3_visible']);
        if (isset($_GET['slot3'])) Mage::getModel('core/config')->saveConfig('barcode/product/slot3', $_GET['slot3']);
        if (isset($_GET['slot3_settings'])) Mage::getModel('core/config')->saveConfig('barcode/product/slot3_settings', $_GET['slot3_settings']);

        if (isset($_GET['slot4_visible'])) Mage::getModel('core/config')->saveConfig('barcode/product/slot4_visible', $_GET['slot4_visible']);
        if (isset($_GET['slot4'])) Mage::getModel('core/config')->saveConfig('barcode/product/slot4', $_GET['slot4']);
        if (isset($_GET['slot4_settings'])) Mage::getModel('core/config')->saveConfig('barcode/product/slot4_settings', $_GET['slot4_settings']);

        if (isset($_GET['font_for_text'])) Mage::getModel('core/config')->saveConfig('barcode/product/use_font_for_text', $_GET['font_for_text']);
        if (isset($_GET['font_size']) && $_GET['font_size'] > 0) Mage::getModel('core/config')->saveConfig('barcode/product/font_size', $_GET['font_size']);

        if (isset($_GET['barcode_order_include_logo'])) Mage::getModel('core/config')->saveConfig('barcode/order/barcode_order_include_logo', $_GET['barcode_order_include_logo']);
        if (isset($_GET['invoice_enabled'])) Mage::getModel('core/config')->saveConfig('barcode/order/invoice_enabled', $_GET['invoice_enabled']);
        if (isset($_GET['invoice_position'])) Mage::getModel('core/config')->saveConfig('barcode/order/invoice_position', $_GET['invoice_position']);
        if (isset($_GET['packingslip_enabled'])) Mage::getModel('core/config')->saveConfig('barcode/order/packingslip_enabled', $_GET['packingslip_enabled']);
        if (isset($_GET['packingslip_position'])) Mage::getModel('core/config')->saveConfig('barcode/order/packingslip_position', $_GET['packingslip_position']);
        if (isset($_GET['order_padding_top']) && $_GET['order_padding_top'] > 0) Mage::getModel('core/config')->saveConfig('barcode/order/padding_top', $_GET['order_padding_top']);
        if (isset($_GET['order_padding_left']) && $_GET['order_padding_left'] > 0) Mage::getModel('core/config')->saveConfig('barcode/order/padding_left', $_GET['order_padding_left']);
        if (isset($_GET['order_barcode_width']) && $_GET['order_barcode_width'] > 0) Mage::getModel('core/config')->saveConfig('barcode/order/barcode_width', $_GET['order_barcode_width']);
        if (isset($_GET['order_barcode_height']) && $_GET['order_barcode_height'] > 0) Mage::getModel('core/config')->saveConfig('barcode/order/barcode_height', $_GET['order_barcode_height']);
        if (isset($_GET['rma_valid_duration']) && $_GET['rma_valid_duration'] >= 0) Mage::getModel('core/config')->saveConfig('barcode/order/rma_valid_duration', $_GET['rma_valid_duration']);
        if (isset($_GET['stock_update'])) Mage::getModel('core/config')->saveConfig('barcode/order/stock_update', $_GET['stock_update']);

        if (isset($_GET['debug_isEnabled'])) Mage::getModel('core/config')->saveConfig('barcode/debug/isEnabled', $_GET['debug_isEnabled']);

        // Optimized save products > 10k
        $conversion = $_GET['conversion'];
        $barcode_field = $_GET['field'];
        $symbology = $_GET['symbology'];
        $barcode_source = $_GET['source'];

        $resource = Mage::getSingleton('core/resource');
        $tableName = $resource->getTableName('catalog/product');
        $readConnection = $resource->getConnection('core_read');
        $generatePerRequest = isset($_GET['generate_per_request']) ? $_GET['generate_per_request'] : 0;
        if ($generatePerRequest > 0) {
            if (isset($_GET['first_click']) && $_GET['first_click']) {
                $this->_getSession()->unsetData('barcode_ajaxsaveconfigAction_lastPage_data');
                $this->_getSession()->unsetData('barcode_ajaxsaveconfigAction_totalRow_data');
            }
            // Pagination data
            //if $generatePerRequest = 0 then set it = 100 default
            if( $generatePerRequest == 0 ) $generatePerRequest = 100;

            $num_rec_per_page = $generatePerRequest;
            $lastPage = $this->_getSession()->getData('barcode_ajaxsaveconfigAction_lastPage_data');
            if (!$lastPage) {
                $lastPage = 0;
            }
            $start_from = $lastPage * $num_rec_per_page;
            $this->_getSession()->setData('barcode_ajaxsaveconfigAction_lastPage_data', $lastPage + 1);

            // Count if have not count data
            $totalRow = $this->_getSession()->getData('barcode_ajaxsaveconfigAction_totalRow_data');
            if (!$totalRow) {
                //            $query = SELECT COUNT(*) FROM `catalog_product_entity` WHERE  `type_id` =  'simple'
                $query = "SELECT COUNT(*) FROM `$tableName`";
                $totalRow = $readConnection->fetchOne($query);
                $this->_getSession()->setData('barcode_ajaxsaveconfigAction_totalRow_data', (int)$totalRow);
            }

            //            $query = "SELECT `entity_id` ,  `sku`  FROM $tableName ORDER BY `entity_id` WHERE  `type_id` =  'simple' LIMIT $start_from, $num_rec_per_page";

            $query = "SELECT `entity_id` ,  `sku`  FROM $tableName ORDER BY `entity_id` LIMIT $start_from, $num_rec_per_page";
        } else {
            // Count if have not count data
            $totalRow = $this->_getSession()->getData('barcode_ajaxsaveconfigAction_totalRow_data');

            if (!$totalRow) {
                //            $query = SELECT COUNT(*) FROM `catalog_product_entity` WHERE  `type_id` =  'simple'
                $query = "SELECT COUNT(*) FROM `$tableName`";
                $totalRow = $readConnection->fetchOne($query);
                $this->_getSession()->setData('barcode_ajaxsaveconfigAction_totalRow_data', (int)$totalRow);
            }
            $query = "SELECT `entity_id` ,  `sku`  FROM $tableName ORDER BY `entity_id`";
        }
        $results = $readConnection->fetchAll($query);

        foreach ($results as $_product) {
            $product_id = $_product['entity_id'];
            $product_sku = $_product['sku'];
            $field = '';
            if (intval($conversion == 1)) {
                switch (intval($barcode_field)) {
                    case 0: //Product ID
                        $field = str_pad($product_id, 12, "0", STR_PAD_LEFT);
                        break;
                    case 1: //SKU
                        if (!empty($product_sku)) {
                            $field = substr(number_format(hexdec(substr(md5($product_sku), 0, 16)), 0, "", ""), 0, 12);
                        }

                        break;
                    case 2: //custom field
                        $product = Mage::getModel('catalog/product')->load($product_id);
                        $attr_id = $barcode_source;
                        $attr = Mage::getModel('eav/entity_attribute')->load($attr_id)->getAttributeCode();
                        // $attr_val = $product->getResource()->getAttribute($attr)->getFrontend()->getValue($product);
                        $store_id = Mage::app()->getStore()->getStoreId();
                        $attr_val = Mage::getResourceModel('catalog/product')->getAttributeRawValue($product_id, $attr, $store_id);
                        if (!empty($attr_val)) $field = substr(number_format(hexdec(substr(md5($attr_val), 0, 16)), 0, "", ""), 0, 12);
                        break;
                }
            } else // Conversion: OFF
            {
                $attr_id = Mage::getStoreConfig('barcode/product/barcode_value');
                $attr = Mage::getModel('eav/entity_attribute')->load($attr_id)->getAttributeCode();
                $store_id = Mage::app()->getStore()->getStoreId();
                $attr_val = Mage::getResourceModel('catalog/product')->getAttributeRawValue($product_id, $attr, $store_id);
                $field = $attr_val;
            }
            $field = trim($field);

            //EAN13, Conversion OFF
            if ($symbology == 0 && $conversion == 0) {
                //Check $field leng and is number
                if (strlen($field) < 12 && floatval($field) != 0) {
                    //Add prefix 0
                    $field = str_pad($field, 12, "0", STR_PAD_LEFT);
                }
            }

            //EAN13 add 1 digit
            if ($symbology == 0) {
                $helper = Mage::helper('barcode/barcode');
                $helper->addLastDigitForEan13($field);
            }
            //UPC add 1 digit
            if ($symbology == 7) {
                $helper = Mage::helper('barcode/barcode');
                $helper->addLastDigitForEan13($field);
            }
            //Update barcode value
            Mage::getSingleton('catalog/product_action')->updateAttributes(array($product_id), array('sm_barcode' => $field), 0);
        }
        //end foreach
        $numberUpdated = count($results);

        $numberUpdated += $lastPage * $num_rec_per_page;
        if($numberUpdated>$totalRow){
            $numberUpdated=$totalRow;
        }
        $resData = array();
        if ($generatePerRequest > 0 && $numberUpdated !=$totalRow) {
            $resData['message'] = 'Updated ' . $numberUpdated . "/$totalRow";
        } else {
            $this->_getSession()->unsetData('barcode_ajaxsaveconfigAction_lastPage_data');
            $this->_getSession()->unsetData('barcode_ajaxsaveconfigAction_totalRow_data');
            $resData['success'] = 'Updated ' . $numberUpdated . "/$totalRow";
            $resData['message'] = 'Done';
        }
        $this->getResponse()->setBody(Mage::helper('barcode')->jsonEncode($resData));

    }

//    }

}
