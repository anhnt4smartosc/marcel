<?php
/**
 * Date: 1/11/13
 * Time: 4:13 PM
 */

class SM_Barcode_Block_Adminhtml_Stock_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    var $product = null;

    public function __construct(){
        parent::__construct();
        $this->setId('barcodeStockProductsGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        if($this->getRequest()->getParam('product_id')){
            $product_id = $this->getRequest()->getParam('product_id');
        }
        else{
            $product_id = 0;
        }

        $product = Mage::getModel('catalog/product')->load($product_id);
        if($product->getId()){
            $product_id = intval($product->getId());
        }
        else{
            $product_id = 99999999;
        }
        $this->product = $product;
        $store = $this->_getStore();


        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id')
            ->addFieldToFilter('entity_id', $product_id);


        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $collection->joinField('qty',
                'cataloginventory/stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');
        }
        if ($store->getId()) {
            //$collection->setStoreId($store->getId());
            $adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
            $collection->addStoreFilter($store);
            $collection->joinAttribute(
                'name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $adminStore
            );
            $collection->joinAttribute(
                'custom_name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'status',
                'catalog_product/status',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'visibility',
                'catalog_product/visibility',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'price',
                'catalog_product/price',
                'entity_id',
                null,
                'left',
                $store->getId()
            );
        }
        else {
            $collection->addAttributeToSelect('price');
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        }

        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns() {
        $this->addColumn('entity_id',
            array(
                'header'=> Mage::helper('barcode')->__('Product ID'),
                'type'  => 'number',
                'index' => 'entity_id',
                'filter' => false,
            ));
        $this->addColumn('name',
            array(
                'header'=> Mage::helper('barcode')->__('Product Name'),
                'index' => 'name',
                'filter' => false,
            ));
        $this->addColumn('sku',
            array(
                'header'=> Mage::helper('catalog')->__('SKU'),
                'index' => 'sku',
                'filter' => false,
            ));
        $this->addColumn('qty',
            array(
                'header'=> Mage::helper('barcode')->__('Quantity'),
                'index' => 'qty',
                'type'  => 'number',
                'width' => '100px',
                'renderer' => 'barcode/adminhtml_stock_grid_qty',
                'filter' => false,
            ));


        return parent::_prepareColumns();
    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}