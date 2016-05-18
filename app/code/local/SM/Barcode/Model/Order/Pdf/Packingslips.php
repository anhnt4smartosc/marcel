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

class SM_Barcode_Model_Order_Pdf_Packingslips extends TCPDF
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
        /*$pdf->SetMargins(10, PDF_MARGIN_TOP, 10);*/


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
            $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                ->setOrderFilter($orderId)
                ->load();

            if ($shipments->getSize() > 0) {
                $flag = true;
                $this->getPdfPage($pdf, $shipments);
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
    public function getPdfPage($pdf, $shipments)
    {
        // set font
        $pdf->SetFont('freemono', '', '14');
        $pdf->AddPage('P', 'A4');
        $pdf->setJPEGQuality(100);

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
        } else {
            $str_logo_url = '';
        }

        foreach ($shipments as $shipment) {
            if ($shipment->getStoreId()) {
                Mage::app()->getLocale()->emulate($shipment->getStoreId());
                Mage::app()->setCurrentStore($shipment->getStoreId());
            }
            $order = $shipment->getOrder();

            if (Mage::getStoreConfig('barcode/order/packingslip_enabled') == '1') {
                $barcode_order = Mage::helper('barcode/barcode')->createOrderBarcode($order->getRealOrderId());

                /*
                 * Quick & Dirty
                 * XBAR-661
                 */
                $barcodeOrderFileName = (strlen($order->getRealOrderId()) > 9) ? str_replace("-", "", $order->getRealOrderId()) : $order->getRealOrderId();

                $barcode_order = $media_path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . "barcode/order/". $barcodeOrderFileName .".png";
                $barcode_order_img = '<img style="padding:10px;" height="50" src="'.$barcode_order.'">';
            } else {
                $barcode_order_img = '';
            }

            /*
             * Quick & Dirty approach
             * X-BARCODE 617
             * TODO: rework this below
             */

            $logo_order_barcode_interchange = $str_logo_url;

            if (!empty($barcode_order_img)) {
                switch (Mage::getStoreConfig('barcode/order/packingslip_position')) {
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

            $packingslip_id = $shipment->getIncrementId();
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
            foreach ($shipment->getAllItems() as $item){
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }

                $product_name = $item->getName();
                $product_data[$i]['name'] = $product_name;
                $sku = Mage::helper('core/string')->str_split($this->getSku($item), 17);
                $product_data[$i]['sku'] = $sku[0];
                $product_data[$i]['qty'] = $item->getQty() * 1;
                $prices = $this->getItemPricesForDisplay($order,$item);
                $product_data[$i]['price'] = $prices[0]['price'];
                $product_data[$i]['subtotal'] = $prices[0]['subtotal'];
                $productId = $item->getOrderItem()->getProductId();
                $code = Mage::getStoreConfig('barcode/general/symbology');
                $helper = Mage::helper('barcode/barcode');

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
                                $productSku = $option_simple_product->getSku();
                                $productIdBundleList[] = $productId;
                                $productSkuBundleList[] = $productSku;
                                $helper->createProductBarcode($productId);
                            }
                        }
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
                $str_data .= '<td align="center">'.$data['qty'].'</td>';
                if($product_options == null){
                    $str_data .= '<td>'.$data['name'].'</td>';
                }else{
                    $str_data .= '<td>'.$data['name'].'<br>';

                    /*
                     * XBAR-503
                     */
                    if ($product_options[$data['sku']] != null) {
                        foreach($product_options[$data['sku']] as $option){
                            $str_data .= $option['attr']. ' : ' . $option['attr_val'] . "<br>";
                        }
                    }
                    $str_data = substr_replace($str_data ,"",-4);
                    $str_data .= '</td>';
                }
                $str_img = "";
                if($data['barcode'] != ''){
                    $str_img = '<img src="'.$data['barcode'].'">';
                }
                $str_data .= '<td align="center">'.$str_img.'</td>';
                $str_data .= '<td align="right">'.$data['sku'].'</td>';
                $str_data .= '</tr>';

                $a = 0;

                if(isset($bundle_serialize['bundle_options']))
                {
                    foreach($bundle_serialize['bundle_options'] as $product_item){

                        $imagePath = $media_path . DS . 'barcode' . DS .$productIdBundleList[$a].'_'. $code .'_bc.png';
                        $imagePath = str_replace("\\","/",$imagePath);
                        $str_data .= '<tr>';
                        $str_data .= '<td align="center">&nbsp;</td>';
                        $str_data .= '<td>'.$product_item['label']. '<br>' . $product_item['value'][0]['title'] . '&nbsp;' . $order->formatPriceTxt($product_item['value'][0]['price']) . '</td>';
                        $str_data .= '<td align="center">'.'<img src="'.$imagePath.'">'.'</td>';
                        $str_data .= '<td align="right">'.$productSkuBundleList[$a].'</td>';
                        $str_data .= '</tr>';
                        $a++;
                    }
                }
            }
        }


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
            Invoice # '.$packingslip_id.'<br>
            Order # '.$order_id.'<br>
            Order Date: '.$order_date.'
        </td>
        <td align="right">
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
        <td width="6%" height="20px" style="font-size:14px;border-bottom: 1px solid #B6B5B5" align="center">&nbsp;&nbsp;Qty</td>
        <td width="49%" height="20px" style="font-size:14px;border-bottom: 1px solid #B6B5B5;" align="center">&nbsp;&nbsp;Products</td>
        <td width="30%" height="20px" style="font-size:14px;border-bottom: 1px solid #B6B5B5;" align="left">&nbsp;&nbsp;Barcode</td>
        <td width="15%" height="20px" style="font-size:14px;border-bottom: 1px solid #B6B5B5;" align="right">&nbsp;&nbsp;SKU</td>
    </tr>
    '.$str_data.'
    <tr>
        <td width="5%" height="20px" style="font-size:14px;border-top: 1px solid #B6B5B5" align="center">&nbsp;</td>
        <td width="50%" height="20px" style="font-size:14px;border-top: 1px solid #B6B5B5;">&nbsp;</td>
        <td width="30%" height="20px" style="font-size:14px;border-top: 1px solid #B6B5B5;" align="center">&nbsp;</td>
        <td width="20%" height="20px" style="font-size:14px;border-top: 1px solid #B6B5B5;" align="right">&nbsp;</td>
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

    protected $_defaultTotalModel = 'sales/order_pdf_total_default';

}
