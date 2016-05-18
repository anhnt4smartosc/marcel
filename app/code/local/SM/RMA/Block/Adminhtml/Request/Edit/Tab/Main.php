<?php

class SM_RMA_Block_Adminhtml_Request_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form{
	protected function _prepareForm(){
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('request_details', array('legend'=>Mage::helper('rma')->__('Request Information')));
		
		$fieldset->addField('id', 'hidden', array(
		  'required'  => false,
		  'name'      => 'id'
		));
        
        $fieldset->addField('rma_id', 'label', array(
			'label'		=> Mage::helper('rma')->__('RMA #'),
			'name'		=> 'rma_id',
		));
        
        $fieldset->addField('order_increment_id', 'label', array(
			'label'		=> Mage::helper('rma')->__('Order #'),
			'name'		=> 'order_increment_id',
		));
        
		$fieldset->addField('customer_name', 'label', array(
			'label'		=> Mage::helper('rma')->__('Customer Name'),
			'name'		=> 'customer_name',
			//'style'		=> 'width:200px;font-weight:bold;',
			//'required'	=> true,
			//'after_element_html' => '<button class="scalable" id="assign_button" type="button"><span>'.$this->__('Find customer').'</span></button>'
		));
        
        $fieldset->addField('package_opened', 'label', array(
			'label'		=> Mage::helper('rma')->__('Package Opened'),
			'name'		=> 'package_opened',
		));
		
        $fieldset->addField('request_type', 'label', array(
			'label'		=> Mage::helper('rma')->__('Request Type'),
			'name'		=> 'request_type',
		));
        
        $request_id = $this->getRequest()->getParam('id');
        $request = Mage::getSingleton('rma/request')->load($request_id);
        $form->setValues(array_merge(
                            $request->getData(),
                            array(  'id'=>$request_id,
                                    'rma_id'=>$request_id,
                                    'package_opened'=>$request->getPackageStatus($request->getPackageOpened()),
                                    'request_type'=>$request->getTypeLabel($request->getRequestType()),                                
                                 )
                            )
                        );
        
		return parent::_prepareForm();
	}
}
