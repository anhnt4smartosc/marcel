<?php

class SM_RMA_Block_Adminhtml_Request_Edit_Tab_Comments extends Mage_Adminhtml_Block_Template
{
	
  public function __construct(){
      parent::__construct();
      $this->setId('rmaCommentsGrid');
	  $this->setTemplate('sm/rma/edit/comments.phtml');
	  $this->_label = Mage::helper('rma')->__("Comments");
	  $this->_headLabel = Mage::helper('rma')->__("Request Information");
  } 
  
  protected function DTFormat($dt){
		return $this->formatDate($dt, 'medium', true);
	}
}
