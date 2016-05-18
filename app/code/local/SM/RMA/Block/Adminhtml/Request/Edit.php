<?php

class SM_RMA_Block_Adminhtml_Request_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'rma';
        $this->_controller = 'adminhtml_request';
        $this->_updateButton('save', 'label', Mage::helper('rma')->__('Update Request'));               
        $this->_removeButton('delete');
        
        $this->_formScripts[] = "
				function requestSubmitComment(){
                    var url = '".Mage::helper('adminhtml')->getUrl('adminhtml/rma_ajax/submitComment/')."';
                    new Ajax.Request(url, {
                        method: 'post',
                        parameters: $('edit_form').serialize(true),
                        onComplete: function(transport) {
                            var data = transport.responseText.evalJSON();
                            if(!data.error){
                                var new_comment = '<li class=\"helpdesk-message\"><h5>'+data.customer_name+'<span class=\"separator\">|</span>'+data.created_time+'</h5><small style=\"display:block;\">'+data.content+'</small></li>';
                                if($('no-comments') == undefined){
                                    $('comments').innerHTML = new_comment + $('comments').innerHTML;
                                }
                                else{
                                    $('comments').innerHTML = new_comment;
                                }
                                
                                $('comment').value = '';
                            }
                            
                        }
                    });
                }
                ";
    }

    public function getHeaderText()
    {
        if($request_id = $this->getRequest()->getParam('id')) {
            $request = Mage::getModel('rma/request')->load($request_id);
            return Mage::helper('rma')->__("RMA #%s - %s", $this->htmlEscape($request->getId()), $request->getStatusLabel($request->getStatus()));
        } else {
            return Mage::helper('rma')->__('RMA Information');
        }
    }

}
