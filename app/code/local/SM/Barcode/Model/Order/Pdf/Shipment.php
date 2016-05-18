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
class SM_Barcode_Model_Order_Pdf_Shipment extends Mage_Sales_Model_Order_Pdf_Shipment {

    protected function insertOrder(&$page, $obj, $putOrderId = true) {
        parent::insertOrder($page, $obj, $putOrderId);
        if (Mage::helper('barcode')->isEnable() && Mage::helper('barcode')->canShowOnPackingslip()) {
            if ($obj instanceof Mage_Sales_Model_Order) {
                $shipment = null;
                $order = $obj;
            } elseif ($obj instanceof Mage_Sales_Model_Order_Shipment) {
                $shipment = $obj;
                $order = $shipment->getOrder();
            }

             //Unit
            $unit_input = Mage::getStoreConfig("barcode/general/input_size_unit");
            $unit_output = 'pt';


            $image = Mage::helper('barcode/barcode')->createOrderBarcode($order->getRealOrderId());
            if (is_file($image)) {

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

                $image = Zend_Pdf_Image::imageWithPath($image);
                //order padding left
                $padding_left = Mage::getStoreConfig('barcode/order/padding_left');
                if(isset($padding_left) && is_numeric($padding_left) && $padding_left > 0){
                    $padding_left = Mage::helper('barcode/barcode')->unitConverter($padding_left,$unit_input,$unit_input);
                } else{
                    $padding_left = 0; //set default
                }

                //order padding top
                $padding_top = Mage::getStoreConfig('barcode/order/padding_top');
                if(isset($padding_top) && is_numeric($padding_top) && $padding_top > 0){
                    $padding_top = Mage::helper('barcode/barcode')->unitConverter($padding_top,$unit_input,$unit_output);
                } else{
                    $padding_top = 0; //set default
                }

                if (intval(Mage::getStoreConfig('barcode/order/packingslip_position'))==3){
                    // BOTTOM RIGHT
                    $top = $barcode_height * 2;
                    $left = 482 - $padding_left;
                } elseif (intval(Mage::getStoreConfig('barcode/order/packingslip_position'))==2){
                    // BOTTOM LEFT
                    $top = $barcode_height * 2;
                    $left = 25 + $padding_left;
                } elseif (intval(Mage::getStoreConfig('barcode/order/packingslip_position'))==0){
                    // TOP LEFT
                    $top = 825 - intval($padding_top);
                    $left = 25 + $padding_left;
                } else{
                    // TOP RIGHT
                    $top = 825 - $padding_top;
                    $left = 482 - $padding_left;
                }
                
                $page->drawImage($image, $left, $top - $barcode_height, $left + $barcode_width, $top);
            }
            // start drawing logo
            if (Mage::getStoreConfig("barcode/order/include_logo")) {
                $logoFile = is_file("media/barcode/" . Mage::getStoreConfig("barcode/product/logo_image_file")) ? "media/barcode/" . Mage::getStoreConfig("barcode/product/logo_image_file") : "media/barcode/logo.png";
                $logoSize = $logoType = getimagesize($logoFile);
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

                $logoFileWidth = intval($logoSize["0"]);
                $logoFileHeight = intval($logoSize["1"]);

                // resize logo
                $availableHeight = $barcode_height;
                $availableWidth = $barcode_width;

                if ($logoFileHeight > $availableHeight) {
                    $logoHeight = $availableHeight;
                    $logoWidth = $logoFileWidth
                            * $logoHeight / $logoFileHeight;
                } else {
                     $logoWidth = Mage::getStoreConfig("barcode/product/logo_width");
                    if (is_numeric($logoWidth) && $logoWidth > 0 ) {
                        $logoWidth = Mage::helper('barcode/barcode')->unitConverter($logoWidth, $unit_input, $unit_output);
                    } else{
                        $logoWidth = $logoSize["0"]; //set real image size
                    }

                    $logoHeight = Mage::getStoreConfig("barcode/product/logo_height");
                    if (is_numeric($logoHeight) && $logoHeight > 0 ) {
                        $logoHeight = Mage::helper('barcode/barcode')->unitConverter($logoHeight, $unit_input, $unit_output);
                    } else{
                        $logoHeight = $logoSize["1"]; //set real image size
                    }

                }
                // 2nd check
                if ($logoHeight > $availableHeight) {
                    $logoHeight = $availableHeight;
                    $logoWidth = $logoFileWidth
                            * $logoHeight / $logoFileHeight;
                }
                // check if posible to display logo
                if ($logoHeight < 0 || $logoWidth < 0)
                    $logoHeight = $logoWidth = 0;
                // final check before drawing
                if (is_file($logoFile)) {
                    $imageLogo = Zend_Pdf_Image::imageWithPath($logoFile);
                    if ($left!=25)
                        $left = 25;
                    else 
                        $left = 570 - $logoWidth;
                    $top -= $logoHeight;
                    //$page->drawImage($image, $left, $bottom, $right, $top);
                    $page->drawImage($imageLogo, $left, $top, $left + $logoWidth, $top + $logoHeight);
                }
            }
            // end drawing logo            
        }
    }

    protected function insertImage($product_id, $x1, $y1, $x2, $y2, $width, $height, &$page)
    {
        if (!is_null($product_id)) {
            try{
                $width = (int) $width;
                $height = (int) $height;
                //create image\

                $code = Mage::getStoreConfig('barcode/general/symbology');
                $helper = Mage::helper('barcode/barcode');
                $helper->printBarcode($product_id);

                $imagePath = Mage::getBaseDir('media') . DS . 'barcode' . DS .$product_id.'_'. $code .'_bc.png';
                $image = Zend_Pdf_Image::imageWithPath($imagePath);
                //Draw image to PDF
                $page->drawImage($image, $x1, $y1, $x2, $y2);
            }
            catch (Exception $e) {
                return false;
            }
        }
    }

    protected function _drawHeader(Zend_Pdf_Page $page)
    {
        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y-15);
        $this->y -= 10;
        $page->setFillColor(new Zend_Pdf_Color_RGB(0, 0, 0));

        //columns headers
        $lines[0][] = array(
            'text' => Mage::helper('sales')->__('Products'),
            'feed' => 100,
        );

        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('Barcode'),
            'feed'  => 350
        );

        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('Qty'),
            'feed'  => 35
        );

        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('SKU'),
            'feed'  => 565,
            'align' => 'right'
        );

        $lineBlock = array(
            'lines'  => $lines,
            'height' => 10
        );

        $this->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
    }

    public function getPdf($shipments = array())
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('shipment');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);
        $input_unit = Mage::getStoreConfig('barcode/general/input_size_unit');
        $output_unit = 'px';

        $barcode_height = Mage::getStoreConfig('barcode/product/barcode_height');
            if(isset($barcode_height) && is_numeric($barcode_height) && $barcode_height > 0){
                $barcode_height = $helper = Mage::helper('barcode/barcode')->unitConverter($barcode_height,$input_unit,$output_unit);
            } else{
                $barcode_height = 62; //set default
            }

        foreach ($shipments as $shipment) {
            if ($shipment->getStoreId()) {
                Mage::app()->getLocale()->emulate($shipment->getStoreId());
                Mage::app()->setCurrentStore($shipment->getStoreId());
            }
            $page  = $this->newPage();
             $this->y -= $barcode_height; //fixed Logo/Barcode hover header  
            $order = $shipment->getOrder();
            /* Add image */
            $this->insertLogo($page, $shipment->getStore());
            /* Add address */
            $this->insertAddress($page, $shipment->getStore());
            /* Add head */
            $this->insertOrder(
                $page,
                $shipment,
                Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_SHIPMENT_PUT_ORDER_ID, $order->getStoreId())
            );
            /* Add document text and number */
            $this->insertDocumentNumber(
                $page,
                Mage::helper('sales')->__('Packingslip # ') . $shipment->getIncrementId()
            );
            /* Add table */
            $this->_drawHeader($page);
            /* Add body */
            foreach ($shipment->getAllItems() as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                /* Draw item */
                $this->_drawItem($item, $page, $order);

                /* Draw product image */

                switch($item->getOrderItem()->getProduct()->getTypeId()){
                    case 'configurable':
                        $productId = Mage::getModel('catalog/product')->getIdBySku($item->getOrderItem()->getSku());
                        $this->insertImage($productId, 190, (int)($this->y + 15), 260, (int)($this->y) + 35, 110, 50, $page);
                        break;

                    case 'bundle':
                        $y_plus = 0;
                        $bundle_serialize =  unserialize($item->getOrderItem()->getData('product_options'));
                        foreach($bundle_serialize['bundle_options'] as $option_items){
                            foreach($option_items['value'] as $option_item){
                                $option_simple_product = Mage::getModel('catalog/product')
                                    ->loadByAttribute('name',$option_item['title']);
                                $this->insertImage($option_simple_product->getId(), 195, (int)($this->y + 15 + $y_plus), 265, (int)($this->y) + 35 + $y_plus, 110, 50, $page);
                                $y_plus += 42;
                            }
                        }

                        break;

                    case 'simple':
                        $productId = $item->getOrderItem()->getProductId();
                        $this->insertImage($productId, 190, (int)($this->y + 15), 260, (int)($this->y) + 35, 110, 50, $page);
                        break;
                }

                $page = end($pdf->pages);
            }
        }
        $this->_afterGetPdf();
        if ($shipment->getStoreId()) {
            Mage::app()->getLocale()->revert();
        }
        return $pdf;
    }

}

