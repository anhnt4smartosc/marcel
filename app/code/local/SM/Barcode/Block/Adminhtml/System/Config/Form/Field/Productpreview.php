<?php
/**
 * Order preview
 *
 * @category    XBarcode
 * @package     SM_Barcode
 * @copyright   Copyright (c) 2013 SmartOSC (http://www.smartosc.com)
 * @author      Truongnq
 */

class SM_Barcode_Block_Adminhtml_System_Config_Form_Field_Productpreview extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected $_addRowButtonHtml = array();
    protected $_removeRowButtonHtml = array();

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $html ='
        <script type="text/javascript">
            function xbarcode_preview_product(){
                jQuery.noConflict();

            var url = "'. Mage::helper("adminhtml")->getUrl("*/barcode_print/preview") .'";
            url = url + "?id=" + jQuery("#barcode_product_preview_product_id").val();

            var paperSize = jQuery("#barcode_product_paper_size").val();
            if(paperSize == "Custom"){
                var paperWidth  = jQuery("#barcode_product_paper_width").val();
                var paperHeight = jQuery("#barcode_product_paper_height").val();
            }
            else {
                var paperWidth  = 0;
                var paperHeight = 0;
            }

            var data = {
              "symbology" : jQuery("#barcode_general_symbology").val(),
            "unit" : jQuery("#barcode_general_input_size_unit").val(),

            "qty" : jQuery("#barcode_product_preview_qty").val(),

            "conversion" : jQuery("#barcode_product_conversion").val(),
            "field" : jQuery("#barcode_product_barcode_field").val(),
            "source" : jQuery("#barcode_product_barcode_source").val(),

            "orientation" : jQuery("#barcode_product_orientation").val(),
            "paperSize" : paperSize,
            "paperWidth" : paperWidth,
            "paperHeight" : paperHeight,
            "width" : jQuery("#barcode_product_width").val(),
            "height" : jQuery("#barcode_product_height").val(),

            "rows" : jQuery("#barcode_product_rows_display").val(),
            "cols" : jQuery("#barcode_product_columns_display").val(),

            "marginTop" : jQuery("#barcode_product_page_margin_top").val(),
            "marginLeft" : jQuery("#barcode_product_page_margin_left").val(),

            "labelMarginTop" : jQuery("#barcode_product_label_margin_top").val(),
            "labelMarginLeft" : jQuery("#barcode_product_label_margin_left").val(),
            "labelPaddingTop" : jQuery("#barcode_product_label_padding_top").val(),
            "labelPaddingBottom" : jQuery("#barcode_product_label_padding_bottom").val(),
            "labelPaddingLeft" : jQuery("#barcode_product_label_padding_left").val(),
            "labelPaddingRight" : jQuery("#barcode_product_label_padding_right").val(),

            "includeLogo" : jQuery("#barcode_product_include_logo").val(),
            "logoWith" : jQuery("#barcode_product_logo_width").val(),
            "logoHeight" : jQuery("#barcode_product_logo_height").val(),
            "logoSettings" : jQuery("#barcode_product_logo_settings").val(),

            "barcodeWidth" : jQuery("#barcode_product_barcode_width").val(),
            "barcodeHeight" : jQuery("#barcode_product_barcode_height").val(),
            "barcodeSettings" : jQuery("#barcode_product_barcode_settings").val(),

            "showProductName" : jQuery("#barcode_product_name_visible").val(),
            "productNameSettings" : jQuery("#barcode_product_product_name_settings").val(),
            "productNameLeng" : jQuery("#barcode_product_product_name_leng").val(),
            "showPrice" : jQuery("#barcode_product_price_visible").val(),
            "priceSettings" : jQuery("#barcode_product_price_settings").val(),

            "showSlot1" : jQuery("#barcode_product_slot1_visible").val(),
            "slot1Attribute" : jQuery("#barcode_product_slot1").val(),
            "slot1Settings" : jQuery("#barcode_product_slot1_settings").val(),

            "showSlot2" : jQuery("#barcode_product_slot2_visible").val(),
            "slot2Attribute" : jQuery("#barcode_product_slot2").val(),
            "slot2Settings" : jQuery("#barcode_product_slot2_settings").val(),

            "showSlot3" : jQuery("#barcode_product_slot3_visible").val(),
            "slot3Attribute" : jQuery("#barcode_product_slot3").val(),
            "slot3Settings" : jQuery("#barcode_product_slot3_settings").val(),

            "showSlot4" : jQuery("#barcode_product_slot4_visible").val(),
            "slot4Attribute" : jQuery("#barcode_product_slot4").val(),
            "slot4Settings" : jQuery("#barcode_product_slot4_settings").val(),

            "font" : jQuery("#barcode_product_use_font_for_text").val(),
            "fontSize" : jQuery("#barcode_product_font_size").val(),

            };

            jQuery.each(data, function(index, value) {
              url =  url + "&" + index + "="  + value;
            });

            window.open(url, "_blank");
            }

        </script>

        <p><button class="scalable " type="button" onclick ="xbarcode_preview_product();return false;"> <span>Preview Product Barcode Labels</span></button>
        <div id="xbarcode_product_preview_result"></div>

        ';
        return $html;
    }

}