<?php

/**
 * Created by PhpStorm.
 * User: NguyenCT
 * Date: 3/28/14
 * Time: 10:38 AM
 */
class Alex_Sales_Helper_Product extends SM_XPos_Helper_Product {
    private $_isOnlineMode = false;
    protected $_store;
    private $_bassCurrency;
    private $_currentCurrency;
    private $_billingAdd = null;
    private $_shippingAdd = null;
    private $_defaultCustomerId = null;
    private $_xposHelperData;
    protected $_integrateMSMWH;
    protected $_xposCache;

    protected $_warehouseId;

    public function __construct()
    {
        parent::__construct();
        $this->_store = Mage::app()->getStore(Mage::helper('xpos')->getXPosStoreId());
        $this->_xposHelperData = Mage::helper('xpos');
        $this->_xposCache = Mage::getSingleton('xpos/cache');
        $this->_integrateMSMWH = Mage::getSingleton('xpos/integrate_mageStoreMWH_integrate');
    }

    public function getProductList($controller, $page = 1, $warehouseId) {
        $this->_warehouseId = $warehouseId;
        $this->_isOnlineMode = false;
        Mage::helper('catalog/product')->setSkipSaleableCheck(true);

        $storeId = Mage::getSingleton('adminhtml/session')->getCurrentStoreView();
        $limit = Mage::helper('xpos/configXPOS')->getProdPerRequest();

        $productInfo = array();

        $productCollection = Mage::getResourceModel('catalog/product_collection')
            ->setStoreId($storeId)
            ->addStoreFilter($storeId)
            ->addAttributeToSelect('*')
            ->setPageSize($limit)
            ->setCurPage($page);

        if (Mage::helper('xpos/configXPOS')->getSearchingInStock() == 1 && $this->_xposHelperData->isIntegrateWithMageStoreMWH($warehouseId)) {
            Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($productCollection);
        }


        if ($this->_xposHelperData->isIntegrateWithXMWH($warehouseId)) {
            $productCollection->getSelect()->joinLeft(Mage::getConfig()->getTablePrefix() . 'erp_inventory_warehouse_product', 'entity_id =' . Mage::getConfig()->getTablePrefix() . 'erp_inventory_warehouse_product.product_id', array("warehouse_id"))
                ->where(Mage::getConfig()->getTablePrefix() . "erp_inventory_warehouse_product.warehouse_id = " . $warehouseId);
        }

        $productCollection = $this->queryProduct($productCollection);

        if ($productCollection->getLastPageNumber() < $page) {
            return array('productInfo' => $productInfo, 'totalProduct' => $productCollection->getSize(), 'totalLoad' => 0);
        }

        $productCollection->load();

        //        set Store
        Mage::app()->setCurrentStore($this->getCurrentSessionStoreId());
        $this->_bassCurrency = Mage::app()->getStore()->getBaseCurrencyCode();
        $this->_currentCurrency = Mage::app()->getStore()->getCurrentCurrencyCode();

        $allowProduct = array();
        if (!empty($warehouseId))
            $this->_allProduct = $allowProduct = $this->getWarehouseProduct($warehouseId);


        //$price_display_type_config = Mage::app()->getStore()->getConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE);
        //if ($price_display_type_config == Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX) {
        //    Mage::app()->getStore()->setConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE, Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH);
        //}

        $injectCustomSalesProduct = false;
        foreach ($productCollection as $product) {
            if (!array_key_exists($product->getId(), $allowProduct) && !empty($warehouseId) && $product->getTypeId() == 'simple') {
                continue;
            }
            if ($product->getId() == Mage::helper('xpos/customSalesHelper')->getCustomSalesId())
                continue;

            $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);

            if (!$this->_billingAdd || !$this->_shippingAdd) {
                $addDefaultCustomer = Mage::getModel('xpos/guest')->getAddDefaultCustomer();
                $billingAdd = $this->_billingAdd = $addDefaultCustomer['billingAdd'];
                $shippingAdd = $this->_shippingAdd = $addDefaultCustomer['shippingAdd'];

            } else {
                $billingAdd = $this->_billingAdd;
                $shippingAdd = $this->_shippingAdd;
            }
            $this->_isOnlineMode = true;
            if (!$this->_defaultCustomerId) {
                $this->_defaultCustomerId = Mage::helper('xpos/configXPOS')->getDefaultCustomerId(Mage::helper('xpos/product')->getCurrentSessionStoreId());
            }
            $pInfo = $this->extractData($controller, $product, $billingAdd, $shippingAdd, $this->_defaultCustomerId);

            if (is_null($pInfo))
                continue;

            if ($this->_xposHelperData->isIntegrateWithXMWH($warehouseId)) {
                $collection_qty = Mage::getModel('inventoryplus/warehouse_product')->getCollection()
                    ->addFieldToFilter('warehouse_id', array('eq' => $warehouseId))
                    ->addFieldToFilter('product_id', array('eq' => $product->getId()));
                $info_array = $collection_qty->getData();

                $pInfo['qty'] = $info_array[0]['available_qty'];

            } elseif ($this->_xposHelperData->isIntegrateWithMageStoreMWH($warehouseId)) {
                if (isset($allowProduct[$product->getId()])) {
                    $data = $allowProduct[$product->getId()];
                    $pInfo['qty'] = $data['qty'];
                } else
                    $pInfo['qty'] = 0;
            } else
                $pInfo['qty'] = $stock->getQty();


            $pInfo['is_qty_decimal'] = '123123';
            $pInfo['has_manager_inventory'] = $stock->getData('use_config_manage_stock');
            /** XPOS 2091: Refine product data to do save into localStorage
             * Quantity SHOULD be Integer instead of current Float
             * @temporary
             */
            $pInfo['qty'] = $pInfo['is_qty_decimal'] == '1' ? $pInfo['qty'] : (int)$pInfo['qty'];

            Zend_Debug::dump($pInfo);

            $productInfo[$pInfo['id']] = $pInfo;
        }
        if (!$injectCustomSalesProduct && $page == 1) {
            $id = Mage::helper('xpos/customSalesHelper')->getCustomSalesId();
            $productInfo[$id] = $this->getCustomSale($controller);
            $injectCustomSalesProduct = true;
        }
        //if ($price_display_type_config == Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX) {
        //    Mage::app()->getStore()->setConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE, Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX);
        //}

        return array('productInfo' => $productInfo, 'totalProduct' => $productCollection->getSize(), 'totalLoad' => sizeof($productInfo));
    }

    private function getIayzModel() {
        return Mage::getSingleton('xpos/iayz');
    }

    function extractData($controller, $product, $billingAdd = null, $shippingAdd = null, $customerId = null) {
        $productType = $product->getTypeId();
        $hasOption = false;
        if ($product->getHasOptions() && $productType == 'simple') {
//            foreach ($product->getProductOptionsCollection() as $option) {
//                $option->setProduct($product);
//                $product->addOption($option);
//            }
            $hasOption = true;
        }

        $image = Mage::helper('catalog/image');
        $tax = Mage::helper('xpos/tax');
        $options = null;

        $smallImage = $product->getData('small_image');
        if ($smallImage != null && $smallImage != 'no_selection') {
            try {
                $smallImage = $image->init($product, 'small_image')->resize(75)->__toString();
            } catch (Exception $e) {
                $smallImage = null;
            }

        } else {
            $smallImage = null;
        }

        //another search data
        $searchBy = Mage::helper('xpos/configXPOS')->getSearchBy();
        $anotherData = '';
        if ($searchBy != '') {
            $result = array();
            $attributes = explode(",", $searchBy);
            foreach ($attributes as $attribute) {
                if ($attribute != 'apparel_type') {
                    $anotherData .= $product->getResource()->getAttribute($attribute)->getFrontend()->getValue($product) . ' ';
                } else {
                    $anotherData .= $product->getTypeID() . ' ';
                }
            }
        }

        //additional field show in search
//            $additional = Mage::getStoreConfig('xpos/search/additional_field');
//            $additionalData = $product->getResource()->getAttribute($additional)->getFrontend()->getValue($product) . ' ';

        if ($this->getIayzModel()->ultraBootLoad()) {
        } else {
            if ($product->getHasOptions() || $productType == "configurable" || $productType == "bundle" || $productType == "grouped" || $productType == "giftcard") {
                $productId = $product->getId();
                Mage::app()->setCurrentStore(Mage::helper('xpos/product')->getCurrentSessionStoreId());
                $product = Mage::getModel('catalog/product')
                    ->setStoreId(Mage::helper('xpos/product')->getCurrentSessionStoreId())
                    ->load($productId);
            }
            Mage::unregister('current_product');
            Mage::unregister('product');
            Mage::register('current_product', $product);
            Mage::register('product', $product);
            $update = $controller->getLayout()->getUpdate();
            $type = 'LAYOUT_GENERAL_CACHE_TAG';
            Mage::app()->getCacheInstance()->cleanType($type); // Clean cache //Mage::app()->cleanCache();
            if ($product->getHasOptions() || $productType == "configurable" || $productType == "bundle"
                || $productType == "grouped" || $productType == "giftcard" || $productType == "giftvoucher"
            ) {
                $update->resetHandles();
                $update->addHandle('ADMINHTML_XPOS_CATALOG_PRODUCT_COMPOSITE_CONFIGURE');
                $update->addHandle('XPOS_PRODUCT_TYPE_' . $productType);
                $controller->loadLayoutUpdates()->generateLayoutXml()->generateLayoutBlocks();
                $options = $controller->getLayout()->getOutput();
                if (strlen($options) < 3) {
                    $options = null;
                }
            }
        }

        if ($productType == 'giftcard') {

            $temp = $product->getData('giftcard_amounts');
            if (isset($temp[0])) {
                $price = (float)$temp[0]['value'];
            } else $price = 0;
            //$price = $product->getData('giftcard_amounts')[0]['value'];
//            $finalPrice = (float)$price;
//            $finalPriceWithTax = (float)$price;
        }

        $lstBundleId = null;

        /*Integrate with another multiware house*/
        if ($product->getTypeId() == 'bundle') {
            if ($this->_xposHelperData->isIntegrateWithMageStoreMWH($this->_warehouseId))
                if (!$this->_integrateMSMWH->checkSalesAbleBundleProduct($product->getTypeInstance(true)->getChildrenIds($product->getId(), false), $this->_allProduct))
                    return null;
        }

        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type_Grouped::TYPE_CODE) {
            if ($this->_xposHelperData->isIntegrateWithMageStoreMWH($this->_warehouseId))
                if (!$this->_integrateMSMWH->checkSalesAbleGroupedProduct($product->getTypeInstance(true)->getAssociatedProducts($product), $this->_allProduct))
                    return null;
        }
        /*\End integrate*/


        if ($product->getTypeId() == 'configurable') {
            $conf = Mage::getModel('catalog/product_type_configurable')->setProduct($product);

            $simple_collection = $conf->getUsedProductCollection()->addAttributeToSelect('*')->addFilterByRequiredOptions();

            foreach ($simple_collection as $simplePro) {
                $childId = $simplePro->getData('entity_id');
                $childProduct = Mage::getModel('catalog/product')->load($childId);
                $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($childProduct);
                $lstBundleId[$simplePro->getData('entity_id')] = $stock->getQty();
            }
        }
        if ($product->getTypeId() == 'configurable') {
            /*TODO: get child product with attribute and qty*/
            if ($this->_xposHelperData->isIntegrateWithMageStoreMWH($this->_warehouseId)) {
                /*if integrate with magestore multiWareHouse and will not collect configurable product if its not saleable */
                $childProductConfig = $this->getIayzModel()->getChildOfConfigurableProductAttributeOption($product->getId(), $this->_allProduct);
                if (!$childProductConfig)
                    return null;
            } else
                $childProductConfig = $this->getIayzModel()->getChildOfConfigurableProductAttributeOption($product->getId());
        }


        /* BEGIN RE CAL TAX - END Price
         * Perfect behavior: in System > Configuration > Tax > Calculation Settings > Tax Calculation Based On
         *  If Tax Calculation Based On is "Shipping Origin": tax amount of product in local data is calculated from address in System > Configuration > Shipping Address > Origin
         * If Tax Calculation Based On is "Shipping Adress": tax amount of product in local data is calculated from address in System > Configuration > Tax > Default Tax Destination Calculation
       */
        if ($this->_isOnlineMode == false) {
            $product->setTaxPercent(null);
            //$fPrice = $tax->getPrice($product, $product->getFinalPrice(), null, /*$shippingAddress =*/ false, /*$billingAddress = */ false, /*ctc = */ false);
            $fPrice = $tax->getPrice($product, $product->getFinalPrice(), false, null, null, null, null, null, true);
            $product->setTaxPercent(null);
            $fPriceInclTax = $tax->getPrice($product, $product->getFinalPrice(), true, null, null, null, null, null, true);
            $percent = $product->getTaxPercent();
            $includingPercent = null;
            $taxClassId = $product->getTaxClassId();
            if (is_null($percent)) {
                if ($taxClassId) {
                    $request = Mage::getSingleton('tax/calculation')
                        ->getRateRequest(null, null, null, null);
                    $percent = Mage::getSingleton('tax/calculation')
                        ->getRate($request->setProductClassId($taxClassId));
                }
            }
            if (!$percent) {
                $percent = 0;
            }
        } else {
            $product->setTaxPercent(null);
//            $currentStoreId = $this->getIayzModel()->getCurrentStoreId();
            $currentCustomer = Mage::getModel('customer/customer')->load($customerId);
            $customerTaxClassId = $this->getIayzModel()->getCustomerTaxClassId($currentCustomer);
            //$fPrice = $tax->getPrice($product, $product->getFinalPrice(), null, /*$shippingAddress =*/ false, /*$billingAddress = */ false, /*ctc = */ false);
            SM_XPos_Model_Tax_Calculation::$_getFromXpos = true;
            $fPrice = $tax->getPrice($product, $product->getFinalPrice(), false, $shippingAdd, $billingAdd, $customerTaxClassId, null, null, true);
            $product->setTaxPercent(null);
            SM_XPos_Model_Tax_Calculation::$_getFromXpos = true;
            $fPriceInclTax = $tax->getPrice($product, $product->getFinalPrice(), true, $shippingAdd, $billingAdd, $customerTaxClassId, null, null, true);
            $percent = $product->getTaxPercent();
            $includingPercent = null;
            $taxClassId = $product->getTaxClassId();
            if (is_null($percent)) {
                if ($taxClassId) {
                    $request = Mage::getSingleton('tax/calculation')
                        ->getRateRequest(null, null, null, null);
                    $percent = Mage::getSingleton('tax/calculation')
                        ->getRate($request->setProductClassId($taxClassId));
                }
            }
            if (!$percent) {
                $percent = 0;
            }
        }
        /*end - recal TAX AND PRICE*/

        $taxCalc = Mage::getSingleton('tax/calculation');
        $taxAmount = $taxCalc->calcTaxAmount($fPrice, $percent, false, true);

        if ($this->_currentCurrency) {
            $baseCurrencyCode = $this->_bassCurrency;
            $currentCurrencyCode = $this->_currentCurrency;
        } else {
            $this->_bassCurrency = Mage::app()->getStore()->getBaseCurrencyCode();
            $this->_currentCurrency = Mage::app()->getStore()->getCurrentCurrencyCode();
            $baseCurrencyCode = $this->_bassCurrency;
            $currentCurrencyCode = $this->_currentCurrency;
        }


        $directoryHelper = Mage::helper('directory');
        $priceFixCurrency = $directoryHelper->currencyConvert($fPrice, $baseCurrencyCode, $currentCurrencyCode);
        $priceInclTaxFixCurrency = $directoryHelper->currencyConvert($fPriceInclTax, $baseCurrencyCode, $currentCurrencyCode);


        $pInfo = array(
            'id' => $product->getId(),
            'visibility' => $product->getVisibility(),
            'type' => $product->getTypeId(),
            'name' => $product->getData('name'),
            'price' => $priceFixCurrency,
            'priceInclTax' => $priceInclTaxFixCurrency,
            //            'priceInclTax' => $product->getPrice(),
            'small_image' => $smallImage,
            'sku' => $product->getSku(),
            'tax' => $percent,
            'searchString' => trim($anotherData),
            'tax_amount' => $taxAmount,
            'commitRates' => $this->getCommitRate($product->getFinalPrice())
        );

        if (isset($childProductConfig))
            $pInfo['configProductData'] = $childProductConfig;
        if ($hasOption == true) {
            $pInfo['h'] = '1';
        }


        /*TODO: get setting to allow the stock to be managed via the backend. Reduce perfomance*/
        if ($this->getIayzModel()->isEnableManageStockbyMangento() == true) {
            $pInfo['ms'] = $this->getIayzModel()->getManageStockByProductId($pInfo['id']);
        }

        if ($options != null)
            $pInfo['options'] = $options;

        if ($this->getIayzModel()->getAddData() == true) {
            $pData = array();
            if ($product->getTypeId() != 'simple') {
                try {
                    $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')
                        ->getParentIdsByChild($product->getId());
                    foreach ($parentIds as $_parentId) {
                        $_parentProduct = Mage::getModel('catalog/product')->load($_parentId);
                        $_superAttributes = $_parentProduct->getTypeInstance(true)->getConfigurableAttributes($_parentProduct);
                        foreach ($_superAttributes as $_superAttribute) {
                            $_superAttributeCode = $_superAttribute->getProductAttribute()->getAttributeCode();
                            $pData[$_superAttributeCode] = $product->getData($_superAttributeCode);
                        }
                    }
                } catch (Exception $ex) {
                    Mage::log('Error load product: ' . $product->getId());
                }
            }
            /*
             * XPOS 2091: Refine product data to do save into localStorage
             * Only load "list_bundle", "options" for non simple products
             */
            if ($lstBundleId != null)
                $pData['list_bundle'] = $lstBundleId;

            /*
             * XPOS 1463: User can enable attribute(s) to display in product listing
             * This punch will make the load action slow down
             */
            $additionalAttributesForDisplayOnHover = Mage::helper('xpos/configXPOS')->getAttributeForDisplay();
            if (!empty($additionalAttributesForDisplayOnHover)) {
                $additionalInformation = array();
                $_attributes = explode(',', $additionalAttributesForDisplayOnHover);
                foreach ($_attributes as $_attribute) {
                    $_attributeValue = $product->getAttributeText($_attribute);
                    if ($_attributeValue == false) {
                        $_attributeValue = $product[$_attribute];
                    }
                    if ($_attributeValue)
                        $additionalInformation[$_attribute] = $_attributeValue;
                }
            }

            if (isset($additionalInformation) && !empty($additionalInformation)) {
                $pData['additional_info'] = $additionalInformation;
            }

            //for displaying child products SKU
            if (!is_null($pData)) {
                $pInfo = array_merge($pData, $pInfo);
            }
        }

        //$pInfo = array_filter( $pInfo, 'strlen' );
        return $pInfo;
    }

    public function getCommitRate($finalPrice) {
        $config = Mage::getStoreConfig('alexsales_commits/commit_points/payment_points');
        $data = unserialize($config);
        $result = array();

        if(sizeof($data)) {
            foreach ($data as $paymentConfig) {
                $finalRate = ($finalPrice / 100) * $paymentConfig['points'];
                $formattedRate = Mage::helper('directory')->currencyConvert($finalRate);
                $result[$paymentConfig['method_id']] = $formattedRate;
            }
        }

        return $result;
    }
}
