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

Ajax.PackageBarcodeautocompleter = Class.create(Autocompleter.Base, {
    initialize: function (element, update, url, options) {
        this.baseInitialize(element, update, options);
        this.options.asynchronous = true;
        this.options.onComplete = this.onComplete.bind(this);
        this.options.defaultParams = this.options.parameters || null;
        this.url = url;
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
            if (json.status) {
                packagebarcodeCtrl.markCheck(json.shipment_id);
                $('barcode_search_indicator').hide();
                $('barcode_search_autocomplete').hide();
            }
        }
        catch (e) {
            this.updateChoices(request.responseText);
        }

    }
});

var packagebarcodeController = new Class.create();
packagebarcodeController.prototype = {
    initialize: function () {

    },
    getSelectionId: function (li) {
        return false;
    },
    markCheck: function (shipmentId) {
        Element.show('loading-mask');
        if ($('barcode_search').up('.order-group').down('.grid')) {
            var checkboxes = $('barcode_search').up('.order-group').down('.grid').select('input.ship-order');
            if (checkboxes.length > 0) {
                for (var i = 0; i < checkboxes.length; i++) {
                    if (checkboxes[i].value == shipmentId) {
                        checkboxes[i].checked = true;
                        break;
                    }
                }
            }
        }
        Element.hide('loading-mask');
        $('barcode_search').value = '';
    }
}

var packagebarcodeCtrl = new packagebarcodeController();