<?php

class SM_RMA_Block_Adminhtml_Request_Edit_Tab_Sub extends Mage_Adminhtml_Block_Widget_Form{
	protected function _prepareForm(){
		$form = new Varien_Data_Form();
		$this->setForm($form);
        
        /*$fieldset = $form->addFieldset('request_options', array('legend'=>Mage::helper('rma')->__('Request Options')));
        
        $fieldset->addField('status', 'select', array(
			'label'		=> Mage::helper('rma')->__('Set Status To'),
			'name'		=> 'status',
            'options'   => Mage::getModel('rma/request')->getAllStatuses(),
		));
        
        $fieldset->addField('request_type', 'select', array(
			'label'		=> Mage::helper('rma')->__('Request Type'),
			'name'		=> 'request_type',
            'options'   => array(1 => 'Exchange', 2 => 'Refund'),
		));*/
        
        $fieldset = $form->addFieldset('request_add_comment', array('legend'=>Mage::helper('rma')->__('Add Comment')));
        
        $fieldset->addField('comment', 'textarea', array(
			'label'		=> Mage::helper('rma')->__('Comment'),
			'name'		=> 'comment',
            'style'     => 'width: 100%;height:100px;',
            'after_element_html' => '<button class="scalable" id="request-add-comment" type="button" onclick="requestSubmitComment();"><span>'.$this->__('Submit Comment').'</span></button>',
		));
        
        $request_id = $this->getRequest()->getParam('id');
        $request = Mage::getSingleton('rma/request')->load($request_id);
        $form->setValues(array_merge(
                            $request->getData(),
                            array(  'id'=>$request_id,
                                    'rma_id'=>$request_id,
                                    'package_opened'=>$request->getPackageOpened()?'Yes':'No',
                                )
                            )
                        );
        
		return parent::_prepareForm();
	}
}
