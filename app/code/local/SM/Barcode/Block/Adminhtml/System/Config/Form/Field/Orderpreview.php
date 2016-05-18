<?php
/**
 * Order preview
 *
 * @category    XBarcode
 * @package     SM_Barcode
 * @copyright   Copyright (c) 2013 SmartOSC (http://www.smartosc.com)
 * @author      Truongnq
 */

class SM_Barcode_Block_Adminhtml_System_Config_Form_Field_Orderpreview extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected $_addRowButtonHtml = array();
    protected $_removeRowButtonHtml = array();

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        // $headBlock = $this->getLayout()->getBlock(‘head’);
        // $headBlock->addJs('http://code.jquery.com/jquery-1.9.1.min.js');

        $html ='

        <script type="text/javascript">

            function xbarcode_preview_order(){
                //Khi thay doi system.xml thi id cua input se thay doi. -> Can sua lai #.xx..
                        var barcode_general_symbology = jQuery("#barcode_general_symbology").val();
                        
                        var barcode_order_barcode_size_padding_top = jQuery("#barcode_order_padding_top").val();
                        var barcode_order_barcode_size_padding_left = jQuery("#barcode_order_padding_left").val();
                        var barcode_order_barcode_size_barcode_width = jQuery("#barcode_order_barcode_width").val();
                        var barcode_order_barcode_size_barcode_height = jQuery("#barcode_order_barcode_height").val();
                        var unit = jQuery("#barcode_general_input_size_unit").val();
                        var barcode_order_id = jQuery("#barcode_order_id").val();
                        
                        jQuery("#xbarcode_preview_result").html(\'<img src="http://i.stack.imgur.com/FhHRx.gif"/>\');
                        var barcode_preview_params = "?barcode=order&symbology=" + barcode_general_symbology + "&width=" + barcode_order_barcode_size_barcode_width + "&height=" + barcode_order_barcode_size_barcode_height + "&include_logo=0" + "&order_id=" + barcode_order_id + "&padding_top=" + barcode_order_barcode_size_padding_top + "&padding_left=" + barcode_order_barcode_size_padding_left + "&unit=" + unit;
                        jQuery("#xbarcode_preview_result").load("'. Mage::helper("adminhtml")->getUrl("*/barcode_ajax/ajaxcreatebarcodepreview") .'" + barcode_preview_params);        
            }
        </script>
        <input type="text" class="input-text" value="100000001" name="" id="barcode_order_id">
        <p><button class="scalable " type="button" onclick ="xbarcode_preview_order();return false;"> <span>Preview Order Barcode</span></button>
        <div id="xbarcode_preview_result"></div><div id="ajaxloading"></div>
        <div style="display:none;">
            <div id="imageajaxloading" style="background: url() no-repeat center center;
               height: 100px;width: 100px;position: fixed;z-index: 1000; left: 50%;top: 50%;margin: -25px 0 0 -25px;">
            </div>
           </div>


        ';

//        $html .='
//                <!--<button type="button" class="scalable" id="sm_ajaxsaveconfig" onclick="ajaxSaveConfig()"><span><span><span>Save and Apply</span></span></span></button>-->
//        <script type="text/javascript">
//            jQuery("#sm_ajaxsaveconfig").insertAfter(".form-buttons");
//
//            //Save config
//            function ajaxSaveConfig(){
//
//                jQuery.dimScreen(1000, 0.5, function() {
//                    jQuery("#html-body").fadeIn();
//                });
//
//                jQuery(window).load(function(){
//                  jQuery("#imageajaxloading").fadeIn(1000);
//                });
//
//                jQuery.ajax({
//                  type: "GET",
//                  dataType: "html",
//                  url: "'.Mage::helper("adminhtml")->getUrl("*/barcode_ajax/ajaxsaveconfig").'",
//                  data: {
//                      enable: jQuery("#barcode_general_enabled").val(),
//                      key: jQuery("#barcode_general_key").val(),
//                      symbology: jQuery("#barcode_general_symbology").val(),
//                      unit: jQuery("#barcode_general_input_size_unit").val(),
//                      conversion: jQuery("#barcode_product_conversion").val(),
//                      field: jQuery("#barcode_product_barcode_field").val(),
//                      field: jQuery("#barcode_product_barcode_field").val(),
//                      source: jQuery("#barcode_product_barcode_source").val(),
//                      bcodelayout: jQuery("#barcode_product_bcodelayout").val(),
//                      width: jQuery("#barcode_product_width").val(),
//                      height: jQuery("#barcode_product_height").val(),
//                      barcode_width: jQuery("#barcode_product_barcode_width").val(),
//                      barcode_height: jQuery("#barcode_product_barcode_height").val(),
//                      columns_display: jQuery("#barcode_product_columns_display").val(),
//                      padding_top: jQuery("#barcode_product_label_padding_top").val(),
//                      padding_bottom: jQuery("#barcode_product_label_padding_bottom").val(),
//                      padding_left: jQuery("#barcode_product_label_padding_left").val(),
//                      padding_right: jQuery("#barcode_product_label_padding_right").val(),
//                      margin_top: jQuery("#barcode_product_page_margin_top").val(),
//                      margin_left: jQuery("#barcode_product_page_margin_left").val(),
//                      name_visible: jQuery("#barcode_product_name_visible").val(),
//                      price_visible: jQuery("#barcode_product_price_visible").val(),
//                      slot1: jQuery("#barcode_product_new_slot_visible1").val(),
//                      slot2: jQuery("#barcode_product_new_slot_visible2").val(),
//                      slot3: jQuery("#barcode_product_new_slot_visible3").val(),
//                      slot4: jQuery("#barcode_product_new_slot_visible4").val(),
//                      text_padding_top: jQuery("#barcode_product_text_padding_top").val(),
//                      text_padding_left: jQuery("#barcode_product_text_padding_left").val(),
//                      font_for_text: jQuery("#barcode_product_use_font_for_text").val(),
//                      font_size: jQuery("#barcode_product_font_size").val(),
//                      line_height: jQuery("#barcode_product_line_height").val(),
//                      include_logo: jQuery("#barcode_product_include_logo").val(),
//                      invoice_enabled: jQuery("#barcode_order_invoice_enabled").val(),
//                      invoice_position: jQuery("#barcode_order_invoice_position").val(),
//                      packingslip_enabled: jQuery("#barcode_order_packingslip_enabled").val(),
//                      packingslip_position: jQuery("#barcode_order_packingslip_position").val(),
//                      order_padding_top: jQuery("#barcode_order_padding_top").val(),
//                      order_padding_left: jQuery("#barcode_order_padding_left").val(),
//                      order_barcode_width: jQuery("#barcode_order_barcode_width").val(),
//                      order_barcode_height: jQuery("#barcode_order_barcode_height").val(),
//                      rma_valid_duration: jQuery("#barcode_rma_valid_duration").val(),
//                      stock_update: jQuery("#barcode_rma_stock_update").val(),
//
//                  },
//
//                }).done(function(html) {
//                  alert( "Config Saved" + html );
//                   jQuery.dimScreenStop();
//
//                });
//
//
//            }
//
//
//            //dimScreen()
//            //by Brandon Goldman
//            jQuery.extend({
//                //dims the screen
//                dimScreen: function(speed, opacity, callback) {
//                    if(jQuery("#__dimScreen").size() > 0) return;
//
//                    if(typeof speed == "function") {
//                        callback = speed;
//                        speed = null;
//                    }
//
//                    if(typeof opacity == "function") {
//                        callback = opacity;
//                        opacity = null;
//                    }
//
//                    if(speed < 1) {
//                        var placeholder = opacity;
//                        opacity = speed;
//                        speed = placeholder;
//                    }
//
//                    if(opacity >= 1) {
//                        var placeholder = speed;
//                        speed = opacity;
//                        opacity = placeholder;
//                    }
//
//                    speed = (speed > 0) ? speed : 500;
//                    opacity = (opacity > 0) ? opacity : 0.5;
//                    return jQuery(\'<div>Please wait...</div>\').attr({
//                            id: "__dimScreen"
//                            ,fade_opacity: opacity
//                            ,speed: speed
//                        }).css({
//                        background: "#000 url(\'http://i.stack.imgur.com/FhHRx.gif\') no-repeat center center"
//                        ,height: "100%"
//                        ,left: "0px"
//                        ,opacity: 0
//                        ,position: "absolute"
//                        ,top: "0px"
//                        ,width: "100%"
//                        ,zIndex: 999
//                    }).appendTo(document.body).fadeTo(speed, opacity, callback);
//                },
//
//                //stops current dimming of the screen
//                dimScreenStop: function(callback) {
//                    var x = jQuery("#__dimScreen");
//                    var opacity = x.attr("fade_opacity");
//                    var speed = x.attr("speed");
//                    x.fadeOut(speed, function() {
//                        x.remove();
//                        if(typeof callback == "function") callback();
//                    });
//                }
//            });
//
//        </script>
//        ';

        return $html;
    }

}