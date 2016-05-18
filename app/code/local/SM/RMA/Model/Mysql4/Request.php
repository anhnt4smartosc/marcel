<?php
class SM_RMA_Model_Mysql4_Request extends Mage_Core_Model_Mysql4_Abstract{
   	protected function _construct(){
		$this->_init('rma/request', 'id');
	}
} 
