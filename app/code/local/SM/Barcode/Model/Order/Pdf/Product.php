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

class SM_Barcode_Model_Order_Pdf_Product extends TCPDF
{

    //Get whole pdf file
    public function getPdf($data = array('type'=>''))
    {
        //--------------------- Config TCPDF ---------------------------
        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
//        ob_start();
        ob_clean();
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
        $pdf->SetMargins(0, PDF_MARGIN_TOP, 0);

        //set auto page breaks
        $pdf->SetAutoPageBreak(FALSE, 15);

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
        $getType = isset($data['type']) ? $data['type'] : '';
        switch ($getType) {
            case 'preview':

                if(isset($data['symbology'])) Mage::getModel('core/config')->saveConfig('barcode/general/symbology', $data['symbology']);
                $data['symbology'] = Mage::getStoreConfig('barcode/general/symbology');
                $data['price']['settings'] = $this->getSettings($data['priceSettings'],$data['type']);
                $data['barcode']['settings'] = $this->getSettings($data['barcodeSettings'], $data['type']);
                $data['logo']['settings'] = $this->getSettings($data['logoSettings'],$data['type']);
                $data['product']['settings'] = $this->getSettings($data['productNameSettings'],$data['type']);
                $data['slot1']['settings'] = $this->getSettings($data['slot1Settings'],$data['type']);
                $data['slot2']['settings'] = $this->getSettings($data['slot2Settings'],$data['type']);
                $data['slot3']['settings'] = $this->getSettings($data['slot3Settings'],$data['type']);
                $data['slot4']['settings'] = $this->getSettings($data['slot4Settings'],$data['type']);
                $data['slot1']['attribute'] = $data['slot1Attribute'];
                $data['slot2']['attribute'] = $data['slot2Attribute'];
                $data['slot3']['attribute'] = $data['slot3Attribute'];
                $data['slot4']['attribute'] = $data['slot4Attribute'];
                break;

            default: // Get from Store config

                $data['symbology'] = Mage::getStoreConfig('barcode/general/symbology');
                $input_unit = Mage::getStoreConfig('barcode/general/input_size_unit');

                $data['orientation'] = Mage::getStoreConfig('barcode/product/orientation');
//                $data['paperSize'] = 'A4';
                $data['paperSize'] = Mage::getStoreConfig('barcode/product/paper_size');
                if($data['paperSize'] == 'Custom'){
                    $data['paperWidth'] = Mage::getStoreConfig('barcode/product/paper_width');
                    $data['paperHeight'] = Mage::getStoreConfig('barcode/product/paper_height');
                }
                $data['rows'] = Mage::getStoreConfig('barcode/product/rows_display');
                $data['cols'] = Mage::getStoreConfig('barcode/product/columns_display');
                $data['width'] = Mage::getStoreConfig('barcode/product/width');
                $data['height'] = Mage::getStoreConfig('barcode/product/height');

                $data['barcodeWidth'] = Mage::getStoreConfig('barcode/product/barcode_width');
                $data['barcodeHeight'] = Mage::getStoreConfig('barcode/product/barcode_height');

                $data['labelPaddingTop'] = Mage::getStoreConfig('barcode/product/label_padding_top');
                $data['labelPaddingBottom'] = Mage::getStoreConfig('barcode/product/label_padding_bottom');
                $data['labelPaddingLeft'] = Mage::getStoreConfig('barcode/product/label_padding_left');
                $data['labelPaddingRight'] = Mage::getStoreConfig('barcode/product/label_padding_right');

                $data['labelMarginTop'] = Mage::getStoreConfig('barcode/product/label_margin_top');
                $data['labelMarginLeft'] = Mage::getStoreConfig('barcode/product/label_margin_left');

                $data['marginTop'] = Mage::getStoreConfig('barcode/product/page_margin_top');
                $data['marginLeft'] = Mage::getStoreConfig('barcode/product/page_margin_left');

                $data['logoWith'] = Mage::getStoreConfig('barcode/product/logo_width');
                $data['logoHeight'] = Mage::getStoreConfig('barcode/product/logo_height');
                $data['logoTop'] = Mage::getStoreConfig('barcode/product/logo_padding_top');
                $data['logoLeft'] = Mage::getStoreConfig('barcode/product/logo_padding_left');
                $data['includeLogo'] = Mage::getStoreConfig('barcode/product/include_logo');

                $data['fontSize'] = Mage::getStoreConfig('barcode/product/font_size');
                $data['font'] = Mage::getStoreConfig('barcode/product/use_font_for_text');


                $data['showProductName'] = Mage::getStoreConfig('barcode/product/name_visible');
                $data['productNameLeng'] = Mage::getStoreConfig("barcode/product/product_name_leng");
                $data['product']['settings'] = $this->getSettings('barcode/product/product_name_settings');

                $data['showPrice'] = Mage::getStoreConfig('barcode/product/price_visible');
                $data['price']['settings'] = $this->getSettings('barcode/product/price_settings');

                $data['showSlot1'] = Mage::getStoreConfig('barcode/product/slot1_visible');
                $data['slot1']['attribute'] = Mage::getStoreConfig('barcode/product/slot1');
                $data['slot1']['settings'] = $this->getSettings('barcode/product/slot1_settings');

                $data['showSlot2'] = Mage::getStoreConfig('barcode/product/slot2_visible');
                $data['slot2']['attribute'] = Mage::getStoreConfig('barcode/product/slot2');
                $data['slot2']['settings'] = $this->getSettings('barcode/product/slot2_settings');

                $data['showSlot3'] = Mage::getStoreConfig('barcode/product/slot3_visible');
                $data['slot3']['attribute'] = Mage::getStoreConfig('barcode/product/slot3');
                $data['slot3']['settings'] = $this->getSettings('barcode/product/slot3_settings');

                $data['showSlot4'] = Mage::getStoreConfig('barcode/product/slot4_visible');
                $data['slot4']['attribute'] = Mage::getStoreConfig('barcode/product/slot4');
                $data['slot4']['settings'] = $this->getSettings('barcode/product/slot4_settings');

                $data['logo']['settings'] = $this->getSettings('barcode/product/logo_settings');
                $data['barcode']['settings'] = $this->getSettings('barcode/product/barcode_settings');

                $data['isDebugEnabled'] = 'no';

                break;

        }

        $data['logo']['path'] = file_exists(Mage::getBaseDir('media') . DS . 'barcode' . DS . Mage::getStoreConfig("barcode/general/logo_image_file")) ? Mage::getBaseDir('media') . DS . 'barcode' . DS . Mage::getStoreConfig("barcode/general/logo_image_file") : Mage::getBaseDir('media') . DS . 'barcode' . DS  . "logo.png";
        $data['border'] = 0;
        $data['debugMode'] = false;

        if ($data['isDebugEnabled'] == 'yes') {
            $data['debugMode'] = true;
            $data['border'] = 1;
        }
        $data['totalItems'] = 0;
        $data['from'] = 0;
        $data['itemPerPage'] = $data['cols'] * $data['rows'];

        //Get all product IDs
        foreach ($data['product_ids']['request'] as $product_id => $qty) {
            $data['totalItems'] += $qty;
            for ($i = 0; $i < $qty; $i++) {
                $data['product_ids']['product_id'][] = $product_id;
            }
        }
        if ($data['totalItems'] >= $data['itemPerPage']) {
            $data['to'] = $data['itemPerPage'];
        } else {
            $data['to'] = $data['totalItems'];
        }

        //Write
        //Paging
        $data['currentProductId'] = 0;
        $data['nextProductId'] = 0;
        $numberPages = ceil($data['totalItems'] / $data['itemPerPage']);

        for ($i = 0; $i < $numberPages; $i++) {
            if ($i == 0) {
                $data['nextPageFrom'] = '0';
                $this->getPdfPage($pdf, $data);
            } else {
                if ($i * $data['itemPerPage'] < $data['totalItems']) {
                    if ($data['totalItems'] - $i * $data['itemPerPage'] > $data['itemPerPage']) {
                        $data['to'] += $data['itemPerPage'];
                    } else {
                        $data['to'] = $data['totalItems'];
                    }
                } else {
                    $data['to'] = $data['totalItems'];
                }
                $data['from'] += $data['itemPerPage'];
                $data['nextPageFrom'] = $data['from'];
                $this->getPdfPage($pdf, $data);
            }
        }



        $pdf->lastPage();

        $barcode_dir = Mage::getBaseDir('media') . DS . 'barcode' . DS . 'pdf' . DS;
        if ($data['debugMode']) {
            return $pdf->Output($barcode_dir . 'product_barcode_' . date('Y-m-d-h-i-s') . '.pdf', 'I'); // D ; FD ; I
        } else {
            return $pdf->Output($barcode_dir . 'product_barcode_' . date('Y-m-d-h-i-s') . '.pdf', 'FD'); // D ; FD ; I
        }

    }

    //Get a page of pdf
    public function getPdfPage($pdf, $data)
    {

        // set font
        $pdf->SetFont($data['font'], '', $data['fontSize']);

        if($data['paperSize'] == 'Custom'){
            $resolution = array($data['paperWidth'],$data['paperHeight']);
            $pdf->AddPage($data['orientation'], $resolution);
        }
        else {
            $pdf->AddPage($data['orientation'], $data['paperSize']);
        }

        $pdf->setJPEGQuality(100);

        // set cell padding
        $pdf->setCellPaddings($data['labelPaddingLeft'], $data['labelPaddingTop'], $data['labelPaddingRight'], $data['labelPaddingBottom']);

        // set cell margins
        $pdf->setCellMargins($data['labelMarginLeft'], $data['labelMarginTop'], 0, 0);

        $pdf->SetFillColor(255, 255, 255);

        $x = $data['marginLeft'];
        $y = $data['marginTop'];

        for ($i = $data['from']; $i < $data['to']; $i++) {

            $data['i'] = $i;
            $data['currentProductId'] = $data['product_ids']['product_id'][$i];
            if ($data['from'] + 1 < $data['to']) {
                $data['nextProductId'] = $data['product_ids']['product_id'][$i + 1];
            } else {
                $data['nextProductId'] = $data['product_ids']['product_id'][$i];
            }

            $productData = $this->getProductInfo($data);


            if (($i % $data['cols'] == 0)) {

                /*
                 * productData values as array
                 * $productData['obj']['value']:  value of object
                 * $productData['obj']['settings'][0]: X
                 * $productData['obj']['settings'][1]: Y
                 * $productData['obj']['settings'][2]: Font size: 10
                 * $productData['obj']['settings'][3]: Font style: B,I,BI
                 */


                if ($i == 0) { // First item of page
                    $y = $data['marginTop'] + $data['labelMarginTop'];

                    //Product name

                }
                if ($i != 0) { // First item -> 2nd rows
                    $pdf->ln(4); // Add break line
                    $x = $data['marginLeft']; // Reset X
                    if ($data['nextPageFrom'] == $i) { //First Label in the next page.
                        $y = $data['marginTop'] + $data['labelMarginTop'];
                    } else { // Next row
                        $y += $data['height'] + $data['labelMarginTop']; // Move Y down 1 row
                    }
                }
            }
            //This MultiCell for making border as debug mode.
            $pdf->MultiCell($data['width'], $data['height'], '', $data['border'], 'L', 1, 0, $x, $y, true, 4, true, true, 22, 'T', true);
            //Show product name
            if ($data['showProductName'] == 1) {
                $style = '';
                //Custom font size
                if (isset($productData['product']['settings'][2])) $style .= 'font-size:' . $productData['product']['settings'][2] . ';';
                //Add a line
                $pdf->writeHTMLCell($data['productNameLeng'], $data['fontSize'], $x + $productData['product']['settings'][0], $y + $productData['product']['settings'][1], $this->setStyleToText($productData['product']['settings'][3], $style, $productData['product']['value']), $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = '', $autopadding = true);
            }

            //Show product price
            if ($data['showPrice'] == 1) {
                $style = '';
                //Custom font size
                if (isset($productData['price']['settings'][2])) $style .= 'font-size:' . $productData['price']['settings'][2] . ';';
                //Add a line
                $pdf->writeHTMLCell($data['width'], $data['fontSize'], $x + $productData['price']['settings'][0], $y + $productData['price']['settings'][1], $this->setStyleToText($productData['price']['settings'][3],$style, $productData['price']['value']), $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = '', $autopadding = true);
            }

            //Show slot 1
            if ($data['showSlot1'] == 1) {
                $style = '';
                //Custom font size
                if (isset($productData['slot1']['settings'][2])) $style .= 'font-size:' . $productData['slot1']['settings'][2] . ';';
                //Add a line
                $pdf->writeHTMLCell($data['width'], $data['fontSize'], $x + $productData['slot1']['settings'][0], $y + $productData['slot1']['settings'][1], $this->setStyleToText($productData['slot1']['settings'][3],$style, $productData['slot1']['value']), $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = '', $autopadding = true);
            }

            //Show slot 2
            if ($data['showSlot2'] == 1) {
                $style = '';
                //Custom font size
                if (isset($productData['slot2']['settings'][2])) $style .= 'font-size:' . $productData['slot2']['settings'][2] . ';';
                //Add a line
                $pdf->writeHTMLCell($data['width'], $data['fontSize'], $x + $productData['slot2']['settings'][0], $y + $productData['slot2']['settings'][1], $this->setStyleToText($productData['slot2']['settings'][3], $style, $productData['slot2']['value']), $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = '', $autopadding = true);
            }

            //Show slot 3
            if ($data['showSlot3'] == 1) {
                $style = '';
                //Custom font size
                if (isset($productData['slot3']['settings'][2])) $style .= 'font-size:' . $productData['slot3']['settings'][2] . ';';
                //Add a line
                $pdf->writeHTMLCell($data['width'], $data['fontSize'], $x + $productData['slot3']['settings'][0], $y + $productData['slot3']['settings'][1], $this->setStyleToText($productData['slot3']['settings'][3], $style, $productData['slot3']['value']), $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = '', $autopadding = true);
            }

            //Show slot 4
            if ($data['showSlot4'] == 1) {
                $style = '';
                //Custom font size
                if (isset($productData['slot4']['settings'][2])) $style .= 'font-size:' . $productData['slot4']['settings'][2] . ';';
                //Add a line
                $pdf->writeHTMLCell($data['width'], $data['fontSize'], $x + $productData['slot4']['settings'][0], $y + $productData['slot4']['settings'][1], $this->setStyleToText($productData['slot4']['settings'][3], $style, $productData['slot4']['value']), $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = '', $autopadding = true);
            }

            //Show logo
            if (Mage::getStoreConfig("barcode/general/logo_image_file") != null && $data['includeLogo'] == '1') {
                $pdf->Image($data['logo']['path'], $x + $data['labelMarginLeft'] + $productData['logo']['settings'][0], $y + $data['labelMarginTop'] + $productData['logo']['settings'][1], $data['logoWith'], $data['logoHeight'], '', '', 'T', false, 300, '', false, false, 0, false, false, false);
            }

            //Show barcode image
            $pdf->Image($productData['barcode']['value'], $x + $data['labelMarginLeft'] + $productData['barcode']['settings'][0], $y + $data['labelMarginTop'] + $productData['barcode']['settings'][1], $data['barcodeWidth'], $data['barcodeHeight'], '', '', 'T', false, 300, '', false, false, 0, false, false, false);
            $x += $data['width'] + $data['labelMarginLeft'];

            $data['from']++;
        }
        $pdf->ln(4);

        return $data;
    }

    function getProductInfo($data)
    {
        $return = array();

        //Set product values
        $productId = $data['product_ids']['product_id'][$data['i']];
        $product = Mage::getModel('catalog/product')->load($productId);
        $return['product']['value'] = $product->getName();
        $return['product']['settings'] = $data['product']['settings'];

        //Set price values
        $return['price']['value'] =  Mage::helper('core')->currency($product->getPrice(), true, false);
        $return['price']['settings'] = $data['price']['settings'];

        //Slot 1
        if ($data['showSlot1'] == '1') {
//            $return['slot1']['value'] = $product->getResource()->getAttribute($return['slot1']['value'])->getFrontend()->getValue($product);
            $return['slot1']['code'] = Mage::getModel('eav/entity_attribute')->load($data['slot1']['attribute'])->getAttributeCode();
            $return['slot1']['value'] = $this->getFormatAttribute($return['slot1']['code'],$product);
            $return['slot1']['settings'] = $data['slot1']['settings'];
        }
        //Slot 2
        if ($data['showSlot2'] == '1') {
            $return['slot2']['code'] = Mage::getModel('eav/entity_attribute')->load($data['slot2']['attribute'])->getAttributeCode();
            $return['slot2']['value'] = $this->getFormatAttribute($return['slot2']['code'],$product);
            $return['slot2']['settings'] = $data['slot2']['settings'];
        }
        //Slot 3
        if ($data['showSlot3'] == '1') {
            $return['slot3']['code'] = Mage::getModel('eav/entity_attribute')->load($data['slot3']['attribute'])->getAttributeCode();
            $return['slot3']['value'] = $this->getFormatAttribute($return['slot3']['code'],$product);
            $return['slot3']['settings'] = $data['slot3']['settings'];
        }
        //Slot 4
        if ($data['showSlot4'] == '1') {
            $return['slot4']['code'] = Mage::getModel('eav/entity_attribute')->load($data['slot4']['attribute'])->getAttributeCode();

            $return['slot4']['value'] = $this->getFormatAttribute($return['slot4']['code'],$product);
            $return['slot4']['settings'] = $data['slot4']['settings'];
        }

        //Set logo
        if ($data['includeLogo'] == '1') {
            $return['logo']['settings'] = $data['logo']['settings'];
        }

        //Set Barcode
        $isBarcodeCreated = Mage::helper('barcode/barcode')->createProductBarcode($productId);
        if($isBarcodeCreated){
            $return['barcode']['value'] = Mage::helper('barcode/barcode')->getBarcodePath($productId, 'path');
            if(!file_exists($return['barcode']['value'])){
                $return['barcode']['value'] = Mage::getBaseDir('media') . DS . "barcode" . DS . 'default' . DS . 'barcodeerror.png';
            }
        } else{
            $return['barcode']['value'] = Mage::getBaseDir('media') . DS . "barcode" . DS . 'default' . DS . 'barcodeerror.png';
        }

        $return['barcode']['settings'] = $data['barcode']['settings'];

        return $return;
    }

    public function getSettings($config,$type='default')
    {
        $settings = array();
        switch($type){
            case 'preview':
                break;

            default:
                $config = Mage::getStoreConfig($config);
                break;
        }
        if (!empty($config)) {
            $config = explode(',', $config);
            if (is_array($config) && count($config) > 0) {
                foreach ($config as $value) {
                    $value = trim(strip_tags($value));
                    $value = !empty($value) ? $value : '';
                    $settings[] = $value;
                }
                //Check Default values
                if(empty($settings[0])) $settings[0] = 0;
                if(empty($settings[1])) $settings[1] = 0;
                if(empty($settings[2])) $settings[2] = 9;

            } else {
                $settings = array(2, 5, 9, '');
            }
        } else {
            $settings = array(2, 5, 9, '');
        }

        //Check value
        if(!isset($settings[0])) $settings[0] = 0;
        if(!isset($settings[1])) $settings[1] = 0;
        if(!isset($settings[2])) $settings[2] = 0;
        if(!isset($settings[3])) $settings[3] = '';

        return $settings;
    }

    public function getCustomStyle($setting)
    {
        $style = '';
        $chars = str_split(strtolower($setting));
        foreach($chars as $char){
            $style .= $this->setCustomStyle($char,$style);
        }
        return $style;
    }

    /*
     * Add span to attribute
     */
    public function setStyleToText($setting, $style, $text)
    {
        $style .= $this->getCustomStyle($setting);
        return '<span style="' . $style . '">' . $text . '</span>';
    }

    /*
     * Set custom CSS style to attribute
     */
    public function setCustomStyle($char,$style){
        switch ($char) {
            case 'b':
                $style .= 'font-weight: bold;';
                break;

            case 'i':
                $style .= 'font-style: italic;';
                break;

            case 'u':
                $style .='text-decoration:underline;';
                break;

            case 's':
                $style .='text-decoration:line-through;';
                break;

            case 'o':
                $style .='text-decoration: overline;';
                break;

            default:
                $style .= '';
                break;
        }
        return $style;
    }

    /*
     * Format attribute
     */
    public function getFormatAttribute($code,$product){
        $attribute = $product->getResource()->getAttribute($code);
        $value = $product->getData($code);
        switch($attribute['frontend_input']){
            case 'special_price':
            case 'price':
                if(!empty($value)){
                    $value = Mage::helper('core')->currency($value, true, false);
                }
                break;

            case 'multiselect':
            case 'select':
                $options = explode(',',$value);
                $options_value = '';
                $prefix = count($options) == 1 ? '' : ' / ';
                $count = 0;
                foreach($options as $option){
                    $option_text = $attribute->getSource()->getOptionText($option);
                    $count++;
                    if($count == 1){
                        $options_value .= $option_text ;
                    } else{
                        $options_value .= $prefix . $option_text;
                    }
                }
                $value = $options_value;
                break;
            default:
                break;
        }

        return $value;
    }
}
