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
 * @package     Magestore_Inventorybarcode
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

var dropshipController = new Class.create();

dropshipController.prototype = {
    initialize: function () {

    },
    updateItemComment: function (itemId, url) {
        var comment = $('shipment_item_' + itemId).value;
        if (comment.length === 0 || !comment.trim()) {
            return 0;
        }
        Element.show('loading-mask');
        var parameters = {item_id: itemId, item_comment: comment};
        var request = new Ajax.Request(url, {
            method: 'post',
            parameters: parameters,
            onFailure: '',
            onComplete: function (transport) {
                if (transport.status == 200) {
                    var result = transport.responseText.evalJSON();
                    if (result.status == 1) {
                        $('dropship-success_' + itemId).show();
                        setTimeout(function () {
                            $('dropship-success_' + itemId).hide();
                        }, 2000);
                    } else {
                        $('dropship-fail_' + itemId).show();
                        setTimeout(function () {
                            $('dropship-fail_' + itemId).hide();
                        }, 2000);
                    }
                    Element.hide('loading-mask');
                }
            }
        });
    },
    updateTrackingInfo: function (dropshipId, url) {
        var title = $('tracking-information-title-' + dropshipId).value;
        var number = $('tracking-information-number-' + dropshipId).value;
        if (title.length === 0 || !title.trim() || number.length === 0 || !number.trim()) {
            return 0;
        }

        // Remove any occurrence of character ';'
        title.replace(/;/g , ".");
        number.replace(/;/g , ".");

        // Create tracking info from title and number
        var trackingInfo = title + ';' + number;
        //Element.show('loading-mask');
        var parameters = {dropship_id: dropshipId, tracking_info: trackingInfo};
        var request = new Ajax.Request(url, {
            method: 'post',
            parameters: parameters,
            onFailure: '',
            onComplete: function (transport) {
                if (transport.status == 200) {
                    var result = transport.responseText.evalJSON();
                    if (result.status == 1) {
                        $('viewdropship-success_' + dropshipId).show();
                        setTimeout(function () {
                            $('viewdropship-success_' + dropshipId).hide();
                        }, 3000);
                    } else {
                        $('viewdropship-fail_' + dropshipId).show();
                        setTimeout(function () {
                            $('viewdropship-fail_' + dropshipId).hide();
                        }, 3000);
                    }
                    //Element.hide('loading-mask');
                }
            }
        });
    }
}