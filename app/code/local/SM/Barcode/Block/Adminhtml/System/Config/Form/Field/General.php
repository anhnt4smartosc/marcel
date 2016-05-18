<?php
/**
 * Order preview
 *
 * @category    XBarcode
 * @package     SM_Barcode
 * @copyright   Copyright (c) 2013 SmartOSC (http://www.smartosc.com)
 * @author      Truongnq
 */

class SM_Barcode_Block_Adminhtml_System_Config_Form_Field_General extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected $_addRowButtonHtml = array();
    protected $_removeRowButtonHtml = array();

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '';

        $html .= '

        <script type="text/javascript" src="' . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS) . 'sm/xbarcode/jquery.latest.js"> </script>

        <script type="text/javascript">
            jQuery.noConflict();


        </script>
        <script type="text/javascript" src="' . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS) . 'sm/xbarcode/notifier.js"> </script>
               <button type="button" class="scalable" id="sm_ajaxsaveconfig" onclick="ajaxSaveConfig()"><span><span><span>' . $this->__('Generate new Barcodes') . '</span></span></span></button>
        <script type="text/javascript">
            NotifierjsConfig.defaultTimeOut = 10000;
            NotifierjsConfig.position = ["bottom", "right"];
            //Save config
            function ajaxSaveConfig(callFromAjax){

                var request = jQuery("#barcode_general_generate_per_request").val();
                 if(request<1){
                    alert("Generate per request must greater than zero!");
                 return;
                 }

                jQuery.dimScreen(500, 0.5, function() {
                    jQuery("#html-body").fadeIn();
                });

                jQuery(window).load(function(){
                  jQuery("#imageajaxloading").fadeIn(100);
                });
                if (callFromAjax !== "undefined" && callFromAjax) {
                    first_click = 0;
                } else {
                    first_click = 1;
                }
                jQuery.ajax({
                  type: "GET",
                  dataType: "html",
                  url: "' . Mage::helper("adminhtml")->getUrl("*/barcode_ajax/ajaxsaveconfig") . '",
                  data: {
                      enable: jQuery("#barcode_general_enabled").val(),
                      key: jQuery("#barcode_general_key").val(),
                      symbology: jQuery("#barcode_general_symbology").val(),
                      unit: jQuery("#barcode_general_input_size_unit").val(),
                      conversion: jQuery("#barcode_product_conversion").val(),
                      value: jQuery("#barcode_product_barcode_value").val(),
                      field: jQuery("#barcode_product_barcode_field").val(),
                      source: jQuery("#barcode_product_barcode_source").val(),

                      orientation: jQuery("#barcode_product_orientation").val(),
                      width: jQuery("#barcode_product_width").val(),
                      height: jQuery("#barcode_product_height").val(),
                      padding_top: jQuery("#barcode_product_label_padding_top").val(),
                      padding_bottom: jQuery("#barcode_product_label_padding_bottom").val(),
                      padding_left: jQuery("#barcode_product_label_padding_left").val(),
                      padding_right: jQuery("#barcode_product_label_padding_right").val(),
                      margin_top: jQuery("#barcode_product_page_margin_top").val(),
                      margin_left: jQuery("#barcode_product_page_margin_left").val(),

                      label_margin_top: jQuery("#barcode_product_label_margin_top").val(),
                      label_margin_left: jQuery("#barcode_product_label_margin_left").val(),

                      padding_top : jQuery("#barcode_product_label_padding_top").val(),
                      padding_bottom : jQuery("#barcode_product_label_padding_bottom").val(),
                      padding_left : jQuery("#barcode_product_label_padding_left").val(),
                      padding_right : jQuery("#barcode_product_label_padding_right").val(),



                      barcode_width: jQuery("#barcode_product_barcode_width").val(),
                      barcode_height: jQuery("#barcode_product_barcode_height").val(),
                      barcode_settings: jQuery("#barcode_product_barcode_settings").val(),

                      rows_display: jQuery("#barcode_product_rows_display").val(),
                      columns_display: jQuery("#barcode_product_columns_display").val(),


                      name_visible: jQuery("#barcode_product_name_visible").val(),
                      product_name_leng: jQuery("#barcode_product_product_name_leng").val(),
                      product_name_settings: jQuery("#barcode_product_product_name_settings").val(),

                      price_visible: jQuery("#barcode_product_price_visible").val(),
                      price_settings: jQuery("#barcode_product_price_settings").val(),

                      slot1_visible: jQuery("#barcode_product_slot1_visible").val(),
                      slot1: jQuery("#barcode_product_slot1").val(),
                      slot1_settings: jQuery("#barcode_product_slot1_settings").val(),

                      slot2_visible: jQuery("#barcode_product_slot2_visible").val(),
                      slot2: jQuery("#barcode_product_slot2").val(),
                      slot2_settings: jQuery("#barcode_product_slot2_settings").val(),

                      slot3_visible: jQuery("#barcode_product_slot3_visible").val(),
                      slot3: jQuery("#barcode_product_slot3").val(),
                      slot3_settings: jQuery("#barcode_product_slot3_settings").val(),

                      slot4_visible: jQuery("#barcode_product_slot4_visible").val(),
                      slot4: jQuery("#barcode_product_slot4_visible").val(),
                      slot4_settings: jQuery("#barcode_product_slot4_settings").val(),

                      font_for_text: jQuery("#barcode_product_use_font_for_text").val(),
                      font_size: jQuery("#barcode_product_font_size").val(),
                      line_height: jQuery("#barcode_product_line_height").val(),

                      include_logo: jQuery("#barcode_product_include_logo").val(),
                      logo_settings: jQuery("#barcode_product_logo_settings").val(),
                      logo_width: jQuery("#barcode_product_logo_width").val(),
                      logo_height: jQuery("#barcode_product_logo_height").val(),
                      logo_padding_left : jQuery("#barcode_product_logo_padding_left").val(),
                      logo_padding_top : jQuery("#barcode_product_logo_padding_top").val(),

                      barcode_order_include_logo: jQuery("#barcode_order_include_logo").val(),
                      invoice_enabled: jQuery("#barcode_order_invoice_enabled").val(),
                      invoice_position: jQuery("#barcode_order_invoice_position").val(),
                      packingslip_enabled: jQuery("#barcode_order_packingslip_enabled").val(),
                      packingslip_position: jQuery("#barcode_order_packingslip_position").val(),
                      order_padding_top: jQuery("#barcode_order_padding_top").val(),
                      order_padding_left: jQuery("#barcode_order_padding_left").val(),
                      order_barcode_width: jQuery("#barcode_order_barcode_width").val(),
                      order_barcode_height: jQuery("#barcode_order_barcode_height").val(),
                      rma_valid_duration: jQuery("#barcode_rma_valid_duration").val(),
                      stock_update: jQuery("#barcode_rma_stock_update").val(),
                      generate_per_request: jQuery("#barcode_general_generate_per_request").val(),
                      first_click: first_click,


                    debug_isEnabled : jQuery("#barcode_debug_isEnabled").val(),

                  },

                }).done(function(data) {
                jQuery(".messages").attr("style","display:none");
                    data = JSON.parse(data);
                    if (data.message != "Done") {
                        if ((jQuery("#barcode_general_show_notifier_update").val() === "1")  && !!Notifier) { Notifier.info(data.message); }
                        ajaxSaveConfig(true);
                    } else {
                         Notifier.info(data.success);
                        alert( "Configuration saved and applied!");
                        jQuery.dimScreenStop();
                    }
                });
            }




            jQuery( document ).ready(function() {
            //dimScreen()
            //by Brandon Goldman
            jQuery.extend({
                //dims the screen
                dimScreen: function(speed, opacity, callback) {
                    if(jQuery("#__dimScreen").size() > 0) return;

                    if(typeof speed == "function") {
                        callback = speed;
                        speed = null;
                    }

                    if(typeof opacity == "function") {
                        callback = opacity;
                        opacity = null;
                    }

                    if(speed < 1) {
                        var placeholder = opacity;
                        opacity = speed;
                        speed = placeholder;
                    }

                    if(opacity >= 1) {
                        var placeholder = speed;
                        speed = opacity;
                        opacity = placeholder;
                    }

                    speed = (speed > 0) ? speed : 500;
                    opacity = (opacity > 0) ? opacity : 0.5;
                    return jQuery(\'<div class="loader" id="loading-mask-loader" style="zIndex: 999; height:50px; width:120px; border: solid #EB5E00 1px; background:#fff;">Please wait...</div>\').attr({
                            id: "__dimScreen"
                            ,fade_opacity: opacity
                            ,speed: speed
                        }).css({
                        background: "#000 url(\'' . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) . 'adminhtml/default/default/images/ajax-loader-tr.gif\') no-repeat center center"
                        ,height: "100%"
                        ,left: "0px"
                        ,opacity: 0
                        ,position: "fixed"
                        ,top: "0px"
                        ,width: "100%"
                        ,zIndex: 999
                    }).appendTo(document.body).fadeTo(speed, opacity, callback);
                },

                //stops current dimming of the screen
                dimScreenStop: function(callback) {
                    var x = jQuery("#__dimScreen");
                    var opacity = x.attr("fade_opacity");
                    var speed = x.attr("speed");
                    x.fadeOut(speed, function() {
                        x.remove();
                        if(typeof callback == "function") callback();
                    });

                }
            });
            });



        </script>
        ';

        return $html;
    }

}
