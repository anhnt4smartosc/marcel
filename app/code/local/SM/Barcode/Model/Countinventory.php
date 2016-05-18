<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ADMIN
 * Date: 6/10/13
 * Time: 4:02 PM
 * To change this template use File | Settings | File Templates.\
 *
 * Noi dung sua
 * 222
 *
 */

class SM_Barcode_Model_Countinventory extends Mage_Core_Model_Abstract{

    /* VARIABLES DECLARATIONS
    ========================= */
    private $resource       = NULL;
    private $readConnection = NULL;
    private $writeConnection= NULL;
    private $tableName      = NULL;
    private $return         = array();

    /* FUNCTIONAL DECLARATION
    ========================= */
    public function _construct(){
        parent::_construct();
        $this->_init('barcode/countinventory');

        $this->resource = Mage::getSingleton('core/resource');
        $this->readConnection = $this->resource->getConnection('core_read');
        $this->writeConnection= $this->resource->getConnection('core_write');
        $this->tableName      = $this->resource->getTableName('sm_xbar_countinventory');
    }

    public function getType(){
        return $this->getData('type');
    }

    public function getCountInventData(){
        $query  = "SELECT `product_id` FROM ".$this->tableName ;
        $result = $this->readConnection->fetchAll($query);
        foreach($result as $key => $value){
            $this->return[] = $value['product_id'];
        }

        return $this->return;
    }

    public function getScannedQtyById($id){
        $query  = "SELECT  `scanned_qty` FROM ".$this->tableName . " WHERE product_id = " . $id;
        $result = $this->readConnection->fetchOne($query);
        $this->return[] = $result;

        return $this->return;
    }

    public function getProductIdsToSession(){
        $query  = "SELECT  `product_id`, `scanned_qty` FROM ".$this->tableName ;
        $result = $this->readConnection->fetchAll($query);
        foreach($result as $key => $value){
            $this->return[$value['product_id']] = $value['scanned_qty'];
        }

        $_SESSION['scanned_product_ids'] = $this->return;
    }

    /*
     @description    : Insert new data into table CountInventory
     @author         : thangnv@smartosc.com
     @date           : 6th June, 2013
     @param
        $_productId  (integer) --> Unique id of product
        $_scannedQTy (integer) --> Quantity of the scanned product
     */
    public function insertCountInventData($_productId, $_scannedQty){
        $query = "SELECT * FROM ".$this->tableName." WHERE `product_id` = $_productId";
        $result = $this->readConnection->fetchAll($query);
        if(count($result) == 0){
            $query = "INSERT INTO ".$this->tableName." (`product_id`, `scanned_qty`) VALUES (".$_productId.", ".$_scannedQty.")";
            if($this->writeConnection->query($query)){
                $this->return['msg']    = 'Information saved';
                $this->return['error']  = false;
            } else {
                $this->return['msg']    = 'Information cannot save';
                $this->return['error']  = true;
            }
        }
        else {
            $this->return['msg']    = 'Information is duplicated.';
            $this->return['error']  = true;
        }
        return $this->return;
    }


    /*
     @description    : Update table CountInventory by productID
     @author         : thangnv@smartosc.com
     @date           : 6th June, 2013
     @param
        $_productId  (integer) --> Unique id of product
        $_scannedQTy (integer) --> Quantity of the scanned product
     */
    public function updateCountInventData($_productId, $_scannedQty){
        $query  = "UPDATE ".$this->tableName." SET `scanned_qty` = $_scannedQty WHERE `product_id` = $_productId";
        if($this->writeConnection->query($query)){
            $this->return['msg']    = 'Information saved';
            $this->return['error']  = false;
        } else {
            $this->return['msg']    = 'Information cannot save';
            $this->return['error']  = true;
        }
        return $this->return;
    }

    /*
     @description    : Update table CountInventory by productID
     @author         : thangnv@smartosc.com
     @date           : 6th June, 2013
     @param
        $_productId  (integer) --> Unique id of product
        $_scannedQTy (integer) --> Quantity of the scanned product
     */
    public function deleteProductByProductId($_productId){
        $query  = "DELETE FROM ".$this->tableName." WHERE `product_id` = $_productId";
        if($this->writeConnection->query($query)){
            $this->return['msg']    = 'Information saved';
            $this->return['error']  = false;
        } else {
            $this->return['msg']    = 'Information cannot save';
            $this->return['error']  = true;
        }
        return $this->return;
    }
}