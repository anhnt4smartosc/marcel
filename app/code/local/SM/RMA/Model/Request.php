<?php
class SM_RMA_Model_Request extends Mage_Core_Model_Abstract
{
    /*
     * Request statuses
     */
    const STATUS_PENDING_APPROVAL               = 'pending_approval';
    const STATUS_APPROVED                       = 'approved';
    const STATUS_REJECTED                       = 'resolved_rejected';
    const STATUS_RESOLVED_REFUND                = 'resolved_refund';
    const STATUS_RESOLVED_EXCHANGE_REFUND       = 'resolved_exchange_refund';
    
    protected function _construct(){
        $this->_init('rma/request');
    }
    
    public function getStatusLabel($key){
        $statuses = array(
                        self::STATUS_PENDING_APPROVAL => 'Pending Approval',
                        self::STATUS_APPROVED => 'Approved',
                        self::STATUS_REJECTED => 'Rejected',
                        self::STATUS_RESOLVED_REFUND => 'Resolved (Refund)',
                        self::STATUS_RESOLVED_EXCHANGE_REFUND => 'Resolved (Exchange + Refund)',
                    );
        return isset($statuses[$key])?$statuses[$key]:$key;
    }
    
    public function getAllStatuses(){
        $statuses = array(
                        self::STATUS_PENDING_APPROVAL => 'Pending Approval',
                        self::STATUS_APPROVED => 'Approved',
                        self::STATUS_REJECTED => 'Rejected',
                        self::STATUS_RESOLVED_REFUND => 'Resolved (Refund)',
                        self::STATUS_RESOLVED_EXCHANGE_REFUND => 'Resolved (Exchange + Refund)',
                    );
                    
        return $statuses;
    }
    
    public function getAllItems(){
        $collection = Mage::getModel('rma/item')->getCollection()->addFieldToFilter('rma_id', $this->getId());
        
        return $collection;
    }
    
    public function getTypeLabel($key){
        $types = array(
                    1   => 'Exchange',
                    2   => 'Refund',
                );
        return isset($types[$key])?$types[$key]:'Unknown';
    }
    
    public function getPackageStatus($status){
        return $status?'Yes':'No';
    }
    
    public function getAllComments(){
        $collection = Mage::getModel('rma/comment')->getCollection()
                        ->addFieldToFilter('rma_id', $this->getId())
                        ->orderBy('created_time DESC')
                        ;
        
        return $collection;
    }

    public function getRequestItemTypeName($type_id){

        switch(intval($type_id))
        {
            case 0:
                $type_name = "Rejected";
                break;
            case 1:
                $type_name = "Refund";
                break;
            case 3:
                $type_name = "Refund + Exchange";
                break;
            default:
                $type_name = "n/a";
                break;
        }

        return $type_name;
    }

    public function getRequestItemUpdateStockText($update_stock){

        switch(intval($update_stock))
        {
            case 0:
                $update_stock_text = "No";
                break;
            case 1:
                $update_stock_text = "Yes";
                break;
            default:
                $update_stock_text = $update_stock;
                break;
        }

        return $update_stock_text;
    }


}