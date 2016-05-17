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

var fulfillmentControl = Class.create();
fulfillmentControl.prototype = {
    initialize: function (grid, pickUrl, packUrl, shipUrl, shipPackagesUrl, printOrderListUrl, checkGlobalStockUrl, refreshWarehouseListUrl, cancelUrl) {
        this.grid = grid;
        this.pickUrl = pickUrl;
        this.packUrl = packUrl;
        this.shipUrl = shipUrl;
        this.shipPackagesUrl = shipPackagesUrl;
        this.printOrderListUrl = printOrderListUrl;
        this.checkGlobalStockUrl = checkGlobalStockUrl;
        this.refreshWarehouseListUrl = refreshWarehouseListUrl;
        this.cancelUrl = cancelUrl;
    },
    initMessages: function (orderErrorMessage, uncheckItemMessage, shipOrderConfirmMessage, checkGlobalStockErrorMessage, refreshWarehouseListErrorMessage, confirmOrderMessage) {
        this.orderErrorMessage = orderErrorMessage;
        this.uncheckItemMessage = uncheckItemMessage;
        this.shipOrderConfirmMessage = shipOrderConfirmMessage;
        this.checkGlobalStockErrorMessage = checkGlobalStockErrorMessage;
        this.refreshWarehouseListErrorMessage = refreshWarehouseListErrorMessage;
        this.confirmOrderMessage = confirmOrderMessage;
    },
    cancelaction: function (element, orderId) {
        if (!confirm(this.confirmOrderMessage)) {
            return;
        }
        this.step = 'verify';
        this.actionUrl = this.cancelUrl;
        this.prepareParams(element); 
        var isNotifyEmail = this.checkNotifyEmail(orderId);
        this.nextStep(element, orderId, isNotifyEmail);        
    },
    verifypickaction: function (element, orderId) {
        if (!confirm(this.confirmOrderMessage)) {
            return;
        }
        this.step = 'verify';
        this.actionUrl = this.pickUrl;
        this.prepareParams(element);
        var isNotifyEmail = this.checkNotifyEmail(orderId);
        this.nextStep(element, orderId, isNotifyEmail);
    },
    packaction: function (element, orderId) {
        if (!confirm(this.confirmOrderMessage)) {
            return;
        }
        this.step = 'pick';
        this.actionUrl = this.packUrl;
        this.prepareParams(element);
        var isNotifyEmail = this.checkNotifyEmail(orderId);
        this.nextStep(element, orderId, isNotifyEmail);
    },
    shipaction: function (element, orderId) {
        if (!confirm(this.confirmOrderMessage)) {
            return;
        }
        this.step = 'pack';
        this.actionUrl = this.shipUrl;
        var isNotifyEmail = this.checkNotifyEmail(orderId);
        this.nextStep(element, orderId, isNotifyEmail);
    },
    checkNotifyEmail: function (orderId) {
        if ($('email_notify_' + orderId) && $('email_notify_' + orderId).checked) {
            return 1;
        }
        return 0;
    },
    prepareParams: function (element) {
        var selectedwarehouses = '';
        if (element.up('.append-row')) {
            var warehouseSelectBoxes = element.up('.append-row').select("select.warehouse-shipment");
            for (var i = 0; i < warehouseSelectBoxes.length; i++) {
                if (warehouseSelectBoxes[i].getValue() > 0) {
                    selectedwarehouses += warehouseSelectBoxes[i].name + '=' + warehouseSelectBoxes[i].getValue() + '&';
                }
            }
        }
        var parameters = {selectedwarehouses: selectedwarehouses};
        /* add more params for veritying */
        if (this.step == 'verify') {
            selectedwarehouses = '';
            if (element.up('.append-row')) {
                var pickQtyEls = element.up('.append-row').select('.pick-qty');
                var remainQty = 0;
                for (i = 0; i < pickQtyEls.length; i++) {
                    var pickqty = parseFloat(pickQtyEls[i].value);
                    var whSelectEl = pickQtyEls[i].up('tr').down('.warehouse-shipment');
                    var warehouseId = whSelectEl.value;
                    var itemId = whSelectEl.name;
                    selectedwarehouses += itemId + '=' + warehouseId + '-' + pickqty + '&';
                    var shipQty = parseFloat(pickQtyEls[i].next('.need-to-ship-qty').innerHTML);
                    remainQty += shipQty - pickqty;
                }
            }
            parameters = {selectedwarehouses: selectedwarehouses, remainqty : remainQty};
        }
        /* add more params for picking */
        if (this.step == 'pick') {
            selectedwarehouses = '';
            if (element.up('.append-row')) {
                var pickQtyEls = element.up('.append-row').select('.pick-qty');
                for (i = 0; i < pickQtyEls.length; i++) {
                    var pickqty = pickQtyEls[i].value;
                    //var whSelectEl = pickQtyEls[i].up('tr').down('.warehouse-shipment');
                    //var warehouseId = whSelectEl.value;
                    //var itemId = whSelectEl.name;
                    selectedwarehouses += pickQtyEls[i].id + '=' + pickqty + '&';
                }
            }
            parameters = {selectedwarehouses: selectedwarehouses};
        }
        /* add more params for packing */
        if (this.step == 'pack') {
            selectedwarehouses = '';
            if (element.up('.append-row')) {
                var pickQtyEls = element.up('.append-row').select('.pick-qty');
                for (i = 0; i < pickQtyEls.length; i++) {
                    var pickqty = pickQtyEls[i].value;
                    selectedwarehouses += pickQtyEls[i].id + '=' + pickqty + '&';
                }
            }
            alert(selectedwarehouses);
            return;
            parameters = {selectedwarehouses: selectedwarehouses};
        }        
        this.parameters = parameters;
    },
    nextStep: function (element, orderId, isNotifyEmail) {
        if (!this.checkOrderItems(this.step, element)) {
            alert(this.uncheckItemMessage);
            return;
        }
        this.curElement = element;
        if (element.up('div.grid')) {
            this.grid = eval(element.up('div.grid').up().id + 'JsObject');
        }
        var url = this.actionUrl + 'order_id/' + orderId + '/is_notify_email/' + isNotifyEmail;
        this.currOrderId = orderId;
        new Ajax.Request(url, {
            method: 'get',
            parameters: this.parameters,
            onComplete: function (transport) {
                if (transport.responseText.isJSON()) {
                    var response = transport.responseText.evalJSON();
                    if (response.error) {
                        alert(response.message);
                        return;
                    }
                    /* success */
                    this.curElement.up('.append-row').hide();
                    if (this.grid) {
                        this.grid.doFilter();
                    }
                    alert(response.message);
                    if(response.reload && this.step == 'verify'){
                        this.reloadVerifyOrder(this.currOrderId);
                    }
                } else {
                    alert(this.orderErrorMessage);
                }
            }.bind(this)
        });
    },
    reloadVerifyOrder: function (orderId) {
        var rowItem =  $('order_verifying_grid_table').select('.verifying-row-'+ orderId)[0];
        if(rowItem) {
            var viewButton = rowItem.select('span.show_hide_plus')[0];
            if(viewButton){
                viewButton.click();
            }
            Element.scrollTo(viewButton);
        }
    },
    checkOrderItems: function (checkboxClass, element) {
        var checked = true;
        checkboxClass = '.required-' + checkboxClass + '-item';
        if (element.up('.append-row')) {
            var checkboxes = element.up('.append-row').select(checkboxClass);
            if (checkboxes.length > 0) {
                for (var i = 0; i < checkboxes.length; i++) {
                    if (checkboxes[i].checked == false) {
                        checked = false;
                        checkboxes[i].addClassName('unchecked');
                        checkboxes[i].up().addClassName('unchecked');
                    }
                }
            }
        }
        return checked;
    },
    shipOrders: function (element, group) {
        if (!confirm(this.shipOrderConfirmMessage)) {
            return;
        }
        var selectedPackageIds = this.getSelectedPackageIds(element);
        if (selectedPackageIds.length <= 0) {
            alert('No order selected');
        } else {
            var parameters = {package_ids: selectedPackageIds, group_name: group};
            this.curElement = element;
            this.curPackageGroup = group;
            if (element.up(".order-group")) {
                this.grid = eval(element.up(".order-group").down().id + 'JsObject');
            }
            new Ajax.Request(this.shipPackagesUrl, {
                method: 'get',
                parameters: parameters,
                onComplete: function (transport) {
                    if (transport.responseText.isJSON()) {
                        var response = transport.responseText.evalJSON();
                        if (response.error) {
                            alert(response.message);
                            return;
                        }
                        /* success */
                        //alert(response.message);

                        this.updateTotalPackage(response.total_packages);

                        if (this.grid) {
                            this.grid.doFilter();
                        }
                    } else {
                        alert(this.orderErrorMessage);
                    }
                }.bind(this)
            });
        }
    },
    printListShipment: function (element, group) {
        var selectedPackageIds = this.getSelectedPackageIds(element);
        if(selectedPackageIds == '') {
            alert('No package selected');
            return;
        }        
        var url = this.printOrderListUrl + 'package_ids/' + selectedPackageIds;
        location.href = url;
    },
    getSelectedPackageIds: function (element) {
        var selectedPackageIds = '';
        var selectedItems = element.up(".order-group").select("input.ship-order");
        for (var i = 0; i < selectedItems.length; i++) {
            if (selectedItems[i].checked == true) {
                selectedPackageIds += selectedItems[i].value + ',';
            }
        }
        return selectedPackageIds;
    },
    updateTotalPackage: function (total) {
        if (this.curElement) {
            if (this.curElement.up(".order-group").select('span.total_package'))
                this.curElement.up(".order-group").select('span.total_package')[0].innerHTML = total;
        }
    },
    toggleButtonShowComments: function () {
        if (document.getElementById('show_comments_link').innerHTML == 'Show comments <i class="fa fa-level-down"></i>') {
            document.getElementById('show_comments_link').innerHTML = 'Hide comments <i class="fa fa-level-up"></i>';
        } else {
            document.getElementById('show_comments_link').innerHTML = 'Show comments <i class="fa fa-level-down"></i>';
        }
    },
    submitVerifyOrder: function (element, orderId, url) {
        var orderHistoryBlock = 'order_history_block_' + orderId;
        this.orderHistoryBlock = orderHistoryBlock;
        var comment = $(orderHistoryBlock).down('textarea').value;
        var selectedmethod = null;
        var shippingMethods = element.up('.fulfillment-order-view').select('.order_shipping_method');
        for (var i = 0; i < shippingMethods.length; i++) {
            if (shippingMethods[i].checked == true)
                selectedmethod = shippingMethods[i].value;
        }
        var parameters = {comment: comment, selectedmethod: selectedmethod};
        new Ajax.Request(url, {
            method: 'get',
            parameters: parameters,
            onComplete: function (transport) {
                if (transport.responseText.isJSON()) {
                    var response = transport.responseText.evalJSON();
                    if (response.error) {
                        alert(response.message);
                        return;
                    }
                    /* success */
                    alert(response.message);
                    $(this.orderHistoryBlock).innerHTML = response.orderHistory;
                } else {
                    alert(this.orderErrorMessage);
                }
            }.bind(this)
        });
    },
    checkGlobalStock: function (element, orderId) {
        var parameters = {order_id: orderId};
        this.curElement = element;
        new Ajax.Request(this.checkGlobalStockUrl, {
            method: 'get',
            parameters: parameters,
            onComplete: function (transport) {
                if (transport.responseText.isJSON()) {
                    var response = transport.responseText.evalJSON();
                    if (response.error) {
                        alert(response.message);
                        return;
                    }
                    /* success */
                    if (response.items) {
                        var items = response.items;
                        items = Object.keys(items).map(function (key) {
                            return items[key]
                        });
                        var isUpdateItemCheck = false;
                        if (this.curElement.up('.append-row').up('div.grid').up()) {
                            if (this.curElement.up('.append-row').up('div.grid').up().id == 'order_verifying_grid')
                                isUpdateItemCheck = true;
                        }
                        for (var i = 0; i < items.length; i++) {
                            var notifyElement = this.curElement.up('.order-tables').down('.item-stock-status-' + items[i].id);
                            if (!notifyElement)
                                continue;
                            if (items[i].is_available == true) {
                                notifyElement.innerHTML = items[i].message;
                                notifyElement.removeClassName('outstock');
                                notifyElement.addClassName('instock');
                                if (isUpdateItemCheck)
                                    notifyElement.up('td').next('td').down('input').checked = true;
                            } else {
                                notifyElement.innerHTML = items[i].message;
                                notifyElement.addClassName('outstock');
                                notifyElement.removeClassName('instock');
                                if (isUpdateItemCheck)
                                    notifyElement.up('td').next('td').down('input').checked = false;
                            }
                            notifyElement.up('td').next('td').removeClassName('unchecked');
                        }
                    }
                } else {
                    alert(this.checkGlobalStockErrorMessage);
                }
            }.bind(this)
        });
    },
    refreshWarehouseList: function (element, orderId) {
        var parameters = {order_id: orderId};
        this.curElement = element;
        new Ajax.Request(this.refreshWarehouseListUrl, {
            method: 'get',
            parameters: parameters,
            onComplete: function (transport) {
                if (transport.responseText.isJSON()) {
                    var response = transport.responseText.evalJSON();
                    if (response.error) {
                        alert(response.message);
                        return;
                    }
                    if (response.items) {
                        var items = response.items;
                        items = Object.keys(items).map(function (key) {
                            return items[key]
                        });
                        for (var i = 0; i < items.length; i++) {
                            var warehouseListContainer = $('warehouse_shipment[' + items[i].id + ']').up();
                            warehouseListContainer.innerHTML = items[i].warehouseselect;
                            if (items[i].is_available == true) {
                                warehouseListContainer.next('td').down('input').checked = true;
                            } else {
                                warehouseListContainer.next('td').down('input').checked = false;
                            }
                            warehouseListContainer.next('td').removeClassName('unchecked');
                        }
                    }
                } else {
                    alert(this.refreshWarehouseListErrorMessage);
                }
            }.bind(this)
        });
    },
    selectWarehouseToShip: function (element) {
        var itemId = element.name;
        var pickQtyInput = $('warehouse_shipment_qty[' + itemId + ']');
        var pickCheckbox = $('item_' + itemId);
        var needToShipQty = element.up('tr').down('.need-to-ship-qty').innerHTML;
        if (element.value == '') {
            pickCheckbox.checked = false;
            pickQtyInput.value = '';
        } else {
            var pickqty = 0;
            if ($('warehouse_qty_json_' + itemId)) {
                var warehouseQtys = $('warehouse_qty_json_' + itemId).innerHTML.evalJSON();
                pickqty = Math.min(warehouseQtys[element.value], needToShipQty);
            }
            pickQtyInput.value = pickqty;
            //pickQtyInput.max = pickqty;
            if (pickqty > 0) {
                pickCheckbox.checked = true;
            } else {
                pickCheckbox.checked = false;
            }
        }
    },
    loadDefaulPickQty: function (element) {
        var whSelectEl = element.up('tr').down('.warehouse-shipment');
        if (whSelectEl != null) {
            this.selectWarehouseToShip(whSelectEl);
        }
    },
    checkPickQty: function (element) {
        if (isNaN(parseFloat(element.value)) == true) {
            element.value = element.min;
        }

        if (parseFloat(element.value) > parseFloat(element.max)) {
            element.value = element.max;
        }
        if (parseFloat(element.value) < parseFloat(element.min)) {
            element.value = element.min;
        }
        /* check pack item */
        var checkItem = element.up('tr').down('.required-pack-item');
        if(checkItem){
            if(element.value == element.max) {
                checkItem.checked = true;
            } else {
                checkItem.checked = false;
            }
        }
        
    },
    confirmPackItem: function (element) {
        var scanQtyInput = element.up('tr').down('.pick-qty');
        var packQty = parseFloat(scanQtyInput.next('span').innerHTML);
        if (element.checked == true) {
            scanQtyInput.value = packQty;
        } else {
            scanQtyInput.value = 0;
        }
    }
}