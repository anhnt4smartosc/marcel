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
Webcam.on('error', function (err) {
    console.log('No webcam found');
    $$('button.package_pic').hide();
});

var imageHandleController = new Class.create();
imageHandleController.prototype = {
    initialize: function () {

    },
    takePackagePicture: function (url, orderId) {
        Element.show('loading-mask');
        Webcam.snap(function (dataUri) {
            var parameters = {dataUri: dataUri, orderId: orderId};
            var options = {
                method: 'post',
                parameters: parameters,
                evalScripts: true,
                onFailure: '',
                onSuccess: function (transport) {
                    Element.hide('loading-mask');
                }
            };
            var request = new Ajax.Updater('packing-container', url, options);
        });
    },
    takeProductsPicture: function (url, orderId) {
        Element.show('loading-mask');
        Webcam.snap(function (dataUri) {
            var parameters = {dataUri: dataUri, orderId: orderId};
            var options = {
                method: 'post',
                parameters: parameters,
                evalScripts: true,
                onFailure: '',
                onSuccess: function (transport) {
                    Element.hide('loading-mask');
                }
            };
            var request = new Ajax.Updater('packing-container', url, options);
        });
    },
    deletePackagePicture: function (url, orderId) {
        Element.show('loading-mask');
        var parameters = {orderId: orderId};
        var options = {
            method: 'post',
            parameters: parameters,
            evalScripts: true,
            onFailure: '',
            onSuccess: function (transport) {
                Element.hide('loading-mask');
            }
        };
        var request = new Ajax.Updater('packing-container', url, options);
    },
    deleteProductsPicture: function (url, orderId, imageWebPath) {
        Element.show('loading-mask');
        var parameters = {orderId: orderId, imageWebPath: imageWebPath};
        var options = {
            method: 'post',
            parameters: parameters,
            evalScripts: true,
            onFailure: '',
            onSuccess: function (transport) {
                Element.hide('loading-mask');
            }
        };
        var request = new Ajax.Updater('packing-container', url, options);
    }
}

var imageHandle = new imageHandleController();