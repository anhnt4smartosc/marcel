<?php

class SM_RMA_Block_Adminhtml_Request_Edit_Tab_Item_Value extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        if (!$row->getDone()) {
            /** @var Mage_Sales_Model_Order_Creditmemo $creditmemo */
            $creditmemo = Mage::registry('creditmemo_data');
            $item = $creditmemo->getItemByOrderId(intval($row->getItemId()));

            $amount = $item->getRowTotal() + $item->getTaxAmount() + $item->getHiddenTaxAmount() + $item->getWeeeTaxAppliedRowAmount() - $item->getDiscountAmount();

            $handling_fee = Mage::getStoreConfig("barcode/rma/handling_fee_default")*$row->getQtyToReturn();


            $html = "<div id='processing_" . $row->getItemId() . "' style='display:block;'>n/a</div>
                    <div id='refund_" . $row->getItemId() . "' style='display:none;'>
                       Item Price <input class='price_sm' type='text' id='item-price[" . $row->getItemId() . "]' value='{$amount}' disabled class='input-text' /><br/>
                       Handling fee <input class='handling_sm' type='text' name='request_value[" . $row->getItemId() . "][handling_fee]' class='input-text' value='".$handling_fee."' onkeyup='calcRefund(event,$(\"item-price[" . $row->getItemId() . "]\"),this,$(\"request_value[" . $row->getItemId() . "][1]\"));' /><br/>
                       Refund Amount: <input class='refund_sm' type='text' onblur='validateAmt(this);' id='request_value[" . $row->getItemId() . "][1]' name='request_value[" . $row->getItemId() . "][1]' value='' class='input-text' />
                    </div>
                    <div id='gift_" . $row->getItemId() . "' style='display:none;'>Amount: <input type='text' name='request_value[" . $row->getItemId() . "][2]' value='{$amount}' class='input-text' style='width: 50px;' /></div>
                    ";//<div id='sizes_" . $row->getItemId() . "' style='display:none;'><select name='request_value[" . $row->getItemId() . "][3]'>" . implode('', $options) . "</select></div>";

            $script = "<script type='text/javascript'>
                        document.observe('dom:loaded', function() {
                          // initially hide all containers for tab content
                          var index=0;
                                $$('.price_sm').each(function(){
                                    var price_sm = $$('.price_sm')[index].value;
                                     var handing_fee_sm  =  $$('.handling_sm')[index].value;
                                     if(price_sm!=0)
                                     $$('.refund_sm')[index].value = price_sm - handing_fee_sm;
                                     else $$('.refund_sm')[index].value =0;
                                     index++;
                                });
                          //   var price_sm = $$('.price_sm')[0].value;
                          //  var handing_fee_sm  =  $$('.handling_sm')[0].value;
                         //   $$('.refund_sm')[0].value=price_sm-handing_fee_sm;
                        });

                      function chooseRequestValue(obj, id){
                        if(obj.value*1==1){
                            $('processing_'+id).hide();
//                            $('sizes_'+id).hide();
                            $('refund_'+id).show();
                            $('gift_'+id).hide();
                            $('exchange-items').hide();
                        }
                        else if(obj.value*1==2){
                            $('processing_'+id).hide();
//                            $('sizes_'+id).hide();
                            $('refund_'+id).hide();
                            $('gift_'+id).show();
                            $('exchange-items').hide();
                        }
                        else if(obj.value*1==3){
//                            $('processing_'+id).hide();
//                            $('sizes_'+id).show();
//                            $('refund_'+id).hide();
//                            $('gift_'+id).hide();
                            $('processing_'+id).hide();
//                            $('sizes_'+id).hide();
                            $('refund_'+id).show();
                            $('gift_'+id).hide();
                            $('exchange-items').show();
                        }
                        else{
                            $('processing_'+id).show();
//                            $('sizes_'+id).hide();
                            $('refund_'+id).hide();
                            $('gift_'+id).hide();
                            $('exchange-items').hide();
                        }
                    }
                function parsef(val){
                    return isNaN(parseFloat(val))?0:parseFloat(val);
                }

                function calcRefund(event,price,fee,amt){
                    if(parsef(price.value)!=0){
                         amt.value = parsef(price.value) - parsef(fee.value);
                        validateAmt(amt);
                    }
                    else amt.val=0;
                }
                function validateAmt(e){
                    e.value = parsef(e.value).toFixed(2);
                }
                </script>
                ";
                return $html . $script;
                } else {
                    return Mage::helper('core')->currency($row->getAmount(),true,false);
                }
    }

}

?>