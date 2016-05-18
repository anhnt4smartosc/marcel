<?php
/**
 * Created by PhpStorm.
 * User: sonbv
 * Date: 27/08/2015
 * Time: 14:04
 */
/*
 * @TODO need refactor this class
 * */
class SM_Barcode_Helper_Product_Barcode extends Mage_Core_Helper_Abstract implements SM_Warehouse_Helper_IBarcode
{

    protected $_cacheData = array();

    function __construct()
    {
        $this->_initData();
    }

    public function getProductIdFromBarcode($barcode)
    {
        return isset($this->_cacheData[$barcode]) ? $this->_cacheData[$barcode] : false;
    }

    protected function _initData()
    {
        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->addAttributeToSelect('sm_barcode');
        $collection->addAttributeToFilter('sm_barcode', array('notnull' => true));

        foreach ($collection as $product) {
            if ($product->hasSmBarcode()) $this->_cacheData[$product->getSmBarcode()] = $product->getId();
        }
    }

}