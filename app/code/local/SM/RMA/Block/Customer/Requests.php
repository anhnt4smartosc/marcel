<?php
class SM_RMA_Block_Customer_Requests extends Mage_Core_Block_Template{ 
    protected $_collection;
    
    protected function _construct(){
		$this->_collection = Mage::getModel('rma/request')->getCollection();
		
		$this->_collection
			->addCustomerFilter(Mage::getSingleton('customer/session')->getCustomer()->getId());
        
        $orders = Mage::getResourceModel('sales/order_collection')
                ->addFieldToSelect('*')
                ->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
                ->addFieldToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()))
                ->setOrder('created_at', 'desc')
        ;

        $this->setOrders($orders);
	}
    
    protected function _getCollection(){
		return $this->_collection;
	}

	public function getCollection(){ 
		return $this->_getCollection();			
	}
    
    protected function _beforeToHtml(){
		$this->_getCollection()
			->orderBy('created_time DESC')
			->load()
		;
		return parent::_beforeToHtml();
	}
    
    public function count(){
		return $this->_collection->getSize();
	}
    
    public function getRequestLink($id = null){
		$args = is_null($id) ? array() : array('id'=>$id);
		return Mage::getUrl('rma/customer/view/', $args);
	}
    
    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }
}

