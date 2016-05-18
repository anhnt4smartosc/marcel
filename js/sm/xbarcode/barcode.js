/**
 * Javascript for handling AJAX events occur on Counting Inventory features
 * User: thangnv@smartosc.com
 * Date: 6/5/13
 * Time: 3:15 PM
 */

/* ENABLING NO-CONFLICT MODE IN JQUERY
 ===================================== */
jQuery.noConflict();

/* GLOBAL VARIABLE"
 ================================= */

/* BINDING EVENTS TO DOM ELEMENTS
 ================================= */
jQuery(document).ready(function () {


    /*
     @description    : Binding event for barcode-textbox (Events will be exposed when "ENTER" key is pressed
     @author         : thangnv@smartosc.com
     @date           : 6th June, 2013
     */
    jQuery('#barcode-id').keypress(function (e) {
        if (e.which == 13) {
            // Define variables
            var _i              = 0;
            var url             = jQuery('#frmBarcodeID').attr('action');
            var sm_barcode      = jQuery(this).val().toString();
            var sm_barcode_map  = new Array();
            var qtyScannedObjId = "#qty-scanned-";
            var qtyScannedVal   = 0;
            var symbologyType   = parseInt(jQuery('#sm-symbology-type').val());

            // Validate sm_barcode empty or not
            if (!sm_barcode) {
                return;
            } else if ((symbologyType == 0) &&
                (jQuery.trim(sm_barcode).length == 12)) {
                sm_barcode = '0' + jQuery.trim(sm_barcode);
            }
            
            // Parse all barcode into array
            jQuery('input[name="sm_barcode_hidden"]').each(function () {
                sm_barcode_map[_i] = jQuery(this).val().toString();
                _i++;
            });

            if (jQuery.inArray(sm_barcode, sm_barcode_map) == -1) {
                qtyScannedVal = 1;
                // Send ajax request to retrieve product information
                jQuery.ajax({
                    type: "GET",
                    dataType: "html",
                    url: url.toString(),
                    data: {
                        typeEx: 'addnew',
                        qtyUpdate: qtyScannedVal,
                        sm_barcode: sm_barcode
                    }
                }).done(function (html) {
                        var returnMsg = jQuery.parseJSON(html);
                        if (returnMsg.error) {
                            jQuery('#barcode-li-message').attr('class', 'error-msg');
                            jQuery('#barcodeMessageContent').text(returnMsg.msg);
                            if(jQuery('#barcodeMessage').css('display') == 'none'){
                                jQuery('#barcodeMessage').css('display','');
                                setTimeout(function(){
                                    jQuery('#barcodeMessage').css('display','none');
                                },20000);
                            }
                        } else {
                            var imgObj = '<br/><img src="'+returnMsg.image_url+'" alt="barcode" style="vertical-align:middle;"/>';
                            barcodeCountinventoryGridJsObject.doFilter();

                            jQuery('#barcode-view').html(imgObj);
                            jQuery('#barcode-li-message').attr('class', 'success-msg');
                            if(returnMsg.sku){
                                jQuery('#barcodeMessageContent').text('1 item(s) with SKU '+returnMsg.sku+' is added');
                            }
                            else{
                                jQuery('#barcodeMessageContent').text('1 item(s) added');
                            }

                            if(jQuery('#barcodeMessage').css('display') == 'none'){
                                jQuery('#barcodeMessage').css('display','');
                                setTimeout(function(){
                                    jQuery('#barcodeMessage').css('display','none');
                                },20000);
                            }
                            
                        }
                    });
            }
            else {
                if (jQuery('input[value=' + sm_barcode + ']').length) {
                    qtyScannedObjId += jQuery('input[value=' + sm_barcode + ']').attr('id').toString();
                }
                else {
                    jQuery('#barcode-li-message').attr('class', 'error-msg');
                    jQuery('#barcodeMessageContent').text("Barcode is incorect. Please try again.");
                    if(jQuery('#barcodeMessage').css('display') == 'none'){
                        jQuery('#barcodeMessage').css('display','');
                        setTimeout(function(){
                            jQuery('#barcodeMessage').css('display','none');
                        },20000);
                    }
                    return;
                }
                // Get scanned quantity of product
                qtyScannedVal = parseInt(jQuery(qtyScannedObjId).val());
                qtyScannedVal += 1;

                // Send ajax for modify
                jQuery.ajax({
                    type: "GET",
                    dataType: "html",
                    url: url.toString(),
                    data: {
                        typeEx: 'update',
                        qtyUpdate: qtyScannedVal,
                        sm_barcode: sm_barcode
                    }
                }).done(function (html) {
                        var returnMsg = jQuery.parseJSON(html);
                        if (returnMsg.error) {
                            jQuery('#barcode-li-message').attr('class', 'error-msg');
                            jQuery('#barcodeMessageContent').text(returnMsg.msg);
                            if(jQuery('#barcodeMessage').css('display') == 'none'){
                                jQuery('#barcodeMessage').css('display','');
                                setTimeout(function(){
                                    jQuery('#barcodeMessage').css('display','none');
                                },20000);
                            }
                        }
                        else {
                            jQuery(qtyScannedObjId).val(qtyScannedVal.toString());
                            var imgObj = '<br/><img src="'+returnMsg.image_url+'" alt="barcode" style="vertical-align:middle;"/>';
                            jQuery('#barcode-view').html(imgObj);
                            jQuery('#barcode-li-message').attr('class', 'success-msg');
                            if(returnMsg.sku){
                                jQuery('#barcodeMessageContent').text('1 item(s) with SKU '+returnMsg.sku+' is modified');
                            }
                            else{
                                jQuery('#barcodeMessageContent').text('1 item(s) modified');
                            }
                            if(jQuery('#barcodeMessage').css('display') == 'none'){
                                jQuery('#barcodeMessage').css('display','');
                                setTimeout(function(){
                                    jQuery('#barcodeMessage').css('display','none');
                                },20000);
                            }
                        }
                    });
            }
            // clear input box after key 'enter' is pressed
            jQuery(this).val('').focus();
        }
        return;
    });
});

/* FUNCTIONAL DECLARATION
 ======================== */

/*
 @description    : Correct current quantity of product by adding quantity of scanned product
 @author         : thangnv@smartosc.com
 @date           : 6th June, 2013
 @param
 _elementId (integer) --> Unique id of DOM element
 */
function _validateNumber(_elementId, _getType) {
    // Validating input values
    switch (_getType) {
        case 'val':
            if (jQuery(_elementId).val() != "") {
                var value = jQuery(_elementId).val().replace(/^\s\s*/, '').replace(/\s\s*$/, '');
                var intRegex = /^\d+$/;
                if (!intRegex.test(value)) {
                    alert("Field must be numeric.");
                    jQuery(_elementId).val('').focus();
                    return false;
                }
            } else {
                alert("Field cannot be blank.");
                jQuery(_elementId).val('').focus();
                return false
            }
            return true;
        case 'text':
            if (jQuery(_elementId).text() != "") {
                var value = jQuery(_elementId).text().replace(/^\s\s*/, '').replace(/\s\s*$/, '');
                var intRegex = /^\d+$/;
                if (!intRegex.test(value)) {
                    alert("Field must be numeric.");
                    jQuery(_elementId).text('').focus();
                    return false;
                }
            } else {
                alert("Field cannot be blank.");
                jQuery(_elementId).text('').focus();
                return false
            }
            return true;
        default:
            return false;
    }
}

/*
 @description    : Correct current quantity of product by adding quantity of scanned product
 @author         : thangnv@smartosc.com
 @date           : 6th June, 2013
 @param
  warehouseId (integer)
  warehouseLabel (string)
 */
function _validateSelectedWarehouse(warehouseId, warehouseLabel){
    if((typeof warehouseId == 'undefined') || (warehouseId == null) || (warehouseId == 0) || (warehouseLabel == 'allwarehouse') || (warehouseLabel == null) || (typeof warehouseLabel == 'undefined')){
        alert('You must select warehouse before update product information');
        return false;
    }
    return true;
}

/*
 @description    : Correct current quantity of product based on new quantity inputted by user
 @author         : thangnv@smartosc.com
 @date           : 6th June, 2013
 @param
 _productId (integer) --> Unique id of product
 url        (string)  --> Secret URL for ajax action
 */
function _correctStock(_productId, url, mwhFlag) {
    // Defining variables
    var _currInvenId = '#curr-inventory-';   //Initial DOM id for jQuery selector
    var _currInvenQty;                          //Currently quantity of product
    var _qtyScannedId = '#qty-scanned-';      //Initial DOM id for jQuery selector
    var _qtyScannedQty;                         //Quantity of scanned product
    var _correctbtnId = '#correct-stock-' + _productId.toString();
    var imgAjaxLoaderObjId = '#barcode-img-ajax--loadertr' + _productId.toString();


    if(!jQuery(_correctbtnId).is(":disabled")){
        jQuery(_correctbtnId).attr('disabled', true);
        // Preparing parameters
        _currInvenId += _productId.toString();
        _currInvenQty = parseInt(jQuery(_currInvenId).val());
        if(mwhFlag == 1){
            // get warehouse id and warehouse label
            var parentObj       = jQuery(_currInvenId).closest('tr');
            var selectBoxObj    = parentObj.find('td>select');
            var _selectBoxValue = selectBoxObj.val().toString().split('_');
            var warehouseLabel  = _selectBoxValue[0];
            var warehouseId     = _selectBoxValue[1];
            // validate selected warehouse
            if(!_validateSelectedWarehouse(warehouseId, warehouseLabel)){
                jQuery(_correctbtnId).attr('disabled', false);
                return;
            }
        }

        _qtyScannedId += _productId.toString();
        _qtyScannedQty = parseInt(jQuery(_qtyScannedId).val());

        // Validating input values - Numberic or non-numberic!
        if (!_validateNumber(_qtyScannedId, 'val')) {
            return;
        }

        // Execution
        jQuery(_qtyScannedId).val('0');
        if(mwhFlag == 1){
            jQuery.ajax({
                type: "GET",
                dataType: "html",
                url: url.toString(),
                data: {
                    productId    : _productId,
                    currInvenQty : _currInvenQty,
                    qtyScannedQty: _qtyScannedQty,
                    warehouseId  : warehouseId,
                    warehouseLbl : warehouseLabel
                },
                beforeSend: function(){
                    jQuery(imgAjaxLoaderObjId).show();
                }
            }).done(function (html) {
                    jQuery(imgAjaxLoaderObjId).hide();
                    jQuery(_currInvenId).val(_qtyScannedQty);
                    var returnMsg = jQuery.parseJSON(html);
                    if (returnMsg.error) {
                        jQuery('#barcode-li-message').attr('class', 'error-msg');
                        jQuery('#barcodeMessageContent').text(returnMsg.msg);
                        if(jQuery('#barcodeMessage').css('display') == 'none'){
                            jQuery('#barcodeMessage').css('display','');
                            setTimeout(function(){
                                jQuery('#barcodeMessage').css('display','none');
                            },20000);
                        }
                    }
                    else {
                        jQuery('#barcode-li-message').attr('class', 'success-msg');
                        jQuery('#barcodeMessageContent').text('1 item(s) modified');
                        if(jQuery('#barcodeMessage').css('display') == 'none'){
                            jQuery('#barcodeMessage').css('display','');
                            setTimeout(function(){
                                jQuery('#barcodeMessage').css('display','none');
                            },20000);
                        }
                    }
                    jQuery(_correctbtnId).attr('disabled', false);
                });
        } else {
            jQuery.ajax({
                type: "GET",
                dataType: "html",
                url: url.toString(),
                data: {
                    productId: _productId,
                    currInvenQty: _currInvenQty,
                    qtyScannedQty: _qtyScannedQty
                },
                beforeSend: function(){
                    jQuery(imgAjaxLoaderObjId).show();
                }
            }).done(function (html) {
                    jQuery(imgAjaxLoaderObjId).hide();
                    jQuery(_currInvenId).val(_qtyScannedQty);
                    var returnMsg = jQuery.parseJSON(html);
                    if (returnMsg.error) {
                        jQuery('#barcode-li-message').attr('class', 'error-msg');
                        jQuery('#barcodeMessageContent').text(returnMsg.msg);
                        if(jQuery('#barcodeMessage').css('display') == 'none'){
                            jQuery('#barcodeMessage').css('display','');
                            setTimeout(function(){
                                jQuery('#barcodeMessage').css('display','none');
                            },20000);
                        }
                    }
                    else {
                        jQuery('#barcode-li-message').attr('class', 'success-msg');
                        jQuery('#barcodeMessageContent').text('1 item(s) modified');
                        if(jQuery('#barcodeMessage').css('display') == 'none'){
                            jQuery('#barcodeMessage').css('display','');
                            setTimeout(function(){
                                jQuery('#barcodeMessage').css('display','none');
                            },20000);
                        }
                    }
                    jQuery(_correctbtnId).attr('disabled', false);
                });
        }
    }

    return;
}

/*
 @description    : Correct current quantity of product by adding quantity of scanned product
 @author         : thangnv@smartosc.com
 @date           : 6th June, 2013
 @param
 _productId (integer) --> Unique id of product
 url        (string)  --> Secret URL for ajax action
 */
function _plusStock(_productId, url, mwhFlag) {
    // Defining variables
    var _currInvenId = '#curr-inventory-';   //Initial DOM id for jQuery selector
    var _currInvenQty;                          //Current quantity of product
    var _qtyScannedId = '#qty-scanned-';      //Initial DOM id for jQuery selector
    var _qtyScannedQty;                         //Quantity of scanned product
    var _plusStockBtnId = '#plus-stock-' + _productId.toString();
    var imgAjaxLoaderObjId = '#barcode-img-ajax-loader-tr' + _productId.toString();

    if(!jQuery(_plusStockBtnId).is(":disabled")){
        jQuery(_plusStockBtnId).attr('disabled', true)
        // Preparing parameters
        _currInvenId += _productId.toString();
        _currInvenQty = parseInt(jQuery(_currInvenId).val());

        if(mwhFlag == 1){
            // get warehouse id and warehouse label
            var parentObj       = jQuery(_currInvenId).closest('tr');
            var selectBoxObj    = parentObj.find('td>select');
            var _selectBoxValue = selectBoxObj.val().toString().split('_');
            var warehouseLabel  = _selectBoxValue[0];
            var warehouseId     = _selectBoxValue[1];
            // validate selected warehouse
            if(!_validateSelectedWarehouse(warehouseId, warehouseLabel)){
                jQuery(_plusStockBtnId).attr('disabled', false);
                return;
            }
        }

        _qtyScannedId += _productId.toString();
        _qtyScannedQty = parseInt(jQuery(_qtyScannedId).val());

        // Validating input values - Numberic or non-numberic!
        if (!_validateNumber(_qtyScannedId, 'val')) {
            return;
        }

        // Execution
        _currInvenQty += _qtyScannedQty;
        jQuery(_qtyScannedId).val('0');

        if(mwhFlag == 1){
            jQuery.ajax({
                type: "GET",
                dataType: "html",
                url: url.toString(),
                data: {
                    productId    : _productId,
                    currInvenQty : _currInvenQty,
                    qtyScannedQty: _qtyScannedQty,
                    warehouseId  : warehouseId,
                    warehouseLbl : warehouseLabel
                },
                beforeSend: function(){
                    jQuery(imgAjaxLoaderObjId).show();
                }
            }).done(function (html) {
                    jQuery(imgAjaxLoaderObjId).hide();
                    jQuery(_currInvenId).val(_currInvenQty);
                    var returnMsg = jQuery.parseJSON(html);
                    if (returnMsg.error) {
                        jQuery('#barcode-li-message').attr('class', 'error-msg');
                        jQuery('#barcodeMessageContent').text(returnMsg.msg);
                        if(jQuery('#barcodeMessage').css('display') == 'none'){
                            jQuery('#barcodeMessage').css('display','');
                            setTimeout(function(){
                                jQuery('#barcodeMessage').css('display','none');
                            },20000);
                        }
                    }
                    else {
                        jQuery('#barcode-li-message').attr('class', 'success-msg');
                        jQuery('#barcodeMessageContent').text('1 item(s) modified');
                        if(jQuery('#barcodeMessage').css('display') == 'none'){
                            jQuery('#barcodeMessage').css('display','');
                            setTimeout(function(){
                                jQuery('#barcodeMessage').css('display','none');
                            },20000);
                        }
                    }
                    jQuery(_plusStockBtnId).attr('disabled', false);
                });
        } else {
            jQuery.ajax({
                type: "GET",
                dataType: "html",
                url: url.toString(),
                data: {
                    productId: _productId,
                    currInvenQty: _currInvenQty,
                    qtyScannedQty: _qtyScannedQty
                },
                beforeSend: function(){
                    jQuery(imgAjaxLoaderObjId).show();
                }
            }).done(function (html) {
                    jQuery(imgAjaxLoaderObjId).hide();
                    jQuery(_currInvenId).val(_currInvenQty);
                    var returnMsg = jQuery.parseJSON(html);
                    if (returnMsg.error) {
                        jQuery('#barcode-li-message').attr('class', 'error-msg');
                        jQuery('#barcodeMessageContent').text(returnMsg.msg);
                        if(jQuery('#barcodeMessage').css('display') == 'none'){
                            jQuery('#barcodeMessage').css('display','');
                            setTimeout(function(){
                                jQuery('#barcodeMessage').css('display','none');
                            },20000);
                        }
                    }
                    else {
                        jQuery('#barcode-li-message').attr('class', 'success-msg');
                        jQuery('#barcodeMessageContent').text('1 item(s) modified');
                        if(jQuery('#barcodeMessage').css('display') == 'none'){
                            jQuery('#barcodeMessage').css('display','');
                            setTimeout(function(){
                                jQuery('#barcodeMessage').css('display','none');
                            },20000);
                        }
                    }
                    jQuery(_plusStockBtnId).attr('disabled', false);
                });
        }

    }

    return;
}

/*
 @description    : Correct current quantity of product by subtracting an amount which is equal to the quantity of scanned product
 @author         : thangnv@smartosc.com
 @date           : 6th June, 2013
 @param
 _productId (integer) --> Unique id of product
 url        (string)  --> Secret URL for ajax action
 */
function _reduceStock(_productId, url, mwhFlag) {
    // Defining variables
    var _currInvenId = '#curr-inventory-';   //Initial DOM id for jQuery selector
    var _currInvenQty;                          //Current quantity of product
    var _qtyScannedId = '#qty-scanned-';      //Initial DOM id for jQuery selector
    var _qtyScannedQty;                         //Quantity of scanned product
    var _reduceBtnId = '#reduce-stock-' + _productId;
    var imgAjaxLoaderObjId = '#barcode-img-ajax-loader-tr' + _productId.toString();

    if(!jQuery(_reduceBtnId).is(":disabled")){
        jQuery(_reduceBtnId).attr('disabled', true);

        // Preparing parameters
        _currInvenId += _productId.toString();
        _currInvenQty = parseInt(jQuery(_currInvenId).val());

        if(mwhFlag == 1){
            // get warehouse id and warehouse label
            var parentObj       = jQuery(_currInvenId).closest('tr');
            var selectBoxObj    = parentObj.find('td>select');
            var _selectBoxValue = selectBoxObj.val().toString().split('_');
            var warehouseLabel  = _selectBoxValue[0];
            var warehouseId     = _selectBoxValue[1];
            // validate selected warehouse
            if(!_validateSelectedWarehouse(warehouseId, warehouseLabel)){
                jQuery(_reduceBtnId).attr('disabled', false);
                return;
            }
        }

        _qtyScannedId += _productId.toString();
        _qtyScannedQty = parseInt(jQuery(_qtyScannedId).val());

        // Validating input values - Numberic or non-numberic!
        if (!_validateNumber(_qtyScannedId, 'val')) {
            return;
        }

        // Execution
        if (_currInvenQty >= _qtyScannedQty) {
            _currInvenQty -= _qtyScannedQty;
            jQuery(_qtyScannedId).val('0');
            if(mwhFlag == 1){
                jQuery.ajax({
                    type: "GET",
                    dataType: "html",
                    url: url.toString(),
                    data: {
                        productId    : _productId,
                        currInvenQty : _currInvenQty,
                        qtyScannedQty: _qtyScannedQty,
                        warehouseId  : warehouseId,
                        warehouseLbl : warehouseLabel
                    },
                    beforeSend: function(){
                        jQuery(imgAjaxLoaderObjId).show();
                    }
                }).done(function (html) {
                        jQuery(imgAjaxLoaderObjId).hide();
                        jQuery(_currInvenId).val(_currInvenQty);
                        var returnMsg = jQuery.parseJSON(html);
                        if (returnMsg.error) {
                            jQuery('#barcode-li-message').attr('class', 'error-msg');
                            jQuery('#barcodeMessageContent').text(returnMsg.msg);
                            if(jQuery('#barcodeMessage').css('display') == 'none'){
                                jQuery('#barcodeMessage').css('display','');
                                setTimeout(function(){
                                    jQuery('#barcodeMessage').css('display','none');
                                },20000);
                            }
                        }
                        else {
                            jQuery('#barcode-li-message').attr('class', 'success-msg');
                            jQuery('#barcodeMessageContent').text('1 item(s) modified');
                            if(jQuery('#barcodeMessage').css('display') == 'none'){
                                jQuery('#barcodeMessage').css('display','');
                                setTimeout(function(){
                                    jQuery('#barcodeMessage').css('display','none');
                                },20000);
                            }
                        }
                        jQuery(_reduceBtnId).attr('disabled', false)
                    });
                }
            else {
                jQuery.ajax({
                    type: "GET",
                    dataType: "html",
                    url: url.toString(),
                    data: {
                        productId: _productId,
                        currInvenQty: _currInvenQty,
                        qtyScannedQty: _qtyScannedQty
                    },
                    beforeSend: function(){
                        jQuery(imgAjaxLoaderObjId).show();
                    }
                }).done(function (html) {
                        jQuery(imgAjaxLoaderObjId).hide();
                        jQuery(_currInvenId).val(_currInvenQty);
                        var returnMsg = jQuery.parseJSON(html);
                        if (returnMsg.error) {
                            jQuery('#barcode-li-message').attr('class', 'error-msg');
                            jQuery('#barcodeMessageContent').text(returnMsg.msg);
                            if(jQuery('#barcodeMessage').css('display') == 'none'){
                                jQuery('#barcodeMessage').css('display','');
                                setTimeout(function(){
                                    jQuery('#barcodeMessage').css('display','none');
                                },20000);
                            }
                        }
                        else {
                            jQuery('#barcode-li-message').attr('class', 'success-msg');
                            jQuery('#barcodeMessageContent').text('1 item(s) modified');
                            if(jQuery('#barcodeMessage').css('display') == 'none'){
                                jQuery('#barcodeMessage').css('display','');
                                setTimeout(function(){
                                    jQuery('#barcodeMessage').css('display','none');
                                },20000);
                            }
                        }
                        jQuery(_reduceBtnId).attr('disabled', false)
                    });
            }
        }
        else {
            jQuery(_qtyScannedId).focus();
            jQuery('#barcode-li-message').attr('class', 'error-msg');
            jQuery('#barcodeMessageContent').text("Scanned product quantity should be less than current product quantity.");
            if(jQuery('#barcodeMessage').css('display') == 'none'){
                jQuery('#barcodeMessage').css('display','');
                setTimeout(function(){
                    jQuery('#barcodeMessage').css('display','none');
                },20000);
            }
            jQuery(_reduceBtnId).attr('disabled', false)
        }
    }
    return;
}

/*
 @description    : Delete product on grid
 @author         : thangnv@smartosc.com
 @date           : 6th June, 2013
 @param
 _productId (integer) --> Unique id of product
 url        (string)  --> Secret URL for ajax action
 */
function _deleteStock(_productId, url, mwhFlag) {
    // Defining variables
    var _currInvenId = '#curr-inventory-';   //Initial DOM id for jQuery selector

    // Preparing parameters
    _currInvenId += _productId.toString();

    var _deletebtnId = '#delete-stock-' + _productId;

    // Execution
    if(!jQuery(_deletebtnId).is(":disabled")){
        jQuery(_deletebtnId).attr('disabled', true);
        jQuery.ajax({
            type: "GET",
            dataType: "html",
            url: url.toString(),
            data: {productId: _productId}
        }).done(function (html) {
                var returnMsg = jQuery.parseJSON(html);
                if (returnMsg.error) {
                    jQuery('#barcode-li-message').attr('class', 'error-msg');
                    jQuery('#barcodeMessageContent').text(returnMsg.msg);
                    if(jQuery('#barcodeMessage').css('display') == 'none'){
                        jQuery('#barcodeMessage').css('display','');
                        setTimeout(function(){
                            jQuery('#barcodeMessage').css('display','none');
                        },20000);
                    }
                }
                else {
                    var gridCheckBoxObj = jQuery(_currInvenId).closest('tr').find(':checkbox:checked');
                    if(gridCheckBoxObj.length > 0) {
                        var countCheckItem = parseInt(jQuery('#barcodeCountinventoryGrid_massaction-count').text());
                        var checkItemValue = gridCheckBoxObj.val();
                        if(countCheckItem > 0){
                            gridCheckBoxObj.attr('checked', false);
                            barcodeCountinventoryGrid_massactionJsObject.checkedString = barcodeCountinventoryGrid_massactionJsObject.checkedString.split(",");
                            var _index = barcodeCountinventoryGrid_massactionJsObject.checkedString.indexOf(checkItemValue)
                            barcodeCountinventoryGrid_massactionJsObject.checkedString.splice(_index, 1);
                            barcodeCountinventoryGrid_massactionJsObject.checkedString = barcodeCountinventoryGrid_massactionJsObject.checkedString.toString();
                        }
                    }

                    barcodeCountinventoryGridJsObject.doFilter();

                    jQuery('#barcode-li-message').attr('class', 'success-msg');
                    if(returnMsg.sku){
                        jQuery('#barcodeMessageContent').text('1 item(s) with SKU '+returnMsg.sku+' is remove');
                    }
                    else{
                        jQuery('#barcodeMessageContent').text('1 item(s) remove');
                    }
                    if(jQuery('#barcodeMessage').css('display') == 'none'){
                        jQuery('#barcodeMessage').css('display','');
                        setTimeout(function(){
                            jQuery('#barcodeMessage').css('display','none');
                        },20000);
                    }
                }
                jQuery(_deletebtnId).attr('disabled', false)
            });
    }
    return;
}

/*
 @description    : Save quantity of scanned product which is inputed by user (on keypress - enter key)
 @author         : thangnv@smartosc.com
 @date           : 6th June, 2013
 @param
 _productId (integer) --> Unique id of product
 event                --> Event listener
 url                  --> Secret ajax URL
 */
function _updateScannedQty(event, _productId, url) {
    // Defining variables
    var _qtyScannedId = '#qty-scanned-';      //Initial DOM id for jQuery selector
    var _qtyScannedVal;                       //Scanned quantity value
    var smBarcodeObj;                         //DOM object
    var sm_barcode;                           //Barcode of product
    var imgAjaxLoaderObjId = '#scanned-qty-img-ajax-loader-tr' + _productId.toString();

    // Preparing parameters
    _qtyScannedId += _productId.toString();

    event = (event) ? event : ((window.event) ? window.event : "");
    if (event.which == 13 || event.keyCode == 13) {
        // Validating input value (numberic or non-numberic)
        if (!_validateNumber(_qtyScannedId, 'val')) {
            return;
        }

        // Preparing parameters for postting to server
        _qtyScannedVal = parseInt(jQuery(_qtyScannedId).val());
        smBarcodeObj = jQuery("#div_functional_" + _productId).children("#" + _productId);
        sm_barcode = smBarcodeObj.val();

        // Send ajax request
        jQuery.ajax({
            type: "GET",
            dataType: "html",
            url: url.toString(),
            data: {
                typeEx: 'update',
                productId: _productId,
                qtyUpdate: _qtyScannedVal,
                sm_barcode: sm_barcode
            },
            beforeSend: function(){
                jQuery(imgAjaxLoaderObjId).show();
            }
        }).done(function (html) {
                jQuery(imgAjaxLoaderObjId).hide();
                var returnMsg = jQuery.parseJSON(html);
                if (returnMsg.error) {
                    jQuery('#barcode-li-message').attr('class', 'error-msg');
                    jQuery('#barcodeMessageContent').text(returnMsg.msg);
                    if(jQuery('#barcodeMessage').css('display') == 'none'){
                        jQuery('#barcodeMessage').css('display','');
                        setTimeout(function(){
                            jQuery('#barcodeMessage').css('display','none');
                        },20000);
                    }
                }
                else {
                    jQuery('#barcode-li-message').attr('class', 'success-msg');
                    jQuery('#barcodeMessageContent').text('1 item(s) modified');
                    if(jQuery('#barcodeMessage').css('display') == 'none'){
                        jQuery('#barcodeMessage').css('display','');
                        setTimeout(function(){
                            jQuery('#barcodeMessage').css('display','none');
                        },20000);
                    }
                }
            });
    }
    return;
}

/*
 @description    : Save quantity of scanned product which is inputed by user (OnBlur event)
 @author         : thangnv@smartosc.com
 @date           : 6th June, 2013
 @param
 _productId (integer) --> Unique id of product
 event                --> Event listener
 url                  --> Secret ajax URL
 */
function _updateScannedQtyOnBlur(_productId, url) {
    // Defining variables
    var _qtyScannedId = '#qty-scanned-';        //Initial DOM id for jQuery selector
    var _qtyScannedVal;                         //Scanned quantity value
    var smBarcodeObj;                           //DOM object
    var sm_barcode;                             //Barcode of product
    var imgAjaxLoaderObjId = '#scanned-qty-img-ajax-loader-tr' + _productId.toString();

    // Preparing parameters
    _qtyScannedId += _productId.toString();

    if (!_validateNumber(_qtyScannedId, 'val')) {
        return;
    }

    // Preparing parameters for postting to server
    _qtyScannedVal = parseInt(jQuery(_qtyScannedId).val());
    smBarcodeObj = jQuery("#div_functional_" + _productId).children("#" + _productId);
    sm_barcode = smBarcodeObj.val();

    // Send ajax request
    jQuery.ajax({
        type: "GET",
        dataType: "html",
        url: url.toString(),
        data: {
            typeEx: 'update',
            productId: _productId,
            qtyUpdate: _qtyScannedVal,
            sm_barcode: sm_barcode
        },
        beforeSend: function(){
            jQuery(imgAjaxLoaderObjId).show();
        }
    }).done(function (html) {
            jQuery(imgAjaxLoaderObjId).hide();
            var returnMsg = jQuery.parseJSON(html);
            if (returnMsg.error) {
                jQuery('#barcode-li-message').attr('class', 'error-msg');
                jQuery('#barcodeMessageContent').text(returnMsg.msg);
                if(jQuery('#barcodeMessage').css('display') == 'none'){
                    jQuery('#barcodeMessage').css('display','');
                    setTimeout(function(){
                        jQuery('#barcodeMessage').css('display','none');
                    },20000);
                }
            }
            else {
                jQuery('#barcode-li-message').attr('class', 'success-msg');
                jQuery('#barcodeMessageContent').text( '1 item(s) modified');
                if(jQuery('#barcodeMessage').css('display') == 'none'){
                    jQuery('#barcodeMessage').css('display','');
                    setTimeout(function(){
                        jQuery('#barcodeMessage').css('display','none');
                    },20000);
                }
            }
        });
    return;
}

function massActionSubmitBtn(mwhFlag) {
    /*
     @description    : Binding event for barcode-textbox (Events will be exposed when "ENTER" key is pressed
     @author         : thangnv@smartosc.com
     @date           : 6th June, 2013
     */
    if(!jQuery('#mass-action-submit-btn').is(":disabled")){
        jQuery('#mass-action-submit-btn').attr('disabled', true);
        //Declare variables
        var type = jQuery('#barcodeCountinventoryGrid_massaction-select').val().toString();
        var url = '';
        var postData = barcodeCountinventoryGrid_massactionJsObject.checkedString;
        var reduce_error_flag = false;
        // Validate selected value
        if (type.length <= 0 || type == '') {
            alert("Please select an action");
            jQuery('#mass-action-submit-btn').attr('disabled', false);
            return;
        }

        if (postData.length === 0) {
            alert('Please select item(s)');
            jQuery('#mass-action-submit-btn').attr('disabled', false);
            return;
        }

        // Get secrect URL
        var url = jQuery('#ajax-mass-action-execute').val().toString();

        // Send ajax request
        if (!reduce_error_flag) {
            if(mwhFlag == 1){
                var optionVal = jQuery('#action-warehouse-select').val().toString();
                if (optionVal == '' || optionVal == null){
                    alert ('You must select a warehouse first');
                    jQuery('#mass-action-submit-btn').attr('disabled', false);
                }
                else {
                    optionVal = optionVal.split('_');
                    var warehouseLbl = optionVal[0];
                    var warehouseId  = optionVal[1];
                    jQuery.ajax({
                        type: "GET",
                        dataType: "html",
                        url: url.toString(),
                        data: {
                            type         : type,
                            postData     : postData.toString(),
                            warehouseLbl : warehouseLbl,
                            warehouseId  : warehouseId
                        }
                    }).done(function (html) {
                            var returnMsg = jQuery.parseJSON(html);
                            if (returnMsg.error) {
                                jQuery('#barcode-li-message').attr('class', 'error-msg');
                                jQuery('#barcodeMessageContent').text(returnMsg.msg);
                                if(jQuery('#barcodeMessage').css('display') == 'none'){
                                    jQuery('#barcodeMessage').css('display','');
                                    setTimeout(function(){
                                        jQuery('#barcodeMessage').css('display','none');
                                    },20000);
                                }
                            }
                            else {
                                barcodeCountinventoryGrid_massactionJsObject.unselectAll();
                                jQuery('#barcodeCountinventoryGrid_massaction-count').text(0);
                                barcodeCountinventoryGridJsObject.doFilter();
                                
                                jQuery('#barcode-li-message').attr('class', 'success-msg');
                                if(type == 'deleteall'){
                                    jQuery('#barcodeMessageContent').text(returnMsg.success_count + ' item(s) deleted');

                                } else {
                                    if(returnMsg.success_count > 0){
                                        jQuery('#barcodeMessageContent').text(returnMsg.success_count + ' item(s) modified');
                                    }else{
                                        jQuery('#barcode-li-message').attr('class', 'error-msg');
                                        jQuery('#barcodeMessageContent').text('Scanned product quantity should be less than current product quantity.');
                                    }
                                }
                                if(jQuery('#barcodeMessage').css('display') == 'none'){
                                    jQuery('#barcodeMessage').css('display','');
                                    setTimeout(function(){
                                        jQuery('#barcodeMessage').css('display','none');
                                    },20000);
                                }
                            }
                            jQuery('#mass-action-submit-btn').attr('disabled', false);
                        });
                }
            }
            else {
                jQuery.ajax({
                    type: "GET",
                    dataType: "html",
                    url: url.toString(),
                    data: {
                        type: type,
                        postData: postData.toString()
                    }
                }).done(function (html) {
                        var returnMsg = jQuery.parseJSON(html);
                        if (returnMsg.error) {
                            jQuery('#barcode-li-message').attr('class', 'error-msg');
                            jQuery('#barcodeMessageContent').text(returnMsg.msg);
                            if(jQuery('#barcodeMessage').css('display') == 'none'){
                                jQuery('#barcodeMessage').css('display','');
                                setTimeout(function(){
                                    jQuery('#barcodeMessage').css('display','none');
                                },20000);
                            }
                        }
                        else {
                            barcodeCountinventoryGrid_massactionJsObject.unselectAll();
                            jQuery('#barcodeCountinventoryGrid_massaction-count').text(0);
                            barcodeCountinventoryGridJsObject.doFilter();

                            jQuery('#barcode-li-message').attr('class', 'success-msg');
                            if(type == 'deleteall'){
                                jQuery('#barcodeMessageContent').text(returnMsg.success_count + ' item(s) deleted');

                            } else {
                                if(returnMsg.success_count > 0){
                                    jQuery('#barcodeMessageContent').text(returnMsg.success_count + ' item(s) modified');
                                }else{
                                    jQuery('#barcode-li-message').attr('class', 'error-msg');
                                    jQuery('#barcodeMessageContent').text('Scanned product quantity should be less than current product quantity.');
                                }
                            }
                            if(jQuery('#barcodeMessage').css('display') == 'none'){
                                jQuery('#barcodeMessage').css('display','');
                                setTimeout(function(){
                                    jQuery('#barcodeMessage').css('display','none');
                                },20000);
                            }
                        }
                        jQuery('#mass-action-submit-btn').attr('disabled', false);
                    });
            }
        }
    }
    return;
}

/*
 @description   : Get current quantity based on warehouse
 @JSEvent       : onChange event occur on <select> element
 @author        : thangnv@smartosc.com
 @date          : 6th June, 2013
 @param
 */
function getCurrQtyWarehouseOnchange(obj, url, productId){
    // Get Post parameters
    var optionVal      = jQuery(obj).val().toString().split('_');
    var warehouseLabel = optionVal[0];
    var warehouseId    = optionVal[1];
    var imgAjaxLoaderObjId = '#barcode-img-ajax-loader-tr' + productId.toString();

    // Send Ajax request
    jQuery.ajax({
        type: "GET",
        dataType: "html",
        url: url.toString(),
        data: {
            productId      : productId,
            warehouseId    : warehouseId,
            warehouseLabel : warehouseLabel
        },
        beforeSend: function(){
            jQuery(imgAjaxLoaderObjId).show();
        }
    }).done(function (html) {
            jQuery(imgAjaxLoaderObjId).hide();
            var returnMsg = jQuery.parseJSON(html);
            if (returnMsg.error) {
                jQuery('#barcode-li-message').attr('class', 'error-msg');
                jQuery('#barcodeMessageContent').text(returnMsg.msg);
                if(jQuery('#barcodeMessage').css('display') == 'none'){
                    jQuery('#barcodeMessage').css('display','');
                    setTimeout(function(){
                        jQuery('#barcodeMessage').css('display','none');
                    },20000);
                }
            }
            else {
                var currQtyHTMLObj = '#curr-inventory-' + productId;
                jQuery(currQtyHTMLObj).val(parseInt(returnMsg.productQty));
            }
        });
    return;
}

/*
 @description    : Update current quantity of product which is inputed by user (onblur)
 @author         : thangnv@smartosc.com
 @date           : 30th July, 2013
 @param
 _productId (integer) --> Unique id of product
 url                  --> Secret ajax URL
 mwhFlag              --> Flag to determine extension is avaialable or not.
 */
function _updateCurrentQtyOnBlur(_productId, url, mwhFlag) {
    // Defining variables
    var _currQtyObjId = '#curr-inventory-';         //Initial DOM id for jQuery selector
    var newProductQty;                              //New product quantity.
    var imgAjaxLoaderObjId = '#barcode-img-ajax-loader-tr' + _productId.toString();
    if (mwhFlag == 1) {
        var parentObj;                                  //Parent Object of the current Object
        var selectBoxObj;                               //Select Box Object which is corresponding to the current Object
        var _selectBoxValue;                            //Value of select box
        var warehouseId;                                //Warehouse id
        var warehouseLabel;                             //Warehouse label
    }

    // Preparing parameter(s)
    _currQtyObjId += _productId.toString();

    // Validating input value (numberic or non-numberic)
    if (!_validateNumber(_currQtyObjId, 'val')) {
        return;
    }

    // Validate selected warehouse
    if (mwhFlag == 1) {
        parentObj       = jQuery(_currQtyObjId).closest('tr');
        selectBoxObj    = parentObj.find('td>select');
        _selectBoxValue = selectBoxObj.val().toString().split('_');
        warehouseLabel  = _selectBoxValue[0];
        warehouseId     = _selectBoxValue[1];

        if (!_validateSelectedWarehouse(warehouseId,warehouseLabel)) {
            return;
        }
    }

    // Prepare paremeters
    newProductQty   = jQuery(_currQtyObjId).val();

    // Send ajax request for update current quantity
    if (mwhFlag == 1) {
        jQuery.ajax({type: "GET",
            dataType: "html",
            url: url.toString(),
            data: {
                productId      : _productId,
                warehouseId    : warehouseId,
                warehouseLabel : warehouseLabel,
                newProductQty  : newProductQty
            },
            beforeSend: function(){
                jQuery(imgAjaxLoaderObjId).show();
            }
        }).done(function (html) {
                jQuery(imgAjaxLoaderObjId).hide();
                var returnMsg = jQuery.parseJSON(html);
                if(returnMsg.error){
                    jQuery('#barcode-li-message').attr('class', 'error-msg');
                    jQuery('#barcodeMessageContent').text(returnMsg.msg);
                    if(jQuery('#barcodeMessage').css('display') == 'none'){
                        jQuery('#barcodeMessage').css('display', '');
                        setTimeout(function(){
                            jQuery('#barcodeMessage').css('display', 'none');
                        }, 20000);
                    }
                }
                else {
                    jQuery('#barcode-li-message').attr('class', 'success-msg');
                    jQuery('#barcodeMessageContent').text(returnMsg.msg);
                    if(jQuery('#barcodeMessage').css('display') == 'none'){
                        jQuery('#barcodeMessage').css('display', '');
                        setTimeout(function(){
                            jQuery('#barcodeMessage').css('display', 'none');
                        }, 20000);
                    }
                }
            });
        return;
    } else {
        jQuery.ajax({type: "GET",
            dataType: "html",
            url: url.toString(),
            data: {
                productId      : _productId,
                newProductQty  : newProductQty
            },
            beforeSend: function(){
                jQuery(imgAjaxLoaderObjId).show();
            }
        }).done(function (html) {
                jQuery(imgAjaxLoaderObjId).hide();
                var returnMsg = jQuery.parseJSON(html);
                if(returnMsg.error){
                    jQuery('#barcode-li-message').attr('class', 'error-msg');
                    jQuery('#barcodeMessageContent').text(returnMsg.msg);
                    if(jQuery('#barcodeMessage').css('display') == 'none'){
                        jQuery('#barcodeMessage').css('display', '');
                        setTimeout(function(){
                            jQuery('#barcodeMessage').css('display', 'none');
                        }, 20000);
                    }
                }
                else {
                    jQuery('#barcode-li-message').attr('class', 'success-msg');
                    jQuery('#barcodeMessageContent').text(returnMsg.msg);
                    if(jQuery('#barcodeMessage').css('display') == 'none'){
                        jQuery('#barcodeMessage').css('display', '');
                        setTimeout(function(){
                            jQuery('#barcodeMessage').css('display', 'none');
                        }, 20000);
                    }
                }
            });
        return;
    }
}

/*
 @description    : This function is used to display the warehouse checkbox. User have to select warehouse before perform MassAction
 @author         : thangnv@smartosc.com
 @date           : 2nd August, 2013
 @param
 */
function massActionSelectWarehouse(){
    jQuery('#action-warehouse-select').show('fast');
    return;
}