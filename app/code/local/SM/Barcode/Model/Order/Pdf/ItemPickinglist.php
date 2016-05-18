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
 * @version    2.7
 * @author     truongnq@smartosc.com
 * @copyright  Copyright (c) 2010-2013 SmartOSC Co. (http://www.smartosc.com)
 */
require_once(BP . DS . 'lib' . DS . 'tcpdf' . DS . 'config' . DS . 'lang' . DS . 'eng.php');
require_once(BP . DS . 'lib' . DS . 'tcpdf' . DS . 'tcpdf.php');

class SM_Barcode_Model_Order_Pdf_ItemPickinglist extends TCPDF
{
    //Get whole pdf file
    public function getPdf($orderIds = array('type' => ''))
    {
        //--------------------- Config TCPDF ---------------------------
        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
//        ob_start();
//        ob_clean();
        set_time_limit(120);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('magento@smartosc.com');
        $pdf->SetTitle('Barcode labels');
        $pdf->SetSubject('');
        $pdf->SetKeywords('');

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        //set margins
        /*$pdf->SetMargins(10, PDF_MARGIN_TOP, 5);
        $pdf->SetMargins(10, PDF_MARGIN_BOTTOM, 5);*/

        //set auto page breaks
        /*$pdf->SetAutoPageBreak(FALSE, 15);*/

        //set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        //set some language-dependent strings
        $lg = Array();
        $lg['a_meta_charset'] = 'UTF-8';
        $lg['a_meta_dir'] = 'ltr';
        $lg['a_meta_language'] = 'fa';
        $lg['w_page'] = 'page';
        $pdf->setLanguageArray($lg);

// ------------------------Barcode config-----------------------

        $flag = false;
        $lstInvoices = array();
        foreach ($orderIds as $orderId) {
            $invoices = Mage::getResourceModel('sales/order_collection')
                ->addFieldToFilter("entity_id",$orderId)
                ->load();

            $lstInvoices[]= $invoices;
            if ($invoices->getSize() == 0) {
                $flag = false;
                $this->getPdfPage($pdf, $invoices);
            }
            else $flag=true;
            if($flag!=true){
                Mage::getSingleton('adminhtml/session')->addError('There are no printable documents related to selected orders.');
                Mage::app()->getResponse()->setRedirect(Mage::getModel('core/url')->getUrl('*/sales_order/'));
                return;
            }

        }

        if (count($lstInvoices) > 0) {
            $flag = true;
            $this->getPdfPage($pdf, $lstInvoices);
        }
        if($flag!=true){
            Mage::getSingleton('adminhtml/session')->addError('There are no printable documents related to selected orders.');
            Mage::app()->getResponse()->setRedirect(Mage::getModel('core/url')->getUrl('*/sales_order/'));
            return;
        }

        $pdf->lastPage();

        $barcode_dir = Mage::getBaseDir('media') . DS . 'barcode' . DS . 'pdf' . DS;
        return $pdf->Output($barcode_dir . 'product_barcode_' . date('Y-m-d-h-i-s') . '.pdf', 'FD'); // D ; FD ; I
    }

    //Get a page of pdf
    public function getPdfPage($pdf, $lstInvoices)
    {
        // set font
        $pdf->SetFont('times', '', '13');
        $pdf->AddPage('P', 'A4');
        $pdf->setJPEGQuality(100);
        //check if include logo is yes or no
        if (Mage::getStoreConfig("barcode/product/include_logo")) {
            $logoFile = is_file("media/barcode/" . Mage::getStoreConfig("barcode/general/logo_image_file")) ? "media/barcode/" . Mage::getStoreConfig("barcode/general/logo_image_file") : "media/barcode/logo.png";
            $logoType = getimagesize($logoFile);
            if ($logoType[2] == 1) {
                //gif not supported
                $logoFile = "media/barcode/logo.png";
            } elseif ($logoType[2] == 2) {
                //jpeg is ok
            } elseif ($logoType[2] == 3) {
                //png is ok
            } else {
                //other types
                $logoFile = "media/barcode/logo.png";
            }
            $base_dir = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
            $logo_url = $base_dir . $logoFile;
            $str_logo_url = '<img src="'.$logo_url.'"/>';
        }

        $html='';
        $invoice_index=0;
        $str_data = "";
        $i = 0;
        $product_data = array();
//        if(count($lstInvoices)==1){
//
//        }
//
//        if(count($lstInvoices)>1)
        foreach ($lstInvoices as $invoice) {
            $total=0;
            foreach($invoice as $invoi){
                if ($invoi->getStoreId()) {
                    Mage::app()->getLocale()->emulate($invoi->getStoreId());
                    Mage::app()->setCurrentStore($invoi->getStoreId());
                }
                $order = $invoi;

                $barcode_order = Mage::helper('barcode/barcode')->createOrderBarcode($order->getIncrementId());
                $barcode_order = $media_path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . "barcode/order/".$order->getIncrementId().".png";

                $invoice_id = $invoi->getIncrementId();
                $order_id = $order->getIncrementId();
                $order_date = Mage::helper('core')->formatDate($order->getCreatedAtStoreDate(), 'medium', false);

                /* Billing Address */
                $billingAddress = $this->_formatAddress($order->getBillingAddress()->format('pdf'));

                /* Payment */
                $paymentInfo = Mage::helper('payment')->getInfoBlock($order->getPayment())
                    ->setIsSecureMode(true)
                    ->toPdf();
                $paymentInfo = htmlspecialchars_decode($paymentInfo, ENT_QUOTES);
                $payment = explode('{{pdf_row_separator}}', $paymentInfo);
                foreach ($payment as $key=>$value){
                    if (strip_tags(trim($value)) == '') {
                        unset($payment[$key]);
                    }
                }
                $payment_method = reset($payment);

                /* Shipping Address and Method */
                if (!$order->getIsVirtual()) {
                    /* Shipping Address */
                    $shippingAddress = $this->_formatAddress($order->getShippingAddress()->format('pdf'));
                    $shippingMethod  = $order->getShippingDescription();
                }

                /* Shipping Charges */
                $totalShippingChargesText = "(" . Mage::helper('sales')->__('Total Shipping Charges') . " ". $order->formatPriceTxt($order->getShippingAmount()) . ")";
                $shipping_charges = $order->formatPriceTxt($order->getShippingAmount());

                /* Billing address */
                $str_billing_address = "";
                for($j=0;$j<count($billingAddress);$j++){
                    $str_billing_address .= "&nbsp;&nbsp;" . $billingAddress[$j] . "<br>";
                }
                // Customter name
                $customer_name = $billingAddress[0];
                $str_billing_address = substr_replace($str_billing_address ,"",-4);

                /* Shipping address */
                $str_shipping_address = "";
                for($j=0;$j<count($shippingAddress);$j++){
                    $str_shipping_address .= "&nbsp;&nbsp;" . $shippingAddress[$j] . "<br>";
                }
                $str_shipping_address = substr_replace($str_shipping_address ,"",-4);


                $product_data_one=array();
                $data_one = array();
                $num=0;
                if(count($lstInvoices)==1)
                $new_list = array();
                foreach ($invoi->getAllItems() as $item){
                    $num++;
                    $product_type = $item->getProduct()->getTypeId();

                    $lstId = array();
                    switch($product_type){
                        case 'configurable':

                            break;

                        case "virtual":
                            break;

                        case 'bundle':
                            break;
//                            $bundle_serialize =  unserialize($item->getData('product_options'));
//                            //$product_data[$i]['bundle_serialize']=$bundle_serialize;
//                            foreach($bundle_serialize['bundle_options'] as $option_items){
//                                foreach($option_items['value'] as $option_item){
//                                    $option_simple_product = Mage::getModel('catalog/product')
//                                        ->loadByAttribute('name',$option_item['title']);
//                                    $productId = $option_simple_product->getId();
//                                    $lstId[]= $productId;
//                                    //$i++;
//                                    //  $productIdBundleList[] = $productId;
//                                    //  $helper->printBarcodeAbs($productId,150,50);
//                                }
//                            }
//                            break;

                        case "simple":
                            $product_data[$i]['id'] = $item->getProductId();
                            break;

                        default:
                            break;
                    }
                    if($product_type=="configurable" || $product_type=="virtual" || $product_type=="bundle") continue;

//                    if(count($lstId)==0){
                        $new_id=$product_data[$i]['id'];
                        $check=0;
                        if($i!=0){
                            if(count($lstInvoices)>1){
                            foreach($product_data as $key=>$pro){
                                if($pro['id']== $new_id && $key !=$i){

                                    $new_pro = $pro;
                                    $qty = $new_pro['qty'];
                                    unset($new_pro['qty']);
                                    $new_pro['qty']=$qty+ $item->getQtyOrdered();

                                    unset($product_data[$key]);
                                    $product_data[$key]=$new_pro;
                                    $str_data='';
                                    //begin convert to html

                                    foreach($product_data as $data){

                                        $product_type = $data['productType'];
                                        if($data['sku']=="" && $product_type!="bundle"){
                                            continue;
                                        }
                                        $sku = Mage::helper('core/string')->str_split($data['sku'], 17);
                                        //$prices = $this->getItemPricesForDisplay($order,$item);

                                        // $productId = $item->getOrderItem()->getProductId();
                                        $code = Mage::getStoreConfig('barcode/general/symbology');
                                        $helper = Mage::helper('barcode/barcode');
                                        //$helper->printBarcodeAbs($productId,120,50);
                                        $typeId = $data['typeid'];
                                        switch($typeId){

//                                            case 'configurable':
//                                                $productId = $data['productID'];
//                                                $helper->printBarcodeAbs($productId,150,50);
//                                                //$total+=$data['qty'];
//                                                break;

                                            //default: //simple
                                            case 'simple':
                                                $productId =$data['productID'];
                                                $helper->createProductBarcode($productId);
                                                //$total+=$data['qty'];
                                                break;
                                            default:
                                                break;
                                        }


                                        $media_path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
                                        if($product_type != 'bundle'){
                                            $imagePath = $media_path . DS . 'barcode' . DS .$productId.'_'. $code .'_bc.png';
                                            $imagePath = str_replace("\\","/",$imagePath);
                                            $product_data[$i]['barcode'] = $imagePath;
                                        }else{
                                            $product_data[$i]['barcode'] = "";
                                        }


                                        $result = array();
                                        if ($options = $data['options']) {
                                            if (isset($options['options'])) {
                                                $result = array_merge($result, $options['options']);
                                            }
                                            if (isset($options['additional_options'])) {
                                                $result = array_merge($result, $options['additional_options']);
                                            }
                                            if (isset($options['attributes_info'])) {
                                                $result = array_merge($result, $options['attributes_info']);
                                            }
                                        }

                                        $options = $result;
                                        $a = 0;
                                        if ($options) {
                                            foreach ($options as $option) {
                                                $attr = Mage::helper('core/string')->str_split(strip_tags($option['label']), 40, true, true);
                                                $product_options[$sku[0]][$a]['attr'] = $attr[0];
                                                if ($option['value']) {
                                                    if (isset($option['print_value'])) {
                                                        $_printValue = $option['print_value'];
                                                    } else {
                                                        $_printValue = strip_tags($option['value']);
                                                    }
                                                    $values = explode(', ', $_printValue);
                                                    foreach ($values as $value) {
                                                        $attr_val = Mage::helper('core/string')->str_split($value, 30, true, true);
                                                    }
                                                    $product_options[$sku[0]][$a]['attr_val'] = $attr_val[0];
                                                }
                                                $a++;
                                            }
                                        }
                                        if($product_type == 'bundle'){
                                            $str_data .=''. '<tr><td><div style="width:10px;border:1px solid #000;"></div></td>';
                                        }else{
                                            $str_data .= ''.'<tr><td><div style="width:10px;border:1px solid #000;"></div></td>';
                                        }
                                        $str_data .= '<td align="center">'.$data['sku'].'</td>';
                                        $str_data .= '<td align="center">'.$data['id'].'</td>';

                                        if($product_options == null){
                                            $str_data .= '<td>'.$data['name'].'</td>';
                                        }else{
                                            $str_data .= '<td>'.$data['name'].'<br>';

                                            $str_data = substr_replace($str_data ,"",-4);
                                            $str_data .=''. '</td>';
                                        }

                                        $str_data .= '<td align="center">'.$data['qty'].'</td>';
                                        $str_img = "";
                                        if($data['barcode'] != ''){
                                            $str_img = '<img src="'.$data['barcode'].'">';
                                        }
                                        $str_data .= '<td align="left" valign="top">'.$str_img.'</td>';


                                        $str_data .= ''.'</tr>';



                                    }
                                    //end convert

                                    $check=1;
                                    break;
                                }
                            }
                        }
                        }

                        if($check==0){
                            $product_name = $item->getName();
                            $product_data[$i]['name'] = $product_name;
                            $sku = Mage::helper('core/string')->str_split($this->getSku($item), 17);
                            $product_data[$i]['sku'] = $sku[0];
                            $product_data[$i]['qty'] = $item->getQtyOrdered() * 1;
                            $product_data[$i]['tax'] = $order->formatPriceTxt($item->getTaxAmount());
                            $prices = $this->getItemPricesForDisplay($order,$item);
                            $product_data[$i]['price'] = $prices[0]['price'];
                            $product_data[$i]['subtotal'] = $prices[0]['subtotal'];


                            $productId = $item->getProductId();
                            $code = Mage::getStoreConfig('barcode/general/symbology');
                            $helper = Mage::helper('barcode/barcode');
                            //$helper->printBarcodeAbs($productId,120,50);
                            $product_data[$i]['typeid']=$item->getProduct()->getTypeId();
                            switch($item->getProduct()->getTypeId()){
//                                case 'configurable':
//                                    $productId = Mage::getModel('catalog/product')->getIdBySku($item->getSku());
//
//                                    $product_data[$i]['productID']=$productId;
//                                    $helper->printBarcodeAbs($productId,150,50);
//                                    $total+=$item->getQtyOrdered();
//                                    break;
                                case "simple":
                                    $productId = $item->getProductId();
                                    $product_data[$i]['productID']=$productId;
                                    $helper->createProductBarcode($productId);
                                    $total+=$item->getQtyOrdered();
                                    break;
                                default: //simple
                                    break;
                            }

                            $product_type = $item->getProduct()->getTypeId();
                            $product_data[$i]['productType']=$product_type;
                            $media_path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
                            if($product_type != 'bundle' && $product_type != 'downloadable' && $product_type != null){
                                $imagePath = $media_path . DS . 'barcode' . DS .$productId.'_'. $code .'_bc.png';

                                $imagePath = str_replace("\\","/",$imagePath);
                                $product_data[$i]['barcode'] = $imagePath;
                            }else{
                                $product_data[$i]['barcode'] = "";
                            }

//                            $result = array();
//                            if ($options = $item->getProductOptions()) {
//                                $product_data[$i]['options']=$options;
//                                if (isset($options['options'])) {
//                                    $result = array_merge($result, $options['options']);
//                                }
//                                if (isset($options['additional_options'])) {
//                                    $result = array_merge($result, $options['additional_options']);
//                                }
//                                if (isset($options['attributes_info'])) {
//                                    $result = array_merge($result, $options['attributes_info']);
//                                }
//                            }
//
//                            $options = $result;
//                            $a = 0;
//                            if ($options) {
//                                foreach ($options as $option) {
//                                    $attr = Mage::helper('core/string')->str_split(strip_tags($option['label']), 40, true, true);
//                                    $product_options[$sku[0]][$a]['attr'] = $attr[0];
//                                    if ($option['value']) {
//                                        if (isset($option['print_value'])) {
//                                            $_printValue = $option['print_value'];
//                                        } else {
//                                            $_printValue = strip_tags($option['value']);
//                                        }
//                                        $values = explode(', ', $_printValue);
//                                        foreach ($values as $value) {
//                                            $attr_val = Mage::helper('core/string')->str_split($value, 30, true, true);
//                                        }
//                                        $product_options[$sku[0]][$a]['attr_val'] = $attr_val[0];
//                                    }
//                                    $a++;
//                                }
//                            }


                            foreach($product_data as $data){
                                if(count($lstInvoices)==1){
                                    if(count($new_list)>0){
                                        foreach($new_list as $pro){

                                            if($data['sku'] == $pro['sku']){
                                                //echo $data['sku']."  con pro la   ".$pro['sku'];
                                                $data['sku']="";
                                                break;
                                            }

                                            else{
                                                $new_list[] = $data;
                                            }
                                        }

                                    }else{
                                        $new_list[] = $data;
                                    }

                                }

                                if($data['sku']!=""){
                                    if($product_type == 'bundle'){
                                        $str_data .=''. '<tr><td><div style="width:10px;border:1px solid #000;"></div></td>';
                                    }else{
                                        $str_data .= ''.'<tr><td><div style="width:10px;border:1px solid #000;"></div></td>';
                                    }
                                    $str_data .= '<td align="center">'.$data['sku'].'</td>';
                                    $str_data .= '<td align="center">'.$data['id'].'</td>';

                                    if($product_options == null){
                                        $str_data .= '<td>'.$data['name'].'</td>';
                                    }else{
                                        $str_data .= '<td>'.$data['name'].'<br>';

                                        $str_data = substr_replace($str_data ,"",-4);
                                        $str_data .=''. '</td>';
                                    }

                                    $str_data .= '<td align="center">'.$data['qty'].'</td>';
                                    $str_img = "";
                                    if($data['barcode'] != ''){
                                        $str_img = '<img src="'.$data['barcode'].'">';
                                    }
                                    $str_data .= '<td align="left" valign="top">'.$str_img.'</td>';


                                    $str_data .= ''.'</tr>';
                                }
                                else{
                                    continue;
                                }
                            }

                        }

                        $i++;

//                    }
//                    else{
//                        foreach($lstId as $key=>$temp_id){
//                            $product_data[$i]['id'] = $temp_id;
//                            $new_id = $temp_id;
//                            $check=0;
//                            if($i!=0){
//                                foreach($product_data as $key=>$pro){
//                                    if($pro['id']== $new_id && $key !=$i){
//
//                                        $new_pro = $pro;
//                                        $qty = $new_pro['qty'];
//                                        unset($new_pro['qty']);
//                                        $new_pro['qty']=$qty+ $item->getQtyOrdered();
//
//                                        unset($product_data[$key]);
//                                        $product_data[$key]=$new_pro;
//                                        $str_data='';
//                                        //begin convert to html
//
//                                        foreach($product_data as $data){
//
//                                            $product_type = $data['productType'];
//                                            if($data['sku']=="" && $product_type!="bundle"){
//                                                continue;
//                                            }
//                                            $sku = Mage::helper('core/string')->str_split($data['sku'], 17);
//                                            //$prices = $this->getItemPricesForDisplay($order,$item);
//
//                                            // $productId = $item->getOrderItem()->getProductId();
//                                            $code = Mage::getStoreConfig('barcode/general/symbology');
//                                            $helper = Mage::helper('barcode/barcode');
//                                            //$helper->printBarcodeAbs($productId,120,50);
//                                            $typeId = $data['typeid'];
//                                            switch($typeId){
//
//                                                case 'configurable':
//                                                    $productId = $data['productID'];
//                                                    $helper->printBarcodeAbs($productId,150,50);
//                                                    //$total+=$data['qty'];
//                                                    break;
//                                                //default: //simple
//                                                case 'simple':
//                                                    $productId =$data['productID'];
//                                                    $helper->printBarcodeAbs($productId,150,50);
//                                                    //$total+=$data['qty'];
//                                                    break;
//                                                default:
//                                                    break;
//                                            }
//
//
//                                            $media_path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
//                                            if($product_type != 'bundle'){
//                                                $imagePath = $media_path . DS . 'barcode' . DS .$productId.'_'. $code .'_bc.png';
//                                                $imagePath = str_replace("\\","/",$imagePath);
//                                                $product_data[$i]['barcode'] = $imagePath;
//                                            }else{
//                                                $product_data[$i]['barcode'] = "";
//                                            }
//
//
//                                            $result = array();
//                                            if ($options = $data['options']) {
//                                                if (isset($options['options'])) {
//                                                    $result = array_merge($result, $options['options']);
//                                                }
//                                                if (isset($options['additional_options'])) {
//                                                    $result = array_merge($result, $options['additional_options']);
//                                                }
//                                                if (isset($options['attributes_info'])) {
//                                                    $result = array_merge($result, $options['attributes_info']);
//                                                }
//                                            }
//
//                                            $options = $result;
//                                            $a = 0;
//                                            if ($options) {
//                                                foreach ($options as $option) {
//                                                    $attr = Mage::helper('core/string')->str_split(strip_tags($option['label']), 40, true, true);
//                                                    $product_options[$sku[0]][$a]['attr'] = $attr[0];
//                                                    if ($option['value']) {
//                                                        if (isset($option['print_value'])) {
//                                                            $_printValue = $option['print_value'];
//                                                        } else {
//                                                            $_printValue = strip_tags($option['value']);
//                                                        }
//                                                        $values = explode(', ', $_printValue);
//                                                        foreach ($values as $value) {
//                                                            $attr_val = Mage::helper('core/string')->str_split($value, 30, true, true);
//                                                        }
//                                                        $product_options[$sku[0]][$a]['attr_val'] = $attr_val[0];
//                                                    }
//                                                    $a++;
//                                                }
//                                            }
//                                            if($product_type == 'bundle'){
//                                                $str_data .=''. '<tr><td><div style="width:10px;border:1px solid #000;"></div></td>';
//                                            }else{
//                                                $str_data .= ''.'<tr><td><div style="width:10px;border:1px solid #000;"></div></td>';
//                                            }
//                                            $str_data .= '<td align="center">'.$data['sku'].'</td>';
//                                            $str_data .= '<td align="center">'.$data['id'].'</td>';
//
//                                            if($product_options == null){
//                                                $str_data .= '<td>'.$data['name'].'</td>';
//                                            }else{
//                                                $str_data .= '<td>'.$data['name'].'<br>';
//
//                                                $str_data = substr_replace($str_data ,"",-4);
//                                                $str_data .=''. '</td>';
//                                            }
//
//                                            $str_data .= '<td align="center">'.$data['qty'].'</td>';
//                                            $str_img = "";
//                                            if($data['barcode'] != ''){
//                                                $str_img = '<img src="'.$data['barcode'].'">';
//                                            }
//                                            $str_data .= '<td align="left" valign="top">'.$str_img.'</td>';
//
//
//                                            $str_data .= ''.'</tr>';
//
//
//
//                                        }
//                                        //end convert
//
//                                        unset($bundle_serialize);
//                                        $bundle_serialize=array();
//                                        $check=1;
//                                        break;
//                                    }
//                                }
//                            }
//
//                            if($check==0){
//                                $productId = $temp_id;
//                                $product_data[$i]['productID']=$productId;
//                                $helper->printBarcodeAbs($productId,150,50);
//                                $simple_product = Mage::getModel('catalog/product')
//                                    ->load($productId);
//                                $product_name = $simple_product->getName();
//                                $product_data[$i]['name'] = $product_name;
//
//                                $sku = Mage::helper('core/string')->str_split($this->getSku($simple_product), 17);
//                                $product_data[$i]['sku'] = $sku[0];
//                                $product_data[$i]['qty'] = $simple_product->getQtyOrdered() * 1;
//                                $product_data[$i]['tax'] = $order->formatPriceTxt($item->getTaxAmount());
//
//                                $prices = $this->getItemPricesForDisplay($order,$simple_product);
//                                $product_data[$i]['price'] = $prices[0]['price'];
//                                $product_data[$i]['subtotal'] = $prices[0]['subtotal'];
//
//                                $code = Mage::getStoreConfig('barcode/general/symbology');
//                                $helper = Mage::helper('barcode/barcode');
//                                //$helper->printBarcodeAbs($productId,120,50);
//                                $product_data[$i]['typeid']="simple";//$item->getProduct()->getTypeId();
////                                switch($item->getProduct()->getTypeId()){
////                                    case 'configurable':
////                                        $productId = Mage::getModel('catalog/product')->getIdBySku($item->getSku());
////                                        $product_data[$i]['productID']=$productId;
////                                        $helper->printBarcodeAbs($productId,150,50);
////                                       // $total+=$item->getQtyOrdered();
////                                        break;
//
//                                //  default: //simple
////                                        $productId = $temp_id;
////                                        $product_data[$i]['productID']=$productId;
////                                        $helper->printBarcodeAbs($productId,150,50);
//                                //$total+=$item->getQtyOrdered();
//                                //     break;
//                                //   }
//
//                                $product_type = "simple";//$item->getProduct()->getTypeId();
//                                $product_data[$i]['productType']=$product_type;
//                                $media_path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
//                                if($product_type != 'bundle'){
//                                    $imagePath = $media_path . DS . 'barcode' . DS .$productId.'_'. $code .'_bc.png';
//
//                                    $imagePath = str_replace("\\","/",$imagePath);
//                                    $product_data[$i]['barcode'] = $imagePath;
//                                }else{
//                                    $product_data[$i]['barcode'] = "";
//                                }
//
//                                $result = array();
//                                if ($options = $simple_product->getProductOptions()) {
//                                    $product_data[$i]['options']=$options;
//                                    if (isset($options['options'])) {
//                                        $result = array_merge($result, $options['options']);
//                                    }
//                                    if (isset($options['additional_options'])) {
//                                        $result = array_merge($result, $options['additional_options']);
//                                    }
//                                    if (isset($options['attributes_info'])) {
//                                        $result = array_merge($result, $options['attributes_info']);
//                                    }
//                                }
//
//                                $options = $result;
//                                $a = 0;
//                                if ($options) {
//                                    foreach ($options as $option) {
//                                        $attr = Mage::helper('core/string')->str_split(strip_tags($option['label']), 40, true, true);
//                                        $product_options[$sku[0]][$a]['attr'] = $attr[0];
//                                        if ($option['value']) {
//                                            if (isset($option['print_value'])) {
//                                                $_printValue = $option['print_value'];
//                                            } else {
//                                                $_printValue = strip_tags($option['value']);
//                                            }
//                                            $values = explode(', ', $_printValue);
//                                            foreach ($values as $value) {
//                                                $attr_val = Mage::helper('core/string')->str_split($value, 30, true, true);
//                                            }
//                                            $product_options[$sku[0]][$a]['attr_val'] = $attr_val[0];
//                                        }
//                                        $a++;
//                                    }
//                                }
//                                foreach($product_data as $data){
//
//                                    if($product_type == 'bundle'){
//                                        $str_data .=''. '<tr><td><div style="width:10px;border:1px solid #000;"></div></td>';
//                                    }else{
//                                        $str_data .= ''.'<tr><td><div style="width:10px;border:1px solid #000;"></div></td>';
//                                    }
//                                    $str_data .= '<td align="center">'.$data['sku'].'</td>';
//                                    $str_data .= '<td align="center">'.$data['id'].'</td>';
//
//                                    if($product_options == null){
//                                        $str_data .= '<td>'.$data['name'].'</td>';
//                                    }else{
//                                        $str_data .= '<td>'.$data['name'].'<br>';
//
//                                        $str_data = substr_replace($str_data ,"",-4);
//                                        $str_data .=''. '</td>';
//                                    }
//
//                                    $str_data .= '<td align="center">'.$data['qty'].'</td>';
//                                    $str_img = "";
//                                    if($data['barcode'] != ''){
//                                        $str_img = '<img src="'.$data['barcode'].'">';
//                                    }
//                                    $str_data .= '<td align="left" valign="top">'.$str_img.'</td>';
//
//
//                                    $str_data .= ''.'</tr>';
//
//                                }
//
//                                unset($bundle_serialize);
//                                $bundle_serialize=array();
//                            }
//
//                            $i++;
//                        }
//                        $i++;
//                        $lstId= array();
//                    }
//                }
                }



//                $base_subtotal = $order->formatPriceTxt($order->getBaseSubtotal());
//                $base_tax = $order->formatPriceTxt($order->getBaseTaxAmount());
                $amount = $order->formatPriceTxt($order->getBaseGrandTotal());
                if($invoice_index==0){
                    $html .= '
                <style>
                    p {
                        color: #003300;
                        font-family: Arial;
                        font-size: 5px;
                    }

                    table.table_header {
                        font-family: Arial;
                        font-size: 13px;
                    }

                    table.table_order {
                        font-family: Arial;
                        font-size: 14px;
                    }

                    table.table_product {
                        font-size: 14px;
                    }

                    table.table_total {
                        font-size: 14px;
                        font-weight: bold;
                    }

                    td {
                        /*border: 2px solid blue;*/
                        /*background-color: #ffffee;*/
                    }
                    td.second {
                        border: 2px dashed green;
                    }
                    div.test {
                        color: #CC0000;
                        background-color: #FFFF66;
                        font-family: Arial;
                        font-size: 10pt;
                        border-style: solid solid solid solid;
                        border-width: 2px 2px 2px 2px;
                        border-color: green #FF00FF blue red;
                        text-align: center;
                    }
                </style>

                <table class="table_header" cellpadding="4" cellspacing="0">
                    <tr>
                        <td align="left">
                        '.'';

                    $html.=''.$str_logo_url;
                    $html.='  </td>
                        <td align="right">'.'';

                    $html.=date('Y-m-d').'</td>
                    </tr>
                    <tr>
                        <td align="left">
                        </td>
                        <td align="right">
                        ';

                    $html.='Print Item Picking List w/Barcodes'.'
                        </td>
                    </tr>
                </table>
                <table class="table_product" cellpadding="4" cellspacing="0">
                    <tr>
                        <td width="4%" height="20px" style="background-color:#B6B5B5;font-size:14px;border-bottom: 1px solid #B6B5B5;" align="center">&nbsp;&nbsp;</td>
                        <td width="15%" height="20px" style="background-color:#B6B5B5;font-size:14px;border-bottom: 1px solid #B6B5B5;" align="center">SKU</td>
                        <td width="15%" height="20px" style="background-color:#B6B5B5;font-size:14px;border-bottom: 1px solid #B6B5B5;" align="center">PRO ID</td>
                        <td width="25%" height="20px" style="background-color:#B6B5B5;font-size:14px;border-bottom: 1px solid #B6B5B5;" align="center">PRODUCT NAME</td>
                        <td width="15%" height="20px" style="background-color:#B6B5B5;font-size:14px;border-bottom: 1px solid #B6B5B5;" align="center">QTY SUM</td>
                        <td width="25%" height="20px" style="background-color:#B6B5B5;font-size:14px;border-bottom: 1px solid #B6B5B5;" align="center">BARCODES</td>
                    </tr>
                ';
                }



            }

            /*$totals = $this->_getTotalsList($invoice);
            foreach ($totals as $total) {
                $total->setOrder($order)->setSource($invoice);

                if ($total->canDisplay()) {
                    foreach ($total->getTotalsForDisplay() as $totalData) {
                        $amount = $totalData['amount'];
                    }
                }
            }*/
            $invoice_index++;
        }

        /*$adress_store = Mage::getStoreConfig("general/store_information/address");*/

        $html.=''.$str_data.'
                </table>';

        // Mage::log($html);

        $pdf->writeHTML($html, true, false, true, false, '');


        return true;

        /*$html = '<h1>Example of HTML text flow</h1>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. <em>Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur?</em> <em>Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?</em><br /><br /><b>A</b> + <b>B</b> = <b>C</b> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <i>C</i> - <i>B</i> = <i>A</i> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <i>C</i> - <i>A</i> = <i>B</i> -&gt; &nbsp;&nbsp; <b>A</b> + <b>B</b> = <b>C</b> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <i>C</i> - <i>B</i> = <i>A</i> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <i>C</i> - <i>A</i> = <i>B</i> -&gt; &nbsp;&nbsp; <b>A</b> + <b>B</b> = <b>C</b> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <i>C</i> - <i>B</i> = <i>A</i> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <i>C</i> - <i>A</i> = <i>B</i> -&gt; &nbsp;&nbsp; <b>A</b> + <b>B</b> = <b>C</b> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <i>C</i> - <i>B</i> = <i>A</i> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <i>C</i> - <i>A</i> = <i>B</i> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <b>A</b> + <b>B</b> = <b>C</b> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <i>C</i> - <i>B</i> = <i>A</i> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <i>C</i> - <i>A</i> = <i>B</i> -&gt; &nbsp;&nbsp; <b>A</b> + <b>B</b> = <b>C</b> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <i>C</i> - <i>B</i> = <i>A</i> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <i>C</i> - <i>A</i> = <i>B</i> -&gt; &nbsp;&nbsp; <b>A</b> + <b>B</b> = <b>C</b> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <i>C</i> - <i>B</i> = <i>A</i> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <i>C</i> - <i>A</i> = <i>B</i> -&gt; &nbsp;&nbsp; <b>A</b> + <b>B</b> = <b>C</b> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <i>C</i> - <i>B</i> = <i>A</i> &nbsp;&nbsp; -&gt; &nbsp;&nbsp; <i>C</i> - <i>A</i> = <i>B</i><br /><br /><b>Bold</b><i>Italic</i><u>Underlined</u> <b>Bold</b><i>Italic</i><u>Underlined</u> <b>Bold</b><i>Italic</i><u>Underlined</u> <b>Bold</b><i>Italic</i><u>Underlined</u> <b>Bold</b><i>Italic</i><u>Underlined</u> <b>Bold</b><i>Italic</i><u>Underlined</u> <b>Bold</b><i>Italic</i><u>Underlined</u> <b>Bold</b><i>Italic</i><u>Underlined</u> <b>Bold</b><i>Italic</i><u>Underlined</u> <b>Bold</b><i>Italic</i><u>Underlined</u> <b>Bold</b><i>Italic</i><u>Underlined</u> <b>Bold</b><i>Italic</i><u>Underlined</u> <b>Bold</b><i>Italic</i><u>Underlined</u> <b>Bold</b><i>Italic</i><u>Underlined</u> <b>Bold</b><i>Italic</i><u>Underlined</u>';
        $pdf->writeHTML($html, true, 0, true, 0);return true;*/

        $pdf->ln(4);

        return $data;
    }

    protected function _formatAddress($address)
    {
        $return = array();
        foreach (explode('|', $address) as $str) {
            foreach (Mage::helper('core/string')->str_split($str, 45, true, true) as $part) {
                if (empty($part)) {
                    continue;
                }
                $return[] = $part;
            }
        }
        return $return;
    }

    public function getSku($item)
    {
        if ($item->getProductOptionByCode('simple_sku'))
            return $item->getProductOptionByCode('simple_sku');
        else
            return $item->getSku();
    }

    public function getItemPricesForDisplay($order,$item)
    {
        if (Mage::helper('tax')->displaySalesBothPrices()) {
            $prices = array(
                array(
                    'label'    => Mage::helper('tax')->__('Excl. Tax') . ':',
                    'price'    => $order->formatPriceTxt($item->getPrice()),
                    'subtotal' => $order->formatPriceTxt($item->getRowTotal())
                ),
                array(
                    'label'    => Mage::helper('tax')->__('Incl. Tax') . ':',
                    'price'    => $order->formatPriceTxt($item->getPriceInclTax()),
                    'subtotal' => $order->formatPriceTxt($item->getRowTotalInclTax())
                ),
            );
        } elseif (Mage::helper('tax')->displaySalesPriceInclTax()) {
            $prices = array(array(
                'price' => $order->formatPriceTxt($item->getPriceInclTax()),
                'subtotal' => $order->formatPriceTxt($item->getRowTotalInclTax()),
            ));
        } else {
            $prices = array(array(
                'price' => $order->formatPriceTxt($item->getPrice()),
                'subtotal' => $order->formatPriceTxt($item->getRowTotal()),
            ));
        }
        return $prices;
    }

    function _sortTotalsList($a, $b)
    {
        if ($a == $b) {
            return 0;
        }
        return ($a < $b) ? -1 : 1;
    }

    protected function _getTotalsList($source)
    {
        $totals = Mage::getConfig()->getNode('global/pdf/totals')->asArray();
        usort($totals, array($this, '_sortTotalsList'));
        $totalModels = array();
        foreach ($totals as $index => $totalInfo) {
            if (!empty($totalInfo['model'])) {
                $totalModel = Mage::getModel($totalInfo['model']);
                if ($totalModel instanceof Mage_Sales_Model_Order_Pdf_Total_Default) {
                    $totalInfo['model'] = $totalModel;
                } else {
                    Mage::throwException(
                        Mage::helper('sales')->__('PDF total model should extend Mage_Sales_Model_Order_Pdf_Total_Default')
                    );
                }
            } else {
                $totalModel = Mage::getModel($this->_defaultTotalModel);
            }

            $totalModel->setData($totalInfo);
            $totalModels[] = $totalModel;
        }
        return $totalModels;
    }

    protected $_defaultTotalModel = 'sales/order_pdf_total_default';

    public function convertHtml($product_data){

    }
    public function convertData(){

    }

}