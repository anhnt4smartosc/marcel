<?php

class SM_RMA_Block_Adminhtml_Request_Edit_Tab_Status extends Mage_Adminhtml_Block_Widget_Form{
	protected function _prepareForm(){
		$form = new Varien_Data_Form();
		$this->setForm($form);

        $request_id = $this->getRequest()->getParam('id');
        $request = Mage::getSingleton('rma/request')->load($request_id);
        
        $fieldset = $form->addFieldset('request_options', array('legend'=>Mage::helper('rma')->__('Request Options')));


        $disableStatusField = false;
        if ($request->getStatus() == 'approved' || false) {
            $disableStatusField = true;
        }

        $status = $fieldset->addField('status', 'select', array(
			'label'		=> Mage::helper('rma')->__('Set Status To'),
			'name'		=> 'status',
            'options'   => Mage::getModel('rma/request')->getAllStatuses(),
            'disabled' => $disableStatusField,
        ));

        $canRefundOnline =  ($this->getCreditmemo()->getInvoice() && $this->getCreditmemo()->getInvoice()->getTransactionId());
        if ($canRefundOnline) {
            $isRefundOffline = $fieldset->addField('do_offline', 'checkbox', array(
                'label'     => Mage::helper('rma')->__('Refund Offline'),
                'checked' => true,
                'name'      => 'do_offline'
            ));
        }

        $fieldset->addField('set_status_only', 'checkbox', array(
				//'required'  => true,
				'label'     => Mage::helper('rma')->__('Set Status Only'),
				'name'      => 'set_status_only'
			));
        

        $form->setValues(array_merge(
                            $request->getData(),
                            array(  'id'=>$request_id,
                                    'rma_id'=>$request_id,
                                    'package_opened'=>$request->getPackageOpened()?'Yes':'No',
                                )
                            )
                        );

        if ($canRefundOnline) {
            $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                ->addFieldMap($status->getHtmlId(), $status->getName())
                ->addFieldMap($isRefundOffline->getHtmlId(), $isRefundOffline->getName())
                ->addFieldDependence(
                    $isRefundOffline->getName(),
                    $status->getName(),
                    'resolved_refund'
                )
            );
        }

		return parent::_prepareForm();
	}

    protected function getCreditmemo()
    {
        return Mage::registry('creditmemo_data');
    }
}
