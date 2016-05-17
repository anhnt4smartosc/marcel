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

function getRowDetails(el, url, order_id, tag) {
    removeAllElementByClass($$('.append-row'));
    if (el.className == 'show_hide_plus') {
        removeAllMinusElementByClass($$('.show_hide_minus'));
        el.removeClassName('show_hide_plus');
        el.addClassName('show_hide_minus');
        ffmSwitchIcon(el, 0);
        if (el.up().up().next() && el.up().up().next(0).hasClassName('append-row')) {
            el.up().up().next().show();
            return;
        }
        var parameters = {
            order_id: order_id
        };
        var request = new Ajax.Request(url,
                {
                    method: 'post',
                    parameters: parameters,
                    onSuccess: function (transport) {
                        if (transport.responseText) {
                            var result = JSON.parse(transport.responseText);
                            $$('.' + tag + '-row-' + order_id).first().insert({after: '<tr class="append-row"><td colspan="10">' + result.return_html + '</td></tr>'});
                        }
                    }
                }
        );
    } else {
        el.removeClassName('show_hide_minus');
        el.addClassName('show_hide_plus');
        ffmSwitchIcon(el, 1);
    }
}

function removeAllElementByClass(elClass) {
    elClass.each(function (item, index) {
        item.hide();
    });
}

function removeAllMinusElementByClass(elClass) {
    elClass.each(function (item, index) {
        item.removeClassName('show_hide_minus');
        item.addClassName('show_hide_plus');
        ffmSwitchIcon(item, 1);
    });
}

function ffmSwitchIcon(element, status) {
    var newClass = 'fa-plus-square-o';
    var curClass = 'fa-minus-square-o';
    if (status == 0) {
        newClass = 'fa-minus-square-o';
        curClass = 'fa-plus-square-o';
    }
    element.select('i.fa').each(function (item, index) {
        item.removeClassName(curClass);
        item.addClassName(newClass);
    });
}


