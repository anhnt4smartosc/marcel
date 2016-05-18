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

class SM_Barcode_Model_Order_Pdf_Order extends TCPDF
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
        foreach ($orderIds as $orderId) {
            $invoices = Mage::getResourceModel('sales/order_invoice_collection')
                ->setOrderFilter($orderId)
                ->load();

            if ($invoices->getSize() > 0) {
                $flag = true;
                $this->getPdfPage($pdf, $invoices);
            }
            if($flag!=true){
                Mage::getSingleton('adminhtml/session')->addError('There are no printable documents related to selected orders.');
                Mage::app()->getResponse()->setRedirect(Mage::getModel('core/url')->getUrl('*/sales_order/'));
                return;
            }

        }

        $pdf->lastPage();

        $barcode_dir = Mage::getBaseDir('media') . DS . 'barcode' . DS . 'pdf' . DS;
        return $pdf->Output($barcode_dir . 'product_barcode_' . date('Y-m-d-h-i-s') . '.pdf', 'FD'); // D ; FD ; I
    }

    //Get a page of pdf
    public function getPdfPage($pdf, $invoices)
    {
        // set font
        $pdf->SetFont('freemono', '', '13');
        $pdf->AddPage('P', 'A4');
        $pdf->setJPEGQuality(100);
        //check if include logo is yes or no
        if (Mage::getStoreConfig("barcode/order/include_logo")) {
            /** it SHOULD be "barcode/order/logo_image_file"
            *
            $logoFile = is_file("media/barcode/" . Mage::getStoreConfig("barcode/product/logo_image_file")) ? "media/barcode/" . Mage::getStoreConfig("barcode/product/logo_image_file") : "media/barcode/logo.png";

            */

            //$logoFile = "media/barcode/logo.png";
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
        } else {
            $str_logo_url = '';
        }

        foreach ($invoices as $invoice) {
            if ($invoice->getStoreId()) {
                Mage::app()->getLocale()->emulate($invoice->getStoreId());
                Mage::app()->setCurrentStore($invoice->getStoreId());
            }
            $order = $invoice->getOrder();

            if (Mage::getStoreConfig('barcode/order/invoice_enabled') == '1') {
                $barcode_order = Mage::helper('barcode/barcode')->createOrderBarcode($order->getRealOrderId());

                /*
                 * Quick & Dirty
                 * XBAR-661
                 */
                $barcodeOrderFileName = (strlen($order->getRealOrderId()) > 9) ? str_replace("-", "", $order->getRealOrderId()) : $order->getRealOrderId();

                $barcode_order = $media_path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . "barcode/order/" . $barcodeOrderFileName . ".png";
                $barcode_order_img = '<img style="padding:10px;" height="50" src="'.$barcode_order.'">';
            } else {
                $barcode_order_img = '';
            }

            /*
             * Quick & Dirty approach
             * X-BARCODE 616
             * TODO: rework this below
             */

            $logo_order_barcode_interchange = $str_logo_url;

            if (!empty($barcode_order_img)) {
                switch (Mage::getStoreConfig('barcode/order/invoice_position')) {
                    case '0':
                        $barcode_order_img_top_left = $barcode_order_img;
                        break;
                    case '1':
                        /*
                         * Case of including logo
                         */
                        $logo_order_barcode_interchange = (!empty($str_logo_url)) ? $str_logo_url : $barcode_order_img;
                        break;
                    case '2':
                        $barcode_order_img_btm_left = $barcode_order_img;
                        break;
                    case '3':
                        $barcode_order_img_btm_right = $barcode_order_img;
                        break;
                }
            }

            $invoice_id = $invoice->getIncrementId();
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
            for($i=0;$i<count($billingAddress);$i++){
                $str_billing_address .= "&nbsp;&nbsp;" . $billingAddress[$i] . "<br>";
            }
            $str_billing_address = substr_replace($str_billing_address ,"",-4);

            /* Shipping address */
            $str_shipping_address = "";
            for($i=0;$i<count($shippingAddress);$i++){
                $str_shipping_address .= "&nbsp;&nbsp;" . $shippingAddress[$i] . "<br>";
            }
            $str_shipping_address = substr_replace($str_shipping_address ,"",-4);

            $product_data = array();
            $i = 0;
            foreach ($invoice->getAllItems() as $item){
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }

                //$product_name = Mage::helper('core/string')->str_split($item->getName(), 35, true, true);
                $product_name = $item->getName();
                $product_data[$i]['name'] = $product_name;
                $sku = Mage::helper('core/string')->str_split($this->getSku($item), 17);
                $product_data[$i]['sku'] = $sku[0];
                $product_data[$i]['qty'] = $item->getQty() * 1;
                $product_data[$i]['tax'] = $order->formatPriceTxt($item->getTaxAmount());
                $prices = $this->getItemPricesForDisplay($order,$item);
                $product_data[$i]['price'] = $prices[0]['price'];
                $product_data[$i]['subtotal'] = $prices[0]['subtotal'];
                $productId = $item->getOrderItem()->getProductId();
                $code = Mage::getStoreConfig('barcode/general/symbology');
                $helper = Mage::helper('barcode/barcode');
                //$helper->printBarcodeAbs($productId,120,50);

                switch($item->getOrderItem()->getProduct()->getTypeId()){
                    case 'configurable':
                        $productId = Mage::getModel('catalog/product')->getIdBySku($item->getOrderItem()->getSku());
                        $helper->createProductBarcode($productId);
                        break;

                    case 'bundle':
                        $bundle_serialize =  unserialize($item->getOrderItem()->getData('product_options'));
                        foreach($bundle_serialize['bundle_options'] as $option_items){
                            foreach($option_items['value'] as $option_item){
                                $option_simple_product = Mage::getModel('catalog/product')
                                    ->loadByAttribute('name',$option_item['title']);
                                $productId = $option_simple_product->getId();
                                $productIdBundleList[] = $productId;
                                $helper->createProductBarcode($productId);
                            }
                        }
                        break;
                    case 'downloadable':
                        $productId = $item->getOrderItem()->getProductId();
                        break;
                    default: //simple
                        $productId = $item->getOrderItem()->getProductId();
                        $helper->createProductBarcode($productId);
                        break;
                }

                $product_type = $item->getOrderItem()->getProduct()->getTypeId();
                $media_path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
                if($product_type != 'bundle' && $product_type != 'downloadable' && $product_type != null){
                    $imagePath = $media_path . DS . 'barcode' . DS .$productId.'_'. $code .'_bc.png';
                    $imagePath = str_replace("\\","/",$imagePath);
                    $product_data[$i]['barcode'] = $imagePath;
                }else{
                    $product_data[$i]['barcode'] = "";
                }
                $i++;

                $result = array();
                if ($options = $item->getOrderItem()->getProductOptions()) {
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

            }

            $str_data = "";
            foreach($product_data as $data){
                $str_data .= '<tr>';
                if($product_options == null){
                    $str_data .= '<td>'.$data['name'].'</td>';
                }else{
                    $str_data .= '<td>'.$data['name'].'<br>';
                    /*
                     * XBAR-503
                     */
                    if ($product_options[$data['sku']] != null) {
                        foreach ($product_options[$data['sku']] as $option) {
                            $str_data .= $option['attr']. ' : ' . $option['attr_val'] . "(size) <br>";
                        }
                    }
                    $str_data = substr_replace($str_data ,"",-4);
                    $str_data .= '</td>';
                }
                $str_img = "";
                if($data['barcode'] != ''){
                    $str_img = '<img src="'.$data['barcode'].'">';
                }
                $str_data .= '<td align="left" valign="top">'.$str_img.'</td>';
                $str_data .= '<td align="center">'.$data['sku'].'</td>';
                if($product_type == 'bundle'){
                    $str_data .= '<td align="center">&nbsp;</td>';
                    $str_data .= '<td align="center">&nbsp;</td>';
                    $str_data .= '<td align="center">&nbsp;</td>';
                    $str_data .= '<td align="center">&nbsp;</td>';
                }else{
                    $str_data .= '<td align="center">'.$data['price'].'</td>';
                    $str_data .= '<td align="center">'.$data['qty'].'</td>';
                    $str_data .= '<td align="center">'.$data['tax'].'</td>';
                    $str_data .= '<td align="center">'.$data['subtotal'].'</td>';
                }
                $str_data .= '</tr>';

                $a = 0;
                if(isset($bundle_serialize['bundle_options']))
                {
                    foreach($bundle_serialize['bundle_options'] as $product_item){

                        $imagePath = $media_path . DS . 'barcode' . DS .$productIdBundleList[$a].'_'. $code .'_bc.png';
                        $imagePath = str_replace("\\","/",$imagePath);
                        $str_data .= '<tr>';
                        $str_data .= '<td>'.$product_item['label']. '<br>' . $product_item['value'][0]['title'] . '</td>';
                        $str_data .= '<td align="left" valign="top">'.'<img src="'.$imagePath.'">'.'</td>';
                        $str_data .= '<td align="center">&nbsp;</td>';
                        $str_data .= '<td align="center">'.$order->formatPriceTxt($product_item['value'][0]['price']).'</td>';
                        $str_data .= '<td align="center">'.$product_item['value'][0]['qty'].'</td>';
                        $str_data .= '<td align="center">'.$data['tax'].'</td>';
                        $str_data .= '<td align="center">'.$order->formatPriceTxt($product_item['value'][0]['qty'] * $product_item['value'][0]['price']).'</td>';
                        $str_data .= '</tr>';
                        $a++;
                    }
                }
            }

            $base_subtotal = $order->formatPriceTxt($order->getBaseSubtotal());
            $base_tax = $order->formatPriceTxt($order->getBaseTaxAmount());
            $amount = $order->formatPriceTxt($order->getBaseGrandTotal());

            /*$totals = $this->_getTotalsList($invoice);
            foreach ($totals as $total) {
                $total->setOrder($order)->setSource($invoice);

                if ($total->canDisplay()) {
                    foreach ($total->getTotalsForDisplay() as $totalData) {
                        $amount = $totalData['amount'];
                    }
                }
            }*/

        }

        /*$adress_store = Mage::getStoreConfig("general/store_information/address");*/

        $showBarcode =0;

        $html = '
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
        <td align="left">'.$barcode_order_img_top_left.'</td>
        <td align="right">'.$logo_order_barcode_interchange.'</td>
    </tr>
    <tr>
        <td align="left">
            Invoice # '.$invoice_id.'<br>
            Order # '.$order_id.'<br>
            Order Date: '.$order_date.'
        </td>
        <td align="right">
        ';

        $html.='
            '.Mage::getStoreConfig("general/store_information/name").'<br>
            '.Mage::getStoreConfig("general/store_information/address").'<br>
            '.Mage::getStoreConfig("general/store_information/phone").'
        </td>
    </tr>
</table>
<br>
<br>
<br>
<table class="table_order" cellpadding="4" cellspacing="0">
    <tr>
        <td width="50%" style="font-size:14px;">&nbsp;&nbsp;<b>Sold to:</b></td>
        <td width="50%" style="font-size:14px;">&nbsp;&nbsp;<b>Ship to:</b></td>
    </tr>
    <tr>
        <td>'.$str_billing_address.'</td>
        <td>'.$str_shipping_address.'</td>
    </tr>
</table>

<p>&nbsp;</p>

<table class="table_order" cellpadding="4" cellspacing="0">
    <tr>
        <td width="50%" style="font-size:14px;">&nbsp;&nbsp;<b>Payment Method:</b></td>
        <td width="50%" style="font-size:14px;">&nbsp;&nbsp;<b>Shipping Method:</b></td>
    </tr>
    <tr>
        <td>&nbsp;&nbsp;'.$payment_method.'</td>
        <td>&nbsp;&nbsp;'.$shippingMethod.'<br><br>&nbsp;&nbsp;'.$totalShippingChargesText.'</td>
    </tr>
</table>

<p>&nbsp;</p>

<table class="table_product" cellpadding="4" cellspacing="0">
    <tr>
        <td width="33%" height="20px" style="font-size:14px;border-bottom: 1px solid #B6B5B5;" align="center">&nbsp;&nbsp;Products</td>
        <td width="14%" height="20px" style="font-size:14px;border-bottom: 1px solid #B6B5B5;" align="left">&nbsp;&nbsp;Barcode</td>
        <td width="14%" height="20px" style="font-size:14px;border-bottom: 1px solid #B6B5B5;" align="center">&nbsp;&nbsp;SKU</td>
        <td width="11%" height="20px" style="font-size:14px;border-bottom: 1px solid #B6B5B5;" align="center">&nbsp;&nbsp;Price</td>
        <td width="6%" height="20px" style="font-size:14px;border-bottom: 1px solid #B6B5B5;" align="center">&nbsp;&nbsp;Qty</td>
        <td width="10%" height="20px" style="font-size:14px;border-bottom: 1px solid #B6B5B5;" align="center">&nbsp;&nbsp;Tax</td>
        <td width="12%" height="20px" style="font-size:14px;border-bottom: 1px solid #B6B5B5;" align="center">&nbsp;&nbsp;Subtotal</td>
    </tr>
    '.$str_data.'
    <tr>
        <td width="33%" height="20px" style="font-size:14px;border-top: 1px solid #B6B5B5;">&nbsp;</td>
        <td width="14%" height="20px" style="font-size:14px;border-top: 1px solid #B6B5B5;" align="center">&nbsp;</td>
        <td width="14%" height="20px" style="font-size:14px;border-top: 1px solid #B6B5B5;" align="center">&nbsp;</td>
        <td width="11%" height="20px" style="font-size:14px;border-top: 1px solid #B6B5B5;" align="center">&nbsp;</td>
        <td width="6%" height="20px" style="font-size:14px;border-top: 1px solid #B6B5B5;" align="center">&nbsp;</td>
        <td width="10%" height="20px" style="font-size:14px;border-top: 1px solid #B6B5B5;" align="center">&nbsp;</td>
        <td width="12%" height="20px" style="font-size:14px;border-top: 1px solid #B6B5B5;" align="center">&nbsp;</td>
    </tr>
</table>

<p>&nbsp;</p>

<table class="table_total" cellpadding="4" cellspacing="0">
    <tr>
        <td align="right" width="85%">Subtotal:</td>
        <td align="right" width="15%">'.$base_subtotal.'</td>
    </tr>
    <tr>
        <td align="right" width="85%">Shipping & Handling:</td>
        <td align="right">'.$shipping_charges.'</td>
    </tr>
    <tr>
        <td align="right" width="85%">Tax:</td>
        <td align="right">'.$base_tax.'</td>
    </tr>
    <tr>
        <td align="right" width="85%">Grand Total:</td>
        <td align="right">'.$amount.'</td>
    </tr>
</table>

<table class="table_header" cellpadding="4" cellspacing="0">
    <tr>
        <td align="left">'.$barcode_order_img_btm_left.'</td>
        <td align="right">'.$barcode_order_img_btm_right.'</td>
    </tr>
</table>

';
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
        if ($item->getOrderItem()->getProductOptionByCode('simple_sku'))
            return $item->getOrderItem()->getProductOptionByCode('simple_sku');
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

}
