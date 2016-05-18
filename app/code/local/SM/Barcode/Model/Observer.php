<?php
class SM_Barcode_Model_Observer
{
    public function handleProductGridMassaction($observer) 
    {
        $grid = $observer->getGrid();
        if (Mage::helper('barcode')->isEnable()) {
            $grid->getMassactionBlock()->addItem('printbarcode', array(
                'label' => Mage::helper('catalog')->__('Print Barcode'),
                'url' => $grid->getUrl('adminhtml/barcode_product/index', array('_current' => true))
            ));
        }
        return $this;
    }

    public function generateBarcode()
    {
        Mage::getSingleton('adminhtml/session')->addError('Note: When changing settings for Barcode Conversion or changing Barcode symbology it is necessary to regenerate new product barcodes.');

//        if (Mage::helper('smcore')->checkLicense(SM_Barcode_Helper_Abstract::PRODUCT, Mage::getStoreConfig('barcode/general/key'))) {
//            $products = Mage::getModel('catalog/product')->getCollection();
//            foreach($products as $product) {
//                $productIds = $product->getId();
//
//                if (intval(Mage::getStoreConfig("barcode/product/conversion") == 1))
//                {
//                    switch (intval(Mage::getStoreConfig("barcode/product/barcode_field")))
//                    {
//                        case 0:
//                            $field = str_pad($productIds, 12, "0", STR_PAD_LEFT);
//                            break;
//                        case 1:
//                            $field = substr(number_format(hexdec(substr(md5($product->getSku()), 0, 16)), 0, "", ""), 0, 12);
//                            break;
//                        case 2:
//                            $attr_id = Mage::getStoreConfig("barcode/product/barcode_source");
//                            $attr = Mage::getModel('eav/entity_attribute')->load($attr_id)->getAttributeCode();
//                            $attr_val = $product->getResource()->getAttribute($attr)->getFrontend()->getValue($product);
//                            $field = substr(number_format(hexdec(substr(md5($attr_val), 0, 16)), 0, "", ""), 0, 12);
//                            break;
//                    }
//                }
//                else // Conversion: OFF
//                {
//                    $attr_id = Mage::getStoreConfig('barcode/product/barcode_value');
//                    $attr = Mage::getModel('eav/entity_attribute')->load($attr_id)->getAttributeCode();
//                    $store_id = Mage::app()->getStore()->getStoreId();
//                    $attr_val = Mage::getResourceModel('catalog/product')->getAttributeRawValue($product->getId(), $attr, $store_id);
//                    $field = $attr_val;
//                }
//
//                if (Mage::getStoreConfig('barcode/general/symbology') == 0 && Mage::getStoreConfig("barcode/product/conversion") == 1) {
//                    $helper = Mage::helper('barcode/barcode');
//                    $helper->addLastDigitForEan13($field);
//                }
//                $field = trim($field);
//                Mage::getSingleton('catalog/product_action')->updateAttributes(array($productIds), array('sm_barcode' => $field), 0);
//            }//end foreach
//
//
//        }
    }

    public function generateBarcodeForSingleProduct($observer) {
        if (!Mage::helper('barcode/license')->checkLicense(SM_Barcode_Helper_Abstract::PRODUCT, Mage::getStoreConfig('barcode/general/key'))) {
            return;
        }
        $before = Mage::registry('original_product');
        $after = $observer->getEvent()->getProduct();
        if(!$after->getId()) { // If product is newly created
            self::createBarcode($after->getId());
            return;
        }

        $attr = ''; // capture attribute code which was set to generate barcode
        $conversion = Mage::getStoreConfig("barcode/product/conversion");
        $symbology = Mage::getStoreConfig('barcode/general/symbology');


        if (intval($symbology == 1)):
            switch (intval(Mage::getStoreConfig("barcode/product/barcode_field"))) {
                case 1:
                    $attr = 'sku';
                    break;
                case 2:
                    $attr_id = Mage::getStoreConfig("barcode/product/barcode_source");
                    $attr = Mage::getModel('eav/entity_attribute')->load($attr_id)->getAttributeCode();
                    break;
            }
        else:
            $attr_id = Mage::getStoreConfig('barcode/product/barcode_value');
            $attr = Mage::getModel('eav/entity_attribute')->load($attr_id)->getAttributeCode();
        endif;

        if ($attr != '' && isset($before)) {
            $attr_val_before = $before->getResource()->getAttribute($attr)->getFrontend()->getValue($before);
            $attr_val_after = $after->getResource()->getAttribute($attr)->getFrontend()->getValue($after);

            if ($attr_val_before != $attr_val_after) {
                $productIds = $after->getId();
                $field = '';

                if (intval($conversion == 1)):
                    switch (intval(Mage::getStoreConfig("barcode/product/barcode_field"))) {
                        case 0:
                            $field = str_pad($productIds, 12, "0", STR_PAD_LEFT);
                            break;
                        case 1:
                            $field = substr(number_format(hexdec(substr(md5($after->getSku()), 0, 16)), 0, "", ""), 0, 12);
                            break;
                        case 2:
                            $attr_id = Mage::getStoreConfig("barcode/product/barcode_source");
                            $attr = Mage::getModel('eav/entity_attribute')->load($attr_id)->getAttributeCode();
                            $attr_val = $after->getResource()->getAttribute($attr)->getFrontend()->getValue($after);
                            $field = substr(number_format(hexdec(substr(md5($attr_val), 0, 16)), 0, "", ""), 0, 12);
                            break;
                    }
                else:
                    $attr_id = Mage::getStoreConfig('barcode/product/barcode_value');
                    $attr = Mage::getModel('eav/entity_attribute')->load($attr_id)->getAttributeCode();
                    $attr_val = $after->getResource()->getAttribute($attr)->getFrontend()->getValue($after);
                    $field = $attr_val;
                endif;

                $field = trim($field);

                //EAN13, Conversion OFF
                if($symbology == 0 && $conversion == 0 ){
                    //Check $field leng and is number
                    if(strlen($field) < 12 && intval($field) != 0){
                        //Add prefix 0
                        $field = str_pad($field, 12, "0", STR_PAD_LEFT);
                    }
                }
                if ($symbology == 0) {
                    $helper = Mage::helper('barcode/barcode');
                    $helper->addLastDigitForEan13($field);
                }


                Mage::getSingleton('catalog/product_action')->updateAttributes(array($productIds), array('sm_barcode' => $field), 0);
            }
        }
    }

    public function hookSaveProductBefore($observer)
    {
        $post = Mage::app()->getRequest()->getParams();
        if (!isset($post['id'])) return;
        $_product = Mage::getModel('catalog/product')->load($post['id']);
        Mage::register('original_product', $_product);
    }

    protected function createBarcode($id) {
        $product = Mage::getModel('catalog/product')->load($id);
        if (!($product->getId())) return false;
        $field = '';

        if (intval(Mage::getStoreConfig("barcode/product/conversion") == 1)):

            switch (intval(Mage::getStoreConfig("barcode/product/barcode_field"))) {
                case 0:
                    $field = str_pad($id, 12, "0", STR_PAD_LEFT);
                    break;
                case 1:
                    $sku = $product->getSku();
                    if(!empty($sku)) $field = substr(number_format(hexdec(substr(md5($product->getSku()), 0, 16)), 0, "", ""), 0, 12);
                    break;
                case 2:
                    $attr_id = Mage::getStoreConfig("barcode/product/barcode_source");
                    $attr = Mage::getModel('eav/entity_attribute')->load($attr_id)->getAttributeCode();
                    $attr_val = $product->getResource()->getAttribute($attr)->getFrontend()->getValue($product);
                    if(!empty($attr_val)) $field = substr(number_format(hexdec(substr(md5($attr_val), 0, 16)), 0, "", ""), 0, 12);
                    break;
            }
        else:
            $attr_id = Mage::getStoreConfig('barcode/product/barcode_value');
            $attr = Mage::getModel('eav/entity_attribute')->load($attr_id)->getAttributeCode();
            $attr_val = $product->getResource()->getAttribute($attr)->getFrontend()->getValue($product);
            $field = $attr_val;
        endif;

        $field = trim($field);


        //EAN13, Conversion OFF
        if(Mage::getStoreConfig('barcode/general/symbology') == 0 && Mage::getStoreConfig('barcode/general/conversion') == 0 ){
            //Check $field leng and is number
            if(strlen($field) < 12 && intval($field) != 0){
                //Add prefix 0
                $field = str_pad($field, 12, "0", STR_PAD_LEFT);
            }
        }

        if (Mage::getStoreConfig('barcode/general/symbology') == 0) {
            $helper = Mage::helper('barcode/barcode');
            $helper->addLastDigitForEan13($field);
        }


        // save barcode
        Mage::getSingleton('catalog/product_action')->updateAttributes(array($id), array('sm_barcode' => $field), 0);
    }
    /**
     * Remove old value of barcode field when duplicate product
    */
    public function catalogModelProductDuplicate($observer) {
        $newProduct = $observer->getNewProduct();
        $newProduct->setData('sm_barcode',null);
    }

    protected $is_barcode_created = false;

    public function catalogProductSaveAfter($observer) {

        if(is_null(Mage::registry('is_created'))) Mage::register('is_created',false);
        $product = $observer->getProduct();
        // if SM_Barcode_Adminhtml_Catalog_ProductController::quickCreateAction is overridden
        // So need set sm_barcode data to null
        if (substr(Mage::app()->getRequest()->getControllerName(), -15) === 'catalog_product'
            && Mage::app()->getRequest()->getActionName() === 'quickCreate') {
            $product->setData('sm_barcode',null);
        }
        $sm_barcode = $product->getData('sm_barcode');
        $sku = $product->getData('sku');
        $id = $product->getId();
        $name = $product->getName();

        if (empty($sm_barcode) || is_null($sm_barcode) && Mage::registry('is_created') == false)  {
            self::createBarcode($product->getId());
            Mage::unregister('is_created');
            Mage::register('is_created',true);
        }
    }

    public function refreshStatus($event)
    {
        Mage::helper('barcode/license')->refreshStatus($event);
    }

    public function saveModifiedDate(){
        $time = date("Y-m-d h:i:sa");
        $path = 'barcode/general/date_modified';
        Mage::getModel('core/config')->saveConfig($path, $time);
    }
}