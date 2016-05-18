<?php
/**
 * Order preview
 *
 * @category    XBarcode
 * @package     SM_Barcode
 * @copyright   Copyright (c) 2013 SmartOSC (http://www.smartosc.com)
 * @author      Truongnq
 */

class SM_Barcode_Block_Adminhtml_System_Config_Form_Field_Reset extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected $_addRowButtonHtml = array();
    protected $_removeRowButtonHtml = array();

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '';

        $html .= '
              <input type="hidden" id="barcode_general_show_notifier_update" value="1"><button type="button" class="scalable" id="sm_resetToDefault" onclick="resetDefault()"><span><span><span>' . $this->__('Reset Default') . '</span></span></span></button>
        <script type="text/javascript">
            var data = {
                "reset" : {
                    "barcode_general_enabled" : "1",
                    "barcode_general_symbology" : "4",
                    "barcode_product_conversion" : "1",
                    "barcode_product_barcode_field" : "1",
                    "barcode_product_paper_size" : "A4",
                    "barcode_product_paper_width" : "",
                    "barcode_product_paper_height" : "",
                    "barcode_product_orientation" : "P",
                    "barcode_general_show_notifier_update": "1",
                    "barcode_general_generate_per_request": "0",
                    "barcode_product_width" : "55",
                    "barcode_product_height" : "22",
                    "barcode_product_rows_display" : "10",
                    "barcode_product_columns_display" : "3",
                    "barcode_product_page_margin_top" : "13",
                    "barcode_product_page_margin_left" : "18",
                    "barcode_product_label_margin_top" : "2",
                    "barcode_product_label_margin_left" : "2",
                    "barcode_product_label_padding_top" : "1",
                    "barcode_product_label_padding_bottom" : "0",
                    "barcode_product_label_padding_left" : "2",
                    "barcode_product_label_padding_right" : "1",
                    "barcode_product_include_logo" : "0",
                    "barcode_product_logo_width" : "10",
                    "barcode_product_logo_height" : "10",
                    "barcode_product_logo_settings" : "0,0",
                    "barcode_product_barcode_width" : "25",
                    "barcode_product_barcode_height" : "10",
                    "barcode_product_barcode_settings" : "28,10",
                    "barcode_product_name_visible" : "1",
                    "barcode_product_product_name_settings" : "0,0,10",
                    "barcode_product_product_name_leng" : "50",
                    "barcode_product_price_visible" : "1",
                    "barcode_product_price_settings" : "0,9,10",
                    "barcode_product_slot1_visible" : "0",
                    "barcode_product_slot2_visible" : "0",
                    "barcode_product_slot3_visible" : "0",
                    "barcode_product_slot4_visible" : "0",
                    "barcode_product_use_font_for_text" : "helvetica",
                    "barcode_product_font_size" : "9",
                    "barcode_product_preview_product_id" : "166",
                    "barcode_product_preview_qty" : "32",
                    "barcode_order_enabled_leading_digits": "1",
                    "barcode_order_invoice_enabled" : "1",
                    "barcode_order_invoice_position" : "0",
                    "barcode_order_packingslip_enabled" : "1",
                    "barcode_order_packingslip_position" : "0",
                    "barcode_order_padding_top" : "",
                    "barcode_order_padding_left" : "",
                    "barcode_order_include_logo" : "0",
                    "barcode_order_barcode_width" : "30",
                    "barcode_order_barcode_height" : "10",
                    "barcode_order_id" : "100000001",
                    "barcode_rma_valid_duration" : "30",
                    "barcode_rma_handling_fee_default": "0",
                    "barcode_rma_stock_update" : "1"
                },

                "PCL195281" : {
                    "test" : "test"

                },
                "PCL190275" : {
                    "test" : "test"
                }
            }

            function resetDefault(){
//                jQuery.each(data, function(key,array){
                        jQuery.each(data.reset, function(input,value){
                        jQuery("#" + input).val(value);
                        jQuery("#" + input).removeAttr("disabled");
                        jQuery("#row_" + input).show();
//                    });

                jQuery("#row_barcode_product_barcode_value").hide();
                jQuery("#row_barcode_product_barcode_source").hide();
                jQuery("#row_barcode_product_logo_width").hide();
                jQuery("#row_barcode_product_logo_height").hide();
                jQuery("#row_barcode_product_logo_settings").hide();
                jQuery("#row_barcode_product_slot1").hide();
                jQuery("#row_barcode_product_slot1_settings").hide();
                jQuery("#row_barcode_product_slot2").hide();
                jQuery("#row_barcode_product_slot2_settings").hide();
                jQuery("#row_barcode_product_slot3").hide();
                jQuery("#row_barcode_product_slot3_settings").hide();
                jQuery("#row_barcode_product_slot4").hide();
                jQuery("#row_barcode_product_slot4_settings").hide();
                jQuery("#row_barcode_product_paper_width").hide();
                jQuery("#row_barcode_product_paper_height").hide();

            });
            }


        </script>
        ';

        return $html;
    }

}