/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Inventoryfulfillment
 * @copyright   Copyright (c) 2015 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

Ajax.Barcodeautocompleter = Class.create(Autocompleter.Base, {
    initialize: function (element, update, url, options, orderId, urlAfter) {
        this.baseInitialize(element, update, options);
        this.options.asynchronous = true;
        this.options.onComplete = this.onComplete.bind(this);
        this.options.defaultParams = this.options.parameters || null;
        this.options.minChars = 6;
        this.url = url;
        this.orderId = orderId;
        this.urlAfter = urlAfter;
    },

    getUpdatedChoices: function () {
        this.startIndicator();

        var entry = encodeURIComponent(this.options.paramName) + '=' +
            encodeURIComponent(this.getToken());

        this.options.parameters = this.options.callback ?
            this.options.callback(this.element, entry) : entry;

        if (this.options.defaultParams)
            this.options.parameters += '&' + this.options.defaultParams;

        new Ajax.Request(this.url, this.options);
    },

    onComplete: function (request) {
        try {
            var json = JSON.parse(request.responseText);
            if (json.show) {
                barcodeCtrl.markCheck(this.urlAfter, this.orderId, json.barcode_id, this.element);
                $(this.options.indicator).hide();
                this.update.hide();
                //$('barcode_search_indicator').hide();
                //$('barcode_search_autocomplete').hide();
            }
        }
        catch (e) {
            this.updateChoices(request.responseText);
        }

    }
});

var barcodeController = new Class.create();
barcodeController.prototype = {
    initialize: function () {

    },
    getSelectionId: function (li) {
        return false;
    },
    markCheck: function (url, orderId, barcode_id, element) {
        this.element = element;
        Element.show('loading-mask');
        var parameters = {barcode_id: barcode_id, order_id: orderId};
        var request = new Ajax.Request(url, {
            method: 'post',
            parameters: parameters,
            onFailure: '',
            onSuccess: function (transport) {
                if (transport.status == 200) {
                    var result = transport.responseText.evalJSON();
                    if (result.status == 1) {
                        var qtyToShip = parseFloat($('qty_to_ship_' + result.package_id).innerHTML);
                        var pickedQty = parseFloat($('pick_qty_item_' + result.package_id).value);
                        if (qtyToShip > 0) {
                            if (pickedQty + 1 > qtyToShip) {
                                alert('Picked Qty exceeded Qty to Ship');
                            } else {
                                pickedQty = pickedQty + 1;
                                $('pick_qty_item_' + result.package_id).value = pickedQty;
                                
                                if (pickedQty == qtyToShip) {
                                    var checkItem = $('pick_qty_item_' + result.package_id).up('tr').down('.required-pack-item');
                                    if(checkItem)
                                        checkItem.checked = true;
                                }
                                
                            }
                        }
                        this.element.value = '';
                    } else {
                        alert('Barcode product not found in this order');
                    }
                    Element.hide('loading-mask');
                }
            }.bind(this)
        });
    },
    printPackageBarcode: function () {
        $('ffm_printbarcode_form').submit();
    }
}

var barcodeCtrl = new barcodeController();