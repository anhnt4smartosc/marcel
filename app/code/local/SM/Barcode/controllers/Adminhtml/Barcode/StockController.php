<?php
/**
 * Date: 1/11/13
 * Time: 3:23 PM
 */

class SM_Barcode_Adminhtml_Barcode_StockController extends SM_Barcode_Controller_Adminhtml_Action {
    public function indexAction() {
        $this->_title($this->__('Barcode'))
            ->_title($this->__('Manage Stock'));
        $this->loadLayout()->_setActiveMenu('smartosc/barcode_stock');
        $this->renderLayout();
    }


    public function gridAction() {
        $this->loadLayout(false);
        $this->renderLayout();
    }

    public function saveAction() {
        $result = array();
        $action = $this->getRequest()->getPost('save_action');

        $result['message'] = 'Hello';
        $result['error'] = false;

        $this->getResponse()->setBody(Mage::helper('barcode')->jsonEncode($result));
    }

    public function checkStockAction() {
        $result = array();
        $result['message'] = 'Hello this is checkStock';
        $result['error'] = false;

        $barcode = $this->getRequest()->getPost('product_barcode');
        // if (Mage::getStoreConfig('barcode/barcode_conversion/conversion')==0) { //Conversion OFF
            // if barcode symbology is not env13 then cut out the last character
//            if (Mage::getStoreConfig('barcode/general/symbology') == 0) //EAN13
//                $barcode = substr($barcode, 0, -1);

        // }

        $_products = Mage::getModel('catalog/product')->getCollection();
        // $products->addAttributeToSelect('id');
        $_products->addFieldToFilter(array(
            array('attribute'=>'sm_barcode','like'=> $barcode . "%"),
        ));

        if (count($_products) > 0):
                foreach($_products as $_product): 
                    $_product = Mage::getModel('catalog/product')->load($_product->getId()); 
                    $result['product_id'] =  $_product->getId();
                    break;
                endforeach;
        endif; 

        // foreach($products as $p ) {
        //     $p = Mage::getModel('catalog/product')->load($p->getId());
        //     $xbar = $this->_getBarcode($p);
        //     if ($barcode == $xbar) {
        //         $result['product_id'] = $p->getId();
        //         break;
        //     }
        // }
        $this->getResponse()->setBody(Mage::helper('barcode')->jsonEncode($result));
}

    public function updateStockAction() {
        $quantity =  $this->getRequest()->getPost('new-qty');
        $productId = $this->getRequest()->getPost('product_id');

        if (!isset($productId) || !isset($quantity)) {
            throw new Exception;
        }

        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
        //Mage::log($stock);
        $stock->setIsInStock($quantity > 0 ? 1 : 0);
        $stock->setQty($quantity);
        try {
            $stock->save();
//            Mage::getSingleton('cataloginventory/stock_status')
//                ->updateStatus($productId);
            $result['error'] = false;
        }
        catch (Exception $ex) {
            $result['error'] = true;
        }
        $this->getResponse()->setBody(Mage::helper('barcode')->jsonEncode($result));
    }

    protected function _getBarcode($product) {
        $field = $product->getData('sm_barcode');
        return $field;
    }
}
