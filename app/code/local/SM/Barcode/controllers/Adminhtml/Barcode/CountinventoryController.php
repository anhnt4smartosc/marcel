<?php
/**
 * Count-Inventory controller.
 * User: thangnv@smartosc.com
 * Date: 6/3/13
 * Time: 1:19 PM
 */

class SM_Barcode_Adminhtml_Barcode_CountinventoryController extends SM_Barcode_Controller_Adminhtml_Action {

    /* VARIABLES DECLARATIONS
    ========================= */
    private $return = array();

    /* FUNCTIONAL DECLARATION
    ========================= */

    /*
     @description    : Initial grid.
     @author         : thangnv@smartosc.com
     @date           : 6th June, 2013
     @param
     */
    protected function _initAction() {
        $this->loadLayout();

        // Get Warehouse information and store in session

        $warehouse = array();

        $validateMWHenabled = Mage::getStoreConfig('xwarehouse/general/enabled');
        if($validateMWHenabled == 1){
            $collection = Mage::getModel('xwarehouse/warehouse')->getCollection();
            foreach($collection as $val){
                $warehouse[] = array(
                    'warehouseLbl' => $val->getLabel(),
                    'warehouseId' => $val->getId()
                );
            }
        }

        Mage::getSingleton('adminhtml/session')->setWarehouseCollection($warehouse);

        return $this;
    }

    /*
     @description    : IndexAction of Grid.
     @author         : thangnv@smartosc.com
     @date           : 6th June, 2013
     @param
     */
    public function indexAction() {
        $this->_initAction();
        $this->renderLayout();
    }

    /*
     @description    : Grid Action.
     @author         : thangnv@smartosc.com
     @date           : 6th June, 2013
     @param
     */
    public function gridAction() {
        $this->loadLayout(false);
        $this->renderLayout();
    }

    /*
     @description    : Update quantity of product in stock and reset scanned product quantity.
     @author         : thangnv@smartosc.com
     @date           : 6th June, 2013
     @param
     */
    public function ajaxcorrectstockAction(){
        $productId      = isset($_GET['productId']) ? $_GET['productId'] : 0;
        $currInvenQty   = isset($_GET['currInvenQty']) ? $_GET['currInvenQty']: 0;
        $qtyScannedQty  = isset($_GET['qtyScannedQty']) ? $_GET['qtyScannedQty'] : 0;

        $validateMWHenabled = Mage::getStoreConfig('xwarehouse/general/enabled');
        if($validateMWHenabled == 1){
            $warehouseId  = isset($_GET['warehouseId']) ? intval($_GET['warehouseId']) : 0;
            $warehouseLbl = isset($_GET['warehouseLbl']) ? (string)$_GET['warehouseLbl'] : null;
        }

        $productStock   = Mage::getModel('catalog/product')->load($productId);
        $validProductId = $productStock->getId();

        if(is_numeric($productId) && is_numeric($currInvenQty) && is_numeric($qtyScannedQty) && !empty($validProductId)){
            if($validateMWHenabled == 1){
                if($warehouseLbl != 'allwarehouse' || $warehouseLbl != null){
                    $collection = Mage::getModel('xwarehouse/warehouse_product')->getCollection();
                    $collection->addFieldToFilter('product_id', $productId);
                    $collection->addFieldToFilter('warehouse_id', $warehouseId);
                    $productInfo = $collection->getFirstItem();
                    $originQty   = $productInfo->getQty();

                    if($originQty != null){
                        $productInfo->setQty($qtyScannedQty);
                        $productInfo->save();

                        $productStock = $productStock->getStockItem();
                        $stockQty     = $productStock->getQty();
                        $stockQty     = ($stockQty - $originQty) + $qtyScannedQty;
                        $productStock->setQty($stockQty);
                        $productStock->save();
                    }
                    else {
                        $arrData = array(
                            'product_id'   => $productId,
                            'warehouse_id' => $warehouseId,
                            'qty'          => $qtyScannedQty,
                            'enable'       => 1
                        );
                        $productInfo->setData($arrData);
                        $productInfo->save();

                        // Update new quantity for global stock
                        $productInfo = Mage::getModel('catalog/product')->load($productId)->getStockItem();
                        $currQty     = $productInfo->getQty();
                        $currQty    += $qtyScannedQty;
                        $productInfo->setQty($currQty);
                        $productInfo->save();
                    }
                    // Reset quantity of scanned product
                    $qtyScannedQty = 0;
                    $this->return = Mage::getModel('barcode/countinventory')->updateCountInventData($productId, $qtyScannedQty);
                }
                else {
                    $this->return['msg']   = 'Correction failed';
                    $this->return['error'] = true;
                }
            }
            else {
                // Update stock quantity
                $stockData = $productStock->getStockItem();
                $stockData->setData('qty',$qtyScannedQty);
                $stockData->save();

                // Reset quantity of scanned product
                $qtyScannedQty = 0;
                $this->return = Mage::getModel('barcode/countinventory')->updateCountInventData($productId, $qtyScannedQty);
            }
        }
        else {
            $this->return['msg']   = 'Correction failed';
            $this->return['error'] = true;
        }


        $this->_outputJSON();
    }

    /*
     @description    : Update quantity of product in stock and reset scanned product quantity.
     @author         : thangnv@smartosc.com
     @date           : 6th June, 2013
     @param
     */
    public function ajaxplusstockAction(){
        $productId      = isset($_GET['productId']) ? intval($_GET['productId']) : 0;
        $currInvenQty   = isset($_GET['currInvenQty']) ? intval($_GET['currInvenQty']) : 0;
        $qtyScannedQty  = isset($_GET['qtyScannedQty']) ? intval($_GET['qtyScannedQty']) : 0;

        $validateMWHenabled = Mage::getStoreConfig('xwarehouse/general/enabled');
        if($validateMWHenabled == 1){
            $warehouseId  = isset($_GET['warehouseId']) ? intval($_GET['warehouseId']) : 0;
            $warehouseLbl = isset($_GET['warehouseLbl']) ? (string)$_GET['warehouseLbl'] : null;
        }

        $productStock   = Mage::getModel('catalog/product')->load($productId);
        $validProductId = $productStock->getId();

        if(is_numeric($productId) && is_numeric($currInvenQty) && is_numeric($qtyScannedQty) && !empty($validProductId)){
            // Update stock quantity
            if($validateMWHenabled == 1){
                if($warehouseLbl != 'allwarehouse' || $warehouseLbl != 'null'){
                    $collection = Mage::getModel('xwarehouse/warehouse_product')->getCollection();
                    $collection->addFieldToFilter('product_id', $productId);
                    $collection->addFieldToFilter('warehouse_id', $warehouseId);
                    $productInfo = $collection->getFirstItem();
                    $originQty   = $productInfo->getQty();

                    if($originQty != null){
                        $productInfo->setQty($currInvenQty);
                        $productInfo->save();

                        $productStock = $productStock->getStockItem();
                        $stockQty     = $productStock->getQty();
                        $stockQty    += $qtyScannedQty;
                        $productStock->setQty($stockQty);
                        $productStock->save();
                    }
                    else {
                        $arrData = array(
                            'product_id'   => $productId,
                            'warehouse_id' => $warehouseId,
                            'qty'          => $qtyScannedQty,
                            'enable'       => 1
                        );
                        $productInfo->setData($arrData);
                        $productInfo->save();

                        // Update new quantity for global stock
                        $productInfo = Mage::getModel('catalog/product')->load($productId)->getStockItem();
                        $currQty     = $productInfo->getQty();
                        $currQty    += $qtyScannedQty;
                        $productInfo->setQty($currQty);
                        $productInfo->save();
                    }
                    // Reset quantity of scanned product
                    $qtyScannedQty = 0;
                    $this->return = Mage::getModel('barcode/countinventory')->updateCountInventData($productId, $qtyScannedQty);
                }
                else {
                    $this->return['msg']   = 'Adding quantity of product in stock failed';
                    $this->return['error'] = true;
                }
            }
            else{
                $stockData = $productStock->getStockItem();
                $stockData->setData('qty',$currInvenQty);
                $stockData->save();

                // Reset quantity of scanned product
                $qtyScannedQty = 0;
                $this->return = Mage::getModel('barcode/countinventory')->updateCountInventData($productId, $qtyScannedQty);
            }
        }
        else {
            $this->return['msg']   = 'Adding quantity of product in stock failed';
            $this->return['error'] = true;
        }
        
        $this->_outputJSON();
    }

    /*
     @description    : Update quantity of product in stock and reset scanned product quantity.
     @author         : thangnv@smartosc.com
     @date           : 6th June, 2013
     @param
     */
    public function ajaxreducestockAction(){
        $productId      = isset($_GET['productId']) ? intval($_GET['productId']) : 0;
        $currInvenQty   = isset($_GET['currInvenQty']) ? intval($_GET['currInvenQty']) : 0;
        $qtyScannedQty  = isset($_GET['qtyScannedQty']) ? intval($_GET['qtyScannedQty']) : 0;

        $validateMWHenabled = Mage::getStoreConfig('xwarehouse/general/enabled');
        $addnewProductToWarehouseFlag = false;

        if($validateMWHenabled == 1){
            $warehouseId  = isset($_GET['warehouseId']) ? intval($_GET['warehouseId']) : 0;
            $warehouseLbl = isset($_GET['warehouseLbl']) ? (string)$_GET['warehouseLbl'] : null;
        }

        $productStock   = Mage::getModel('catalog/product')->load($productId);
        $validProductId = $productStock->getId();

        if(is_numeric($productId) && is_numeric($currInvenQty) && is_numeric($qtyScannedQty) && !empty($validProductId)){
            // Update stock quantity
            if($validateMWHenabled == 1){
                if($warehouseLbl != null){
                    $collection = Mage::getModel('xwarehouse/warehouse_product')->getCollection();
                    $collection->addFieldToFilter('product_id', $productId);
                    $collection->addFieldToFilter('warehouse_id', $warehouseId);
                    $productInfo = $collection->getFirstItem();
                    $originQty   = $productInfo->getQty();

                    if($originQty != null){
                        $productInfo->setQty($currInvenQty);
                        $productInfo->save();

                        $productStock = $productStock->getStockItem();
                        $stockQty     = $productStock->getQty();
                        $stockQty    -= $qtyScannedQty;
                        $productStock->setQty($stockQty);
                        $productStock->save();
                    }
                    else {
                        $arrData = array(
                            'product_id'   => $productId,
                            'warehouse_id' => $warehouseId,
                            'qty'          => 0,
                            'enable'       => 1
                        );
                        $productInfo->setData($arrData);
                        $productInfo->save();
                        $addnewProductToWarehouseFlag = true;
                    }
                    // Reset quantity of scanned product
                    if(!$addnewProductToWarehouseFlag){
                        $qtyScannedQty = 0;
                        $this->return = Mage::getModel('barcode/countinventory')->updateCountInventData($productId, $qtyScannedQty);
                    }
                }
                else {
                    $this->return['msg']   = 'Reducing quantity of product in stock failed';
                    $this->return['error'] = true;
                }
            }
            else {
                $stockData = $productStock->getStockItem();
                $stockData->setData('qty',$currInvenQty);
                $stockData->save();

                // Reset quantity of scanned product
                $qtyScannedQty = 0;
                $this->return = Mage::getModel('barcode/countinventory')->updateCountInventData($productId, $qtyScannedQty);
            }
        }
        else {
            $this->return['msg']   = 'Adding quantity of product in stock failed';
            $this->return['error'] = true;
        }
        $this->_outputJSON();
    }

    /*
     @description    : Delete product information in CountInventory table
     @author         : thangnv@smartosc.com
     @date           : 6th June, 2013
     @param
     */
    public function ajaxdeletestockAction(){
        $productId      = $_GET['productId'];

        if(is_numeric($productId)){
            $product        = Mage::getModel('catalog/product')->load($productId);
            $validProductId = $product->getId();
            $sku = $product->getSku();
            if(!empty($validProductId)){
                // Delete product
                $this->return = Mage::getModel('barcode/countinventory')->deleteProductByProductId($productId);
                $this->return['sku']   = $sku;
            }
            else {
                $this->return['msg']   = 'Deleting quantity of product in stock failed';
                $this->return['error'] = true;
            }
        }
        else {
            $this->return['msg']   = 'Deleting quantity of product in stock failed';
            $this->return['error'] = true;
        }
        $this->_outputJSON();
    }

    /*
     @description    : Update table CountInventory by productID
     @author         : thangnv@smartosc.com
     @date           : 6th June, 2013
     @param
        $_productId  (integer) --> Unique id of product
        $_scannedQTy (integer) --> Quantity of the scanned product
     */
    public function ajaxgetgridinfoAction(){
        $typeEx     = isset($_GET['typeEx']) ? (string)$_GET['typeEx'] : null;
        $qtyUpdate  = isset($_GET['qtyUpdate']) ? intval($_GET['qtyUpdate']) : null;
        $sm_barcode = isset($_GET['sm_barcode'])? $_GET['sm_barcode']: null;

        if (in_array(intval(Mage::getStoreConfig("barcode/general/symbology")), array(0, 7))) {
            if (strlen($sm_barcode) == 12) {
                $sm_barcode ='0'.$sm_barcode;
            }
        }
        if(!empty($typeEx)){
            switch((string)$typeEx){
                case 'addnew':
                    $collection = Mage::getModel('catalog/product')->getCollection();
                    $collection->addFieldToFilter('sm_barcode',$sm_barcode);
                    $collection->addFieldToFilter('type_id','simple');
                    $product    = $collection->getFirstItem();
                    $productId  = $product->getId();

                    $sku = $product->getData('sku');

                    // Generate barcode image
                    Mage::helper('barcode/barcode')->createProductBarcode($productId);
                    // Get barcode image URL
                    $symboCode = Mage::getStoreConfig('barcode/general/symbology');
                    $img_url   = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'barcode/'.$productId.'_'.$symboCode.'_bc.png?'.time();
                    if(!empty($productId)){
                        $this->return = Mage::getModel('barcode/countinventory')->insertCountInventData($productId,$qtyUpdate);
                        $this->return['sku']   = $sku;
                        $this->return['image_url'] = $img_url;
                    }
                    else {
                        $this->return['msg']   = 'Barcode is not correct';
                        $this->return['error'] = true;
                    }
                    break;
                case 'update':
                    $collection = Mage::getModel('catalog/product')->getCollection();
                    $collection->addFieldToFilter('sm_barcode',$sm_barcode);
                    $collection->addFieldToFilter('type_id','simple');
                    $product    = $collection->getFirstItem();
                    $productId  = $product->getId();
                    $sku = $product->getData('sku');
                    // Generate barcode image
                    Mage::helper('barcode/barcode')->createProductBarcode($productId);
                    // Get barcode image URL
                    $symboCode = Mage::getStoreConfig('barcode/general/symbology');
                    $img_url   = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'barcode/'.$productId.'_'.$symboCode.'_bc.png?'.time();
                    if(!empty($productId)){
                        $this->return = Mage::getModel('barcode/countinventory')->updateCountInventData($productId,$qtyUpdate);
                        $this->return['sku']   = $sku;
                        $this->return['image_url'] = $img_url;
                    }
                    else {
                        $this->return['msg']   = 'Barcode is not correct';
                        $this->return['error'] = true;
                    }
                    break;
                default:
                    $this->return['msg']   = 'Barcode is not correct';
                    $this->return['error'] = true;
                    break;
            }
        }
        else {
            $this->return['msg']   = 'Adding quantity of product in stock failed';
            $this->return['error'] = true;
        }

        $this->_outputJSON();
    }


    /*
     @description    : Update or delete table CountInventory by productIDs
     @author         : thangnv@smartosc.com
     @date           : 6th June, 2013
     @param
        $_productId  (integer) --> Unique id of product
        $_scannedQTy (integer) --> Quantity of the scanned product
     */
    public function ajaxmassactionexecuteAction(){
        // Get post parametters from client
        $postData = isset($_GET["postData"]) ? $_GET["postData"] : null;
        $type     = isset($_GET["type"]) ? (string)$_GET["type"] : null;
        $success_count = 0;

        $validateMWHenabled = Mage::getStoreConfig('xwarehouse/general/enabled');
        if($validateMWHenabled ==1 ){
            $warehouseLbl = isset($_GET['warehouseLbl'])? (string)$_GET['warehouseLbl'] : null;
            $warehouseId  = isset($_GET['warehouseId']) ? intval($_GET['warehouseId']) : 0;
        }

        if ($type != null){
            if($postData != null){
                // Parse postData into array to get productId
                $productIds = explode(',', $postData);
                switch ($type){
                    case 'correctall':
                        if($validateMWHenabled == 1 && $warehouseId != 0 && $warehouseId != null && $warehouseLbl != 'allwarehouse' && $warehouseLbl != null){
                            for($_i = 0; $_i < count($productIds); $_i++){
                                // Get scanned quantity
                                $scannedQty = Mage::getModel('barcode/countinventory')->getScannedQtyById(intval($productIds[$_i]));

                                // Update stock quantity
                                $collection = Mage::getModel('xwarehouse/warehouse_product')->getCollection();
                                $collection->addFieldToFilter('product_id', $productIds[$_i]);
                                $collection->addFieldToFilter('warehouse_id', $warehouseId);
                                $productInfo = $collection->getFirstItem();
                                $originQty   = $productInfo->getQty();

                                if($originQty != null){
                                    $productInfo->setQty($scannedQty[0]);
                                    $productInfo->save();

                                    $productStock = Mage::getModel('catalog/product')->load(intval($productIds[$_i]))->getStockItem();
                                    $stockQty     = $productStock->getQty();
                                    $stockQty     = ($stockQty - $originQty) + $scannedQty[0];
                                    $productStock->setQty($stockQty);
                                    $productStock->save();
                                }
                                else {
                                    $arrData = array(
                                        'product_id'   => $productIds[$_i],
                                        'warehouse_id' => $warehouseId,
                                        'qty'          => $scannedQty[0],
                                        'enable'       => 1
                                    );
                                    $productInfo->setData($arrData);
                                    $productInfo->save();

                                    // Update new quantity for global stock
                                    $productInfo = Mage::getModel('catalog/product')->load($productIds[$_i])->getStockItem();
                                    $currQty     = $productInfo->getQty();
                                    $currQty    += $scannedQty[0];
                                    $productInfo->setQty($currQty);
                                    $productInfo->save();
                                }
                                // Reset quantity of scanned product
                                $scannedQty[0] = 0;
                                $this->return = Mage::getModel('barcode/countinventory')->updateCountInventData(intval($productIds[$_i]), $scannedQty[0]);
                                if(!$this->return["error"]){
                                    $success_count +=1;
                                }
                            }
                            $this->return["success_count"] = $success_count;
                        }
                        else {
                            for($_i = 0; $_i < count($productIds); $_i++){
                                // Get scanned quantity
                                $scannedQty = Mage::getModel('barcode/countinventory')->getScannedQtyById(intval($productIds[$_i]));

                                // Update stock quantity
                                $product   = Mage::getModel('catalog/product')->load(intval($productIds[$_i]));
                                $stockData = $product->getStockItem();
                                $stockData->setData('qty',intval($scannedQty[0]));
                                $stockData->save();

                                // Reset quantity of scanned product
                                $scannedQty[0] = 0;
                                $this->return = Mage::getModel('barcode/countinventory')->updateCountInventData(intval($productIds[$_i]), $scannedQty[0]);
                                if(!$this->return["error"]){
                                    $success_count +=1;
                                }
                            }
                            $this->return["success_count"] = $success_count;
                        }
                        break;
                    case 'plusall':
                        if($validateMWHenabled == 1){
                            for($_i = 0; $_i < count($productIds); $_i++){
                                // Get scanned quantity
                                $scannedQty = Mage::getModel('barcode/countinventory')->getScannedQtyById(intval($productIds[$_i]));

                                // Update product information
                                $collection = Mage::getModel('xwarehouse/warehouse_product')->getCollection();
                                $collection->addFieldToFilter('product_id', $productIds[$_i]);
                                $collection->addFieldToFilter('warehouse_id', $warehouseId);
                                $productInfo = $collection->getFirstItem();
                                $originQty   = $productInfo->getQty();

                                if($originQty != null){
                                    $productInfo->setQty($originQty + $scannedQty[0]);
                                    $productInfo->save();

                                    $productStock = Mage::getModel('catalog/product')->load($productIds[$_i])->getStockItem();
                                    $stockQty     = $productStock->getQty();
                                    $stockQty    += $scannedQty[0];
                                    $productStock->setQty($stockQty);
                                    $productStock->save();
                                }
                                else {
                                    $arrData = array(
                                        'product_id'   => $productIds[$_i],
                                        'warehouse_id' => $warehouseId,
                                        'qty'          => $scannedQty[0],
                                        'enable'       => 1
                                    );
                                    $productInfo->setData($arrData);
                                    $productInfo->save();

                                    // Update new quantity for global stock
                                    $productInfo = Mage::getModel('catalog/product')->load($productIds[$_i])->getStockItem();
                                    $currQty     = $productInfo->getQty();
                                    $currQty    += $scannedQty[0];
                                    $productInfo->setQty($currQty);
                                    $productInfo->save();
                                }
                                // Reset quantity of scanned product
                                $scannedQty[0] = 0;
                                $this->return = Mage::getModel('barcode/countinventory')->updateCountInventData($productIds[$_i], $scannedQty[0]);
                                if(!$this->return["error"]){
                                    $success_count +=1;
                                }
                            }
                            $this->return["success_count"] = $success_count;
                        }
                        else {
                            for($_i = 0; $_i < count($productIds); $_i++){
                                // Get current quantity and scanned quantity of products
                                $product   = Mage::getModel('catalog/product')->load($productIds[$_i]);
                                $stockData = $product->getStockItem();
                                $currentQty = intval($stockData->getData('qty'));
                                $scannedQty = Mage::getModel('barcode/countinventory')->getScannedQtyById(intval($productIds[$_i]));

                                // Set new value for current quantiy of product
                                $currentQty += $scannedQty[0];

                                // Update current quantity (saved in database)
                                $stockData->setData('qty',$currentQty);
                                $stockData->save();

                                // Reset quantity of scanned product
                                $scannedQty[0] = 0;
                                $this->return = Mage::getModel('barcode/countinventory')->updateCountInventData($productIds[$_i], $scannedQty[0]);
                                if(!$this->return["error"]){
                                    $success_count +=1;
                                }
                            }
                            $this->return["success_count"] = $success_count;
                        }
                        break;
                    case 'reduceall':
                        if($validateMWHenabled == 1){
                            for($_i = 0; $_i < count($productIds); $_i++){
                                //Get scanned quantity
                                $scannedQty = Mage::getModel('barcode/countinventory')->getScannedQtyById(intval($productIds[$_i]));
                                $addnewProductToWarehouseFlag = false;

                                //Update product information
                                $collection = Mage::getModel('xwarehouse/warehouse_product')->getCollection();
                                $collection->addFieldToFilter('product_id', $productIds[$_i]);
                                $collection->addFieldToFilter('warehouse_id', $warehouseId);
                                $productInfo = $collection->getFirstItem();
                                $originQty   = $productInfo->getQty();

                                if($originQty != null){
                                    if($originQty >= $scannedQty[0]){
                                        $productInfo->setQty($originQty - $scannedQty[0]);
                                        $productInfo->save();

                                        $productStock = Mage::getModel('catalog/product')->load($productIds[$_i])->getStockItem();
                                        $stockQty     = $productStock->getQty();
                                        $stockQty    -= $scannedQty[0];
                                        $productStock->setQty($stockQty);
                                        $productStock->save();
                                    }
                                    else {
                                        $arrData = array(
                                            'product_id'   => $productIds[$_i],
                                            'warehouse_id' => $warehouseId,
                                            'qty'          => 0,
                                            'enable'       => 1
                                        );
                                        $productInfo->setData($arrData);
                                        $productInfo->save();
                                        $addnewProductToWarehouseFlag = true;
                                    }
                                    // Reset quantity of scanned product
                                    if(!$addnewProductToWarehouseFlag){
                                        $scannedQty[0] = 0;
                                        $this->return = Mage::getModel('barcode/countinventory')->updateCountInventData($productIds[$_i], $scannedQty[0]);
                                        if(!$this->return["error"]){
                                            $success_count +=1;
                                        }
                                    }
                                }
                                else {
                                    $this->return['msg']   = 'Reducing quantity of product in stock failed';
                                    $this->return['error'] = true;
                                }
                            }
                            $this->return["success_count"] = $success_count;
                        }
                        else {
                            for($_i = 0; $_i < count($productIds); $_i++){
                                // Get current quantity and scanned quantity of product
                                $product   = Mage::getModel('catalog/product')->load($productIds[$_i]);
                                $stockData = $product->getStockItem();
                                $currentQty = intval($stockData->getData('qty'));
                                $scannedQty = Mage::getModel('barcode/countinventory')->getScannedQtyById(intval($productIds[$_i]));
                                // Execute
                                if($currentQty >= $scannedQty[0]){
                                    $currentQty -= $scannedQty[0];
                                    // Update stock quantity
                                    $stockData->setData('qty',$currentQty);
                                    $stockData->save();

                                    // Reset quantity of scanned product
                                    $scannedQty[0] = 0;
                                    $this->return = Mage::getModel('barcode/countinventory')->updateCountInventData($productIds[$_i], $scannedQty[0]);
                                    if(!$this->return["error"]){
                                        $success_count +=1;
                                    }
                                }
                            }
                            $this->return["success_count"] = $success_count;
                        }
                        break;
                    case 'deleteall':
                        for($_i = 0; $_i < count($productIds); $_i++){
                            // Delete product
                            $this->return = Mage::getModel('barcode/countinventory')->deleteProductByProductId($productIds[$_i]);
                            if(!$this->return["error"]){
                                $success_count +=1;
                            }
                        }
                        $this->return["success_count"] = $success_count;
                        break;
                    default:
                        $this->return['msg']   = 'Adding quantity of product in stock failed';
                        $this->return['error'] = true;
                        break;
                }
            }
        }
        else {
            $this->return['msg']   = 'Adding quantity of product in stock failed';
            $this->return['error'] = true;
        }
        $this->_outputJSON();
    }

    /*
     @description    : Get current quantity of product based on warehouse selected .
     @author         : thangnv@smartosc.com
     @date           : 29th July, 2013
     @param
     */
    public function ajaxwarehousequantityAction(){
        // Get parameters from client
        $productId    = isset($_GET['productId']) ? intval($_GET['productId']) : 0;
        $warehouseId  = isset($_GET['warehouseId']) ? intval($_GET['warehouseId']) : 0;
        $warehouseLbl = isset($_GET['warehouseLabel']) ? (string)$_GET['warehouseLabel'] : null;
        // Get Warehouse information
        if($productId == 0 || $warehouseLbl == null){
            $this->return['msg'] = 'Please choose product and warehouse';
            $this->return['error'] = true;
        }
        else {
            if($warehouseLbl == 'allwarehouse'){
                $product    = Mage::getModel('catalog/product')->load(intval($productId))->getStockItem();
                $productQty = $product->getData('qty');
                $this->return['productQty'] = $productQty;
            }
            else {
                $productQty = Mage::helper('xwarehouse/data')->getWarehouseQty($productId,$warehouseId);
                if(!$productQty){
                    $this->return['productQty'] = 0;
                } else {
                    $this->return['productQty'] = $productQty;
                }
            }
        }
        // Return message
        $this->_outputJSON();
    }

    /*
     @description    : Update current quantity of product
     @author         : thangnv@smartosc.com
     @date           : 30th July, 2013
     @param
     */
    public function ajaxupdatecurrqtyAction(){
        // Get parameters from client
        $productId     = isset($_GET['productId']) ? intval($_GET['productId']) : 0;
        $newProductQty = isset($_GET['newProductQty']) ? intval($_GET['newProductQty']) : 0;

        $validateMWHenabled = Mage::getStoreConfig('xwarehouse/general/enabled');
        if ($validateMWHenabled == 1) {
            $warehouseId   = isset($_GET['warehouseId']) ? intval($_GET['warehouseId']) : 0;
            $warehouseLbl  = isset($_GET['warehouseLabel']) ? (string)$_GET['warehouseLabel'] : null;
        }

        if ($validateMWHenabled == 1){

            if($productId == 0 || $warehouseLbl == null || $warehouseLbl == 'allwarehouse' || $newProductQty == null){
                $this->return['msg'] = 'Invalid product or warehouse.';
                $this->return['error'] = true;
            } else {
                $collection = Mage::getModel('xwarehouse/warehouse_product')->getCollection();
                $collection->addFieldToFilter('product_id', $productId);
                $collection->addFieldToFilter('warehouse_id', $warehouseId);
                $productInfo = $collection->getFirstItem();
                $originQty   = $productInfo->getQty();

                // Check product is existed or not
                if($originQty != null){
                    // Update new quantity for warehouse
                    $productInfo->setQty($newProductQty);
                    $productInfo->save();

                    // Update new quantity for global stock
                    $productInfo = Mage::getModel('catalog/product')->load($productId)->getStockItem();
                    $stockQty    = $productInfo->getQty();
                    $stockQty   += ($newProductQty - $originQty);
                    $productInfo->setQty($stockQty);
                    $productInfo->save();
                } else {
                    $arrData = array(
                        'product_id'   => $productId,
                        'warehouse_id' => $warehouseId,
                        'qty'          => $newProductQty,
                        'enable'       => 1
                    );
                    $productInfo->setData($arrData);
                    $productInfo->save();

                    // Update new quantity for global stock
                    $productInfo = Mage::getModel('catalog/product')->load($productId)->getStockItem();
                    $currQty     = $productInfo->getQty();
                    $currQty    += $newProductQty;
                    $productInfo->setQty($currQty);
                    $productInfo->save();
                }

                $this->return['msg'] = 'Updated successfully.';
                $this->return['error'] = false;
            }

        } else {
            if (($productId == 0) || ($newProductQty < 0)) {
                $this->return['msg'] = 'Invalid product or bad product quantity.';
                $this->return['error'] = true;
            } else {
                // Update new quantity for global stock
                $productInfo = Mage::getModel('catalog/product')->load($productId)->getStockItem();
                $productInfo->setQty($newProductQty);
                $productInfo->save();

                $this->return['msg'] = 'Updated successfully.';
                $this->return['error'] = false;
            }
        }

        $this->_outputJSON();
    }

    protected function _outputJSON()
    {
        $this->getResponse()->setHttpResponseCode(200);
        $this->getResponse()->setHeader('Content-Type', 'application/json');
        $this->getResponse()->setBody(json_encode($this->return));
    }
}



