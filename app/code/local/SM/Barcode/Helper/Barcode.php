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
 * @author     hoadx@smartosc.com
 * @copyright  Copyright (c) 2010-2011 SmartOSC Co. (http://www.smartosc.com)
 */
class SM_Barcode_Helper_Barcode extends SM_Barcode_Helper_Abstract
{

    protected $color_black;
    protected $color_white;

    protected $_layout_width;
    protected $_layout_height;
    protected $_barcode_width;
    protected $_barcode_height;
    protected $_layout_width_input;
    protected $_layout_height_input;
    protected $_barcode_width_input;
    protected $_barcode_height_input;
    protected $_logo_width;
    protected $_logo_height;
    protected $_logo = null;
    protected $_y = 0;
    protected $font;
    protected $bfont;
    protected $_width = 330;
    protected $_height = 120;
    protected $_barcodeWidth = 330;
    protected $_barcodeHeight = 120;

    protected $_final_width;
    protected $_final_height;
    public $_unit_output = 'pt'; //Barcode image, should be converted to pt
    public $_unit_px = 'px';
    protected $_text_padding_top;
    protected $_text_padding_left;

    public function __construct() {
        $this->color_black = new FColor(0, 0, 0);
        $this->color_white = new FColor(255, 255, 255);
        return parent::__construct();
    }

    private function _instantiateBarcode($sourceModelId, $field) {
        switch ($sourceModelId) {
            case 1:
                $code_generated = new code128(30, $this->color_black, $this->color_white, 1, $field, 3, "A");
                break;
            case 2:
                $code_generated = new code128(30, $this->color_black, $this->color_white, 1, $field, 3, "B");
                break;
            case 3:
                $code_generated = new code128(30, $this->color_black, $this->color_white, 1, $field, 3, "C");
                break;
            case 4:
                $code_generated = new code39(30, $this->color_black, $this->color_white, 1, $field, 3);
                break;
            case 5:
                $code_generated = new i25(30, $this->color_black, $this->color_white, 1, $field, 3);
                break;
            case 7:
                $code_generated = new upcb(30, $this->color_black, $this->color_white, 1, $field, 3);
                break;
            default:
                $code_generated = new ean13(30, $this->color_black, $this->color_white, 1, $field, 3);
                break;
        }

        return $code_generated;
    }

    protected function _initProductBarcode()
    {
        //Do not remove it
        if (is_null(Mage::getStoreConfig("barcode/product/bcodelayout"))) Mage::getModel('core/config')->saveConfig('general/config/bcodelayout', 0);

        //Unit conversion: All input unit will be converted to point
        $_unit_input = Mage::getStoreConfig("barcode/general/input_size_unit");

        $this->_layout_width = Mage::getStoreConfig("barcode/product/width");
        if (is_numeric($this->_layout_width) && $this->_layout_width > 0) {
            $this->_layout_width = $this->unitConverter($this->_layout_width, $_unit_input, $this->_unit_output);
        } else {
            $this->_layout_width = 330; //set default
        }

        $this->_layout_height = Mage::getStoreConfig("barcode/product/height");
        if (is_numeric($this->_layout_height) && $this->_layout_height > 0) {
            $this->_layout_height = $this->unitConverter($this->_layout_height, $_unit_input, $this->_unit_output);
        } else {
            $this->_layout_height = 150; //set  default
        }


        $this->_barcode_width = Mage::getStoreConfig("barcode/product/barcode_width");
        if (is_numeric($this->_barcode_width) && $this->_barcode_width > 0) {
            $this->_barcode_width = $this->unitConverter($this->_barcode_width, $_unit_input, $this->_unit_output);
        } else {
            $this->_barcode_width = 180; //set  default
        }

        $this->_barcode_height = Mage::getStoreConfig("barcode/product/barcode_height");
        if (is_numeric($this->_barcode_height) && $this->_barcode_height > 0) {
            $this->_barcode_height = $this->unitConverter($this->_barcode_height, $_unit_input, $this->_unit_output);
        } else {
            $this->_barcode_height = 62; //set  default
        }

        $this->_final_height = 0;
        $this->_final_height += 10; // for padding bottom

        $this->_final_width = 0;
        $this->_final_width += 10; // for padding right
        $this->_final_width += 20; //without using font

        $ifont = 4; // actual font when font are disable on backend

        if (Mage::getStoreConfig('barcode/product/use_font_for_text')) {
            // $ifont = Mage::getStoreConfig('barcode/product/line_height');
            $ifont = Mage::getStoreConfig('barcode/product/font_size');
            $this->_final_width -= 20;
        } else {
            $this->_final_height += $ifont * 2;
        }

        if (Mage::getStoreConfig("barcode/product/name_visible")) {
            $this->_final_height += $ifont * 1.5;
        }

        if (Mage::getStoreConfig("barcode/product/new_slot_visible1") != '') {
            $this->_final_height += $ifont * 1.5;
        }

        if (Mage::getStoreConfig("barcode/product/new_slot_visible2") != '') {
            $this->_final_height += $ifont * 1.5;
        }

        if (Mage::getStoreConfig("barcode/product/new_slot_visible3") != '') {
            $this->_final_height += $ifont * 1.5;
        }

        if (Mage::getStoreConfig("barcode/product/new_slot_visible4") != '') {
            $this->_final_height += $ifont * 1.5;
        }

        if (Mage::getStoreConfig("barcode/product/price_visible")) {
            $this->_final_height += $ifont * 1.5;
        }

        if (Mage::getStoreConfig("barcode/product/text_padding_top")) {
            $this->_text_padding_top = $this->unitConverter(Mage::getStoreConfig("barcode/product/text_padding_top"), $_unit_input, $this->_unit_output);
            $this->_final_height += $this->_text_padding_top;
        }

        if (Mage::getStoreConfig("barcode/product/text_padding_left")) {
            $this->_text_padding_left = $this->unitConverter(Mage::getStoreConfig("barcode/product/text_padding_left"), $_unit_input, $this->_unit_output);
        }


        $this->_final_width += $ifont * ($ifont - 8); // for name display


        if (Mage::getStoreConfig("barcode/product/include_logo") && strlen(Mage::getStoreConfig("barcode/product/logo_image_file")) > 0) {
            $logoFile = is_file("media/barcode/" . Mage::getStoreConfig("barcode/product/logo_image_file")) ? "media/barcode/" . Mage::getStoreConfig("barcode/product/logo_image_file") : "barcode/logo.png";
            if (!is_file($logoFile)) {
                return false;
            }

            $logoSize = getimagesize($logoFile);
            $this->_logo["file"] = $logoFile;
            $this->_logo["file_width"] = intval($logoSize["0"]);
            $this->_logo["file_height"] = intval($logoSize["1"]);

            //Convert to pt
            $bcodelayout = Mage::getStoreConfig("barcode/product/bcodelayout");
            $this->_logo_width = Mage::getStoreConfig("barcode/product/logo_width");
            $this->_logo_height = Mage::getStoreConfig("barcode/product/logo_height");
            if ($bcodelayout == 0) {
                if (is_numeric($this->_logo_width) && $this->_logo_width > 0)
                    $this->_logo["width"] = $this->_logo_width = $this->unitConverter($this->_logo_width, $_unit_input, $this->_unit_output);
                else
                    $this->_logo["height"] = $this->_logo_width = $logoSize["0"];

                if (is_numeric($this->_logo_height) && $this->_logo_height > 0)
                    $this->_logo["height"] = $this->_logo_height = $this->unitConverter($this->_logo_height, $_unit_input, $this->_unit_output);
                else
                    $this->_logo["height"] = $this->_logo_height = $logoSize["1"];

            } else {
                $this->_logo["width"] = $this->_logo_width = $logoSize["0"];
                $this->_logo["height"] = $this->_logo_height = $logoSize["1"];
            }


            // check if posible to display logo
            if ($this->_logo["height"] < 0 || $this->_logo["width"] < 0)
                $this->_logo["height"] = $this->_logo["width"] = 0;

            $this->_logo["position"] = Mage::getStoreConfig("barcode/product/logo_position");
            $this->_logo["resize"] = false;
            if ($this->_logo["width"] != $this->_logo["file_width"] || $this->_logo["height"] != $this->_logo["file_height"]) {
                $this->_logo["resize"] = true;
            }
            // Recalculate follow by position of logo

            if (Mage::getStoreConfig("barcode/product/logo_position") == "0" || Mage::getStoreConfig("barcode/product/logo_position") == "3") {
                if (Mage::getStoreConfig("barcode/product/logo_position") == "3") $this->_final_height += 25; // for more padding
                if (Mage::getStoreConfig("barcode/product/logo_position") == "0") $this->_final_height += 15;
                $this->_final_width += $this->_barcode_width > $this->_logo["width"] ? $this->_barcode_width : $this->_logo["width"];

            } elseif (Mage::getStoreConfig("barcode/product/logo_position") == "1" || Mage::getStoreConfig("barcode/product/logo_position") == "2") {
                $this->_final_height += 20;
                $this->_final_width += $this->_barcode_width + $this->_logo["width"];
            }


            //Check width and height
            $width_checker = intval($this->_layout_width) - ($this->_barcode_width) - ($this->_logo["width"]);
            $height_checker = intval($this->_layout_height) - ($this->_barcode_height) - ($this->_logo["height"]);
            if ($width_checker <= 0) {
//                die("<b>Please fill valid Layout, Barcode and Logo width values</b>. <br /> <i>Layout width value should be greater than Barcode plus Logo width one</i>");
            }
            if ($height_checker <= 0) {
//                die("<b>Please fill valid Layout, Barcode and Logo height values</b>. <br /> <i>Layout height value should be greater than Barcode plus Logo height one</i>");
            }
            if (($this->_layout_width < $this->_barcode_width) || $this->_layout_height < $this->_barcode_height || $this->_layout_height == 0 || $this->_layout_width == 0 || $this->_logo["width"] == 0 || $this->_logo["height"] == 0 || $this->_logo_width == 0 || $this->_logo_height == 0) {
//                die("<b>Please fill valid Layout, Barcode, Logo width/height values</b>. <br /> <i>The width/height values should be greater than Barcode one</i>");
            }
            //Check logo size
            if ($this->_logo["width"] > $this->_logo["file_width"] || $this->_logo["height"] > $this->_logo["file_height"]) {
//                die("<b>Please fill valid Logo's Width/Height should be less than or equal to real uploaded logo width/height </b>.");
            }

        } else {

            $this->_final_width += $this->_barcode_width;
            $this->_final_height += 25;
        }
        //end else - if include_logo config

    }


    protected function generateProductBarcode($productId)
    {
        $symbology = Mage::getStoreConfig('barcode/general/symbology');
        $dir = Mage::getBaseDir("media") . DS . "barcode" . DS;

        if (file_exists($dir . $productId . "_" . $symbology . "_bc.png")) {
            unlink($dir . $productId . "_" . $symbology . "_bc.png");
        }

        if (!is_dir_writeable($dir)) {
            $file = new Varien_Io_File;
            $file->checkAndCreateFolder($dir);
        }

        $product = Mage::getModel("catalog/product")->load($productId);
        if (!$product->getId())
            return false;

        // Creating some Color (arguments are R, G, B)
        $color_black = new FColor(0, 0, 0);
        $color_white = new FColor(255, 255, 255);

        /* Here is the list of the arguments:
          1 - Thickness
          2 - Color of bars
          3 - Color of spaces
          4 - Resolution
          5 - Text
          6 - Text Font (0-5) */
        if (intval(Mage::getStoreConfig("barcode/product/conversion") == 1)):
            switch (intval(Mage::getStoreConfig("barcode/product/barcode_field"))) {
                case 0:
                    $field = str_pad($productId, 12, "0", STR_PAD_LEFT);
                    break;
                case 1:
                    $field = substr(number_format(hexdec(substr(md5($product->getSku()), 0, 16)), 0, "", ""), 0, 12);
                    break;
                case 2:
                    $attr_id = Mage::getStoreConfig("barcode/product/barcode_source");
                    $attr = Mage::getModel('eav/entity_attribute')->load($attr_id)->getAttributeCode();
//                    $attr_val = $product->getResource()->getAttribute($attr)->getFrontend()->getValue($product);
                    $store_id = Mage::app()->getStore()->getStoreId();
                    $attr_val = Mage::getResourceModel('catalog/product')->getAttributeRawValue($product->getId(), $attr, $store_id);
                    $field = substr(number_format(hexdec(substr(md5($attr_val), 0, 16)), 0, "", ""), 0, 12);
                    break;
            } else: //Conversion: OFF.
            $attr_id = Mage::getStoreConfig("barcode/product/barcode_value");
            $attr = Mage::getModel('eav/entity_attribute')->load($attr_id)->getAttributeCode();
            $store_id = Mage::app()->getStore()->getStoreId();

//            $attr_val = $product->getData($attr);
            $attr_val = Mage::getResourceModel('catalog/product')->getAttributeRawValue($product->getId(), $attr, $store_id);

            if (!empty($attr_val)) {
                $field = $attr_val;
            } else { // value empty -> set default value. Fixed bug: XBAR-267
                switch ($symbology) {
                    case '4': //Code39
                        $field = "error";
                        break;
                    case '2': //128B
                        $field = "error";
                        break;
                    default:
                        $field = "error";
                        break;
                }
            }
            //Conversion OFF. Code39, 128B can read Text; otherwhile can NOT
            if ($symbology == 0 || $symbology == 3) {
                $field_checker = doubleval($field);
                if ($field_checker == 0) { // is string
                    return false;
                }
            }

//            if ($symbology == 1 && !ctype_upper($field)) { //128A and under case
//                return false;
//            }

        endif;

        $field = trim($field);

        //EAN13
        if($symbology == 0 && strlen($field)>13){
            $field = substr($field,0,12);
        }


        if ($field != 'error') {
            $code_generated = $this->_instantiateBarcode(intval($symbology), $field);

            $this->_initProductBarcode();
            /* Here is the list of the arguments
              1 - Width
              2 - Height
              3 - Filename (empty : display on screen)
              4 - Background color */


            $path = $dir . $productId . "_" . $symbology . ".png";

            //Barcode image inside. Mark!
            $drawing = new FDrawing($this->_layout_width, $this->_final_height, $path, $this->color_white);
            $drawing->init(); // You must call this method to initialize the image
            $drawing->add_barcode($code_generated);
            $drawing->draw_all();

            $im = $drawing->get_im();

            $imgBarcode = imagecreate($this->_barcode_width, $this->_barcode_height);
            $white = imagecolorallocate($imgBarcode, 255, 255, 255);

//            imagecopyresized($imgBarcode, $imgBarcode, 0, 0, 0, 0, $this->_barcode_width, $this->_barcode_height, $this->_barcode_width,$this->_barcode_height);

            // generate only barcode file
            $path2 = $dir . $productId . '_' . $symbology . "_bc.png";
            $bc_width = $code_generated->getPositionX();
            $bc_height = 50; // It look like all barcode has this height

            //$imageUrl = Mage::getBaseUrl("media") . DS . "barcode" . DS . "order" . DS . $order_id . ".png";
            //createProductBarcode action
            $drawing2 = new FDrawing($bc_width, $bc_height, $path2, $this->color_white);
            $drawing2->init(); // You must call this method to initialize the image
            $code_bc = clone $code_generated; // make clone to avoid damage on $code_generated
            $code_bc->resetPosition();
            $drawing2->add_barcode($code_bc);
            $drawing2->draw_all();
            $drawing2->finish(IMG_FORMAT_PNG);
            return true;

        } else {
            return false;
        }

        return false;
    } //END function createProductBarcode

    protected $_createdProductBarcode = array();

    public function createProductBarcode($productId)
    {
        if (!isset($this->_createdProductBarcode[$productId])) {
            $this->_createdProductBarcode[$productId] = $this->generateProductBarcode($productId);
        }
        return $this->_createdProductBarcode[$productId];

    }

    public function createOrderBarcode($order_id)
    {
        $dir = Mage::getBaseDir("media") . DS . "barcode" . DS . "order" . DS;
        if (!is_dir_writeable($dir)) {
            $file = new Varien_Io_File;
            $file->checkAndCreateFolder($dir);
        }
        //Unit
        $unit_input = Mage::getStoreConfig("barcode/general/input_size_unit");
        $unit_output = 'px';


        $number_of_char = strlen($order_id);
        // to process with editted order, with order_id like 100000005-1 
        if ($number_of_char > 9)
            $order_id = str_replace("-", "", $order_id);

        $leading_digits = Mage::getStoreConfig("barcode/order/enabled_leading_digits");
        if($leading_digits==0) $field = $order_id;
        else
        $field = str_pad($order_id, 12, "0", STR_PAD_LEFT);

       //Barcode width
       $barcode_width = Mage::getStoreConfig("barcode/order/barcode_width");
       if (is_numeric($barcode_width) && $barcode_width > 0 ) {
           $barcode_width = Mage::helper('barcode/barcode')->unitConverter($barcode_width, $unit_input, $unit_output);
       } else{
           $barcode_width = 172; //set default
       }
       $barcode_height = Mage::getStoreConfig("barcode/order/barcode_height");
       if (is_numeric($barcode_height) && $barcode_height > 0 ) {
           $barcode_height = Mage::helper('barcode/barcode')->unitConverter($barcode_height, $unit_input, $unit_output);
       } else{
           $barcode_height = 62; //set default
       }

        $symbologyIntval = intval(Mage::getStoreConfig("barcode/general/symbology"));
        $code_generated = $this->_instantiateBarcode($symbologyIntval, $field);
        if (in_array(intval(Mage::getStoreConfig("barcode/general/symbology")), array(0, 7))) { // ean13
            $width = 110;
        }

        /* Here is the list of the arguments
          1 - Width
          2 - Height
          3 - Filename (empty : display on screen)
          4 - Background color */
        $path = $dir . $order_id . ".png";
        $imageUrl = Mage::getBaseUrl("media") . DS . "barcode" . DS . "order" . DS . $order_id . ".png";
        $drawing = new FDrawing($barcode_width, $barcode_height, $path, $this->color_white);
        $drawing->init(); // You must call this method to initialize the image
        $drawing->add_barcode($code_generated);
        $drawing->draw_all();
        $im = $drawing->get_im();

// Next line create the little picture, the barcode is being copied inside
//      $im2 = imagecreate(330,120);
//
//      imagecopyresized($im2, $im, 189, 10, 0, 0, $code_generated->lastX, $code_generated->lastY, $code_generated->lastX, $code_generated->lastY);
// Draw (or save) the image into PNG format.
        $drawing->finish(IMG_FORMAT_PNG);
        return $path;
    }

    public function createOrderBarcodeAbs($order_id,$width,$height)
    {
        $dir = Mage::getBaseDir("media") . DS . "barcode" . DS . "order" . DS;
        if (!is_dir_writeable($dir)) {
            $file = new Varien_Io_File;
            $file->checkAndCreateFolder($dir);
        }
        //Unit
        $unit_input = Mage::getStoreConfig("barcode/general/input_size_unit");
        $unit_output = 'px';

//        $number_of_char = strlen($order_id);
        // to process with editted order, with order_id like 100000005-1
//        if ($number_of_char > 9)
//            $order_id = str_replace("-", "", $order_id);

        $leading_digits = Mage::getStoreConfig("barcode/order/enabled_leading_digits");
        if ($leading_digits==0) {
            $field = str_replace('-', '', (string)$order_id);
        } else {
            $field = str_pad(str_replace('-', '', (string)$order_id), 12, "0", STR_PAD_LEFT);
        }
        /*$barcode_width = 172;
        $barcode_height = 62;*/
        $code_generated = $this->_instantiateBarcode(intval(Mage::getStoreConfig("barcode/general/symbology")), $field);
        if (in_array(intval(Mage::getStoreConfig("barcode/general/symbology")), array(0, 7))) { // ean13
            $width = 110;
        }

        /* Here is the list of the arguments
          1 - Width
          2 - Height
          3 - Filename (empty : display on screen)
          4 - Background color */
        $path = $dir . $order_id . ".png";
        $drawing = new FDrawing($width, $height, $path, $this->color_white);
        $drawing->init(); // You must call this method to initialize the image
        $drawing->add_barcode($code_generated);
        $drawing->draw_all();
        $drawing->get_im();

        $drawing->finish(IMG_FORMAT_PNG);
        return $path;
    }

    /*
    Create barcode in preview mode
    Return $imageUrl
    */
    public function createOrderBarcodePreview($symbology = 4, $order_id, $width = 172, $height = 62, $include_logo = 0, $padding_top = 0, $padding_left = 0, $unit)
    {
        if (empty($order_id)) $order_id = 123456789000;
//        if (!is_numeric($order_id)) {
//            echo "<div id='sm_xbarcode_error'>Please enter a <strong>valid Order ID</strong>. It can only be numeric.</div>";
//            //echo "<script> var sm_xbarcode_error = document.getElementById('sm_xbarcode_error'); alert(sm_xbarcode_error.textContent);</script>";
//            exit();
//        }

        if (empty($width)) $width = 172;
        if (empty($height)) $height = 62;
        if (empty($include_logo)) $include_logo = 0;
        if (empty($padding_top)) $padding_top = 30;
        if (empty($padding_left)) $padding_left = 30;

        $dir = Mage::getBaseDir("media") . DS . "barcode" . DS . "order" . DS;
        if (!is_dir_writeable($dir)) {
            $file = new Varien_Io_File;
            $file->checkAndCreateFolder($dir);
        }

//        $number_of_char = strlen($order_id);
        // to process with editted order, with order_id like 100000005-1 
//        if ($number_of_char > 9)
//            $order_id = str_replace("-", "", $order_id);

        $leading_digits = Mage::getStoreConfig("barcode/order/enabled_leading_digits");
        if($leading_digits==0) $field =$order_id;
        else
        $field = str_pad($order_id, 12, "0", STR_PAD_LEFT);

        $code_generated = $this->_instantiateBarcode(intval($symbology), $field);

        /* Here is the list of the arguments
          1 - Width
          2 - Height
          3 - Filename (empty : display on screen)
          4 - Background color */
        $path = $dir . "preview_" . $order_id . ".png";
        $drawing = new FDrawing($width, $height, $path, $this->color_white);
        $drawing->init(); // You must call this method to initialize the image
        $drawing->add_barcode($code_generated);
        $drawing->draw_all();
        $drawing->get_im();
        $drawing->finish(IMG_FORMAT_PNG);

        $image_content = base64_encode(file_get_contents($path));
        return $image_content;
    }

    public function  addLastDigitForEan13(&$field)
    {
        $keys = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");
        $odd = true;
        $checksum = 0;
        for ($i = strlen($field); $i > 0; $i--) {
            if ($odd == true) {
                $multiplier = 3;
                $odd = false;
            } else {
                $multiplier = 1;
                $odd = true;
            }
            $checksum += $keys[$field[$i - 1]] * $multiplier;
        }


        $checksum = 10 - $checksum % 10;
        $checksum = ($checksum == 10) ? 0 : $checksum;

        if (strlen($field) == 12)
            $field .= $checksum; // ko hieu sao the nay moi dung'
    }

    public function printBarcode($pId)
    {
        $product = Mage::getModel('catalog/product')->load($pId);
        if (!isset($product)) return;

        $code = Mage::getStoreConfig('barcode/general/symbology');
        $dir = Mage::getBaseDir("media") . DS . "barcode" . DS;
        $path2 = $dir . $pId . '_' . $code . "_bc.png";

        $field = $product->getData('sm_barcode');
        $code_generated = $this->_instantiateBarcode(intval($code), $field);
        $path2 = $dir . $pId . '_' . $code . "_bc.png";
        $bc_width = $code_generated->getPositionX();
        $bc_height = 50; // It look like all barcode has this height
        $path = $dir . $pId . "_" . $code . ".png";
        //Create barcode - print Barcode action
        $drawing2 = new FDrawing(180, $bc_height, $path2, $this->color_white);
        $drawing2->init(); // You must call this method to initialize the image
        $code_bc = clone $code_generated; // make clone to avoid damage on $code_generated
        $code_bc->resetPosition();
        $drawing2->add_barcode($code_bc);
        $drawing2->draw_all();
        $drawing2->finish(IMG_FORMAT_PNG);
    }

    public function printBarcodeAbs($pId,$width,$height)
    {
        $product = Mage::getModel('catalog/product')->load($pId);
        if (!isset($product)) return;

        $code = Mage::getStoreConfig('barcode/general/symbology');
        $dir = Mage::getBaseDir("media") . DS . "barcode" . DS;
        $path2 = $dir . $pId . '_' . $code . "_bc.png";

        $field = $product->getData('sm_barcode');
        $code_generated = $this->_instantiateBarcode(intval($code), $field);

        $path2 = $dir . $pId . '_' . $code . "_bc.png";
        $path = $dir . $pId . "_" . $code . ".png";
        //Create barcode - print Barcode action
        $drawing2 = new FDrawing($width, $height, $path2, $this->color_white);
        $drawing2->init(); // You must call this method to initialize the image
        $code_bc = clone $code_generated; // make clone to avoid damage on $code_generated
        $code_bc->resetPosition();
        $drawing2->add_barcode($code_bc);
        $drawing2->draw_all();
        $drawing2->finish(IMG_FORMAT_PNG);
    }

    /* 
    * Unit conversion function
    * Input: number, input, output unit
    * return output unit
    * Author: Truongnq@smartosc.com
    */

    public function unitConverter($number, $input, $output)
    {
        if (isset($input) && isset($output) && is_numeric($number)) {
            switch ($input) {
                case 'mm':
                    switch ($output) {
                        case 'pt': // mm -> point: 1mm = 2.83464567 point
                            return $number * 2.83464567;

                            break;
                        case 'px': // mm -> pixel
                            return $number * 3.779527559;
                            break;
                        case 'ich': // mm -> inches
                            return $number * 0.0393700787;
                            break;

                        default:
                            return $number;
                            break;
                    }
                    break;

                case 'inch':
                    switch ($output) {
                        case 'pt': // inch -> point
                            return (int)$number * 54; //??
                            break;
                        case 'px': // inch -> pixel
                            return $number * 72;
                            break;
                        case 'mm': // inch -> mm
                            return $number / 0.0393700787; // Or * 25.4
                            break;
                        default:
                            return $number;
                            break;
                    }

                    break;

                case 'px':
                    switch ($output) {
                        case 'pt': // px -> point
                            return $number * 0.75;
                            break;
                        case 'ich': // px -> inches 
                            return $number / 72;
                            break;
                        case 'mm': // px -> mm
                            return $number / 3.779528;
                            break;

                        default:
                            return $number;
                            break;
                    }
                    break;

                default:
                    return false;
                    break;


                case 'pt':
                    switch ($output) {
                        case 'px': // px -> point
                            return $number / 0.75;
                            break;
                        case 'inch': // px -> inches
                            return $number * 0.0138888889;
                            break;
                        case 'mm': // px -> mm
                            return $number * 0.352777778;
                            break;

                        default:
                            return $number;
                            break;
                    }
                    break;

                default:
                    return false;
                    break;


            }
            //end switch
        } else {
            return false;
        }
    }

    public function getBarcodePath($productId, $type = 'path')
    {
        $symbology = Mage::getStoreConfig('barcode/general/symbology');
        switch ($type) {
            case 'path':
                return  Mage::getBaseDir('media') . DS . "barcode" . DS . $productId . '_' . $symbology . '_bc.png';
                break;

            case 'url':
                return  Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . "barcode" . DS . $productId . '_' . $symbology . '_bc.png';
                break;

            default:
                return false;
                break;
        }
    }

}

