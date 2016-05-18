<?php

class SM_Barcode_Block_Adminhtml_Label_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {

        parent::__construct();
        $this->setId('productGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
        $this->setVarNameFilter('product_filter');

    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id')
            ->joinField('qty',
                'cataloginventory/stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');

        $collection->addAttributeToSelect('price');
        $collection->addAttributeToSelect('status');
        $collection->addAttributeToSelect('visibility');
        //$collection->addFieldToFilter('type_id','simple');
        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField('websites',
                    'catalog/product_website',
                    'website_id',
                    'product_id=entity_id',
                    null,
                    'left');
            }
        }
        return parent::_addColumnFilterToCollection($column);
    }




    protected function _prepareColumns()
    {
        $this->addColumn('entity_id',
            array(
                'header' => Mage::helper('catalog')->__('ID'),
                'width' => '50px',
                'type' => 'number',
                'index' => 'entity_id',
            ));
        $this->addColumn('name',
            array(
                'header' => Mage::helper('catalog')->__('Name'),
                'index' => 'name',
            ));

        $store = $this->_getStore();
        if ($store->getId()) {
            $this->addColumn('custom_name',
                array(
                    'header' => Mage::helper('catalog')->__('Name in %s', $store->getName()),
                    'index' => 'custom_name',
                ));
        }

        $this->addColumn('type',
            array(
                'header' => Mage::helper('catalog')->__('Type'),
                'width' => '60px',
                'index' => 'type_id',
                'type' => 'options',
                'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
            ));

        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();

        $this->addColumn('set_name',
            array(
                'header' => Mage::helper('catalog')->__('Attrib. Set Name'),
                'width' => '100px',
                'index' => 'attribute_set_id',
                'type' => 'options',
                'options' => $sets,
            ));

        $this->addColumn('sku',
            array(
                'header' => Mage::helper('catalog')->__('SKU'),
                'width' => '80px',
                'index' => 'sku',
            ));

        $store = $this->_getStore();
        $this->addColumn('price',
            array(
                'header' => Mage::helper('catalog')->__('Price'),
                'type' => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'price',
            ));

        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $this->addColumn('qty',
                array(
                    'header' => Mage::helper('catalog')->__('Qty'),
                    'width' => '100px',
                    'type' => 'number',
                    'index' => 'qty',
                ));
        }

        $this->addColumn('visibility',
            array(
                'header' => Mage::helper('catalog')->__('Visibility'),
                'width' => '70px',
                'index' => 'visibility',
                'type' => 'options',
                'options' => Mage::getModel('catalog/product_visibility')->getOptionArray(),
            ));

        $this->addColumn('status',
            array(
                'header' => Mage::helper('catalog')->__('Status'),
                'width' => '70px',
                'index' => 'status',
                'type' => 'options',
                'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
            ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('websites',
                array(
                    'header' => Mage::helper('catalog')->__('Websites'),
                    'width' => '100px',
                    'sortable' => false,
                    'index' => 'websites',
                    'type' => 'options',
                    'options' => Mage::getModel('core/website')->getCollection()->toOptionHash(),
                ));
        }

        $this->addColumn('sm_barcode',
            array(
                'header'=> Mage::helper('catalog')->__('Barcode'),
                'width' => '50px',
                'index' => 'sm_barcode',
                'renderer' => 'barcode/adminhtml_catalog_product_render_barcode'
            ));

        $this->addColumn('action',
            array(
                'header' => Mage::helper('catalog')->__('Action'),
                'width' => '50px',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('catalog')->__('Edit'),
                        'url' => array(
                            'base' => '*/catalog_product/edit',
                            'params' => array('store' => $this->getRequest()->getParam('store'))
                        ),
                        'field' => 'id'
                    )
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
            ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('product');

        $this->getMassactionBlock()->addItem('printbarcode', array(
            'label' => Mage::helper('catalog')->__('Print Barcode Labels'),
            'url' => $this->getUrl('adminhtml/barcode_product/index', array('_current' => true))
        ));

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }
}
