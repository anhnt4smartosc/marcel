<?php
/**
 * Created by JetBrains PhpStorm.
 * User: thangnv@smartosc.com
 * Date: 6/4/13
 * Time: 3:05 PM
 * To change this template use File | Settings | File Templates.
 */

class SM_Barcode_Block_Adminhtml_Countinventory_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    public function __construct(){
        parent::__construct();
        $this->setId('barcodeCountinventoryGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);
        $this->setVarNameFilter('product_filter');
        $this->setTemplate('sm/barcode/countinventorygrid.phtml');
    }

    protected function _getStore(){
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    public function _prepareCollection(){
        $collection = Mage::getModel('catalog/product')->getCollection();

        $resource = Mage::getSingleton('core/resource');
        $tableName      = $resource->getTableName('sm_xbar_countinventory');

        $select = $collection->getSelect();
        $select->join(array(
            'countinventory' => $tableName),
            'e.entity_id = countinventory.product_id',array('countinventory.*'));

        // Get product quantity and store in $_SESSION
        $collection->addAttributeToSelect('sku')
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
        $collection->addAttributeToSelect('type_id','simple');

        Mage::getModel('barcode/countinventory')->getProductIdsToSession();

        // Add filter to grid
        $productIdsArray = Mage::getModel('barcode/countinventory')->getCountInventData();
        if(!empty($productIdsArray) && is_array($productIdsArray)){
            $collection->addAttributeToFilter('entity_id',array('IN',$productIdsArray));
        } else{
            $collection->addAttributeToFilter('entity_id',array('IN',array('0')));
        }

        // Set collection
        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

        protected function _addColumnFilterToCollection($column) {
        if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField('websites',
                    'catalog/product_website',
                    'website_id',
                    'product_id=entity_id',
                    null,
                    'left');
            }
            // Set filter for renderer column (Scanned Quantity column)
            if ($column->getFilterConditionCallback()) {
                call_user_func($column->getFilterConditionCallback(), $this->getCollection(), $column);
                return parent::_addColumnFilterToCollection($column);
            } else {
                $cond = $column->getFilter()->getCondition();
                $select = $this->getCollection()->getSelect();
                if ($column->getId() == 'scanned_qty') {
                    if (!empty($cond['from']) && !empty($cond['to'])) {
                        $select->where('countinventory.scanned_qty >= '.$cond['from'].' AND countinventory.scanned_qty <= '.$cond['to'].'');
                    } elseif(!empty($cond['from']) && empty($cond['to'])) {
                        $select->where('countinventory.scanned_qty >= '.$cond['from'].'');
                    } elseif(empty($cond['from']) && !empty($cond['to'])) {
                        $select->where('countinventory.scanned_qty <= '.$cond['to'].'');
                    }
                    return $this;
                }
                else {
                    return parent::_addColumnFilterToCollection($column);
                }
            }
        }
    }

    protected function _prepareColumns(){
        $this->addColumn('entity_id',
            array(
                'header' => Mage::helper('catalog')->__('Product ID'),
                'width'  => '50px',
                'type'   => 'number',
                'index'  => 'entity_id',
            )
        );
        $this->addColumn('name',
            array(
                'header' => Mage::helper('catalog')->__('Product Name'),
                'index'  => 'name',
            )
        );
        $this->addColumn('sku',
            array(
                'header' => Mage::helper('catalog')->__('SKU'),
                'width' => '80px',
                'index' => 'sku',
        ));

        $store = $this->_getStore();
        if ($store->getId()) {
            $this->addColumn('custom_name',
                array(
                    'header' => Mage::helper('catalog')->__('Name in %s', $store->getName()),
                    'index'  => 'custom_name',
                )
            );
        }

        $this->addColumn('qty',
            array(
                'header'    => Mage::helper('catalog')->__('Current Qty'),
                'width'     => '30px',
                'renderer'  => 'barcode/adminhtml_countinventory_grid_renderer_currentinventory',
                'type'      => 'number',
                'index'     => 'qty',
                'sortable'  => false
            )
        );

        $this->addColumn('scanned_qty',
            array(
                'header'    => Mage::helper('barcode')->__('Qty Scanned'),
                'width'     => '30px',
                'renderer'  => 'barcode/adminhtml_countinventory_grid_renderer_qtyscanned',
                'type'      => 'number',
                'index'     => 'scanned_qty',
                'sortable'  => false
            )
        );

        //If MultiWare Extension is not available, then this column is not displayed
        if(Mage::getStoreConfig('xwarehouse/general/enabled') == 1){
            $this->addColumn('warehouse',
                array(
                    'header'    => Mage::helper('catalog')->__('Warehouse'),
                    'width'     => '30px',
                    'renderer'  => 'barcode/adminhtml_countinventory_grid_renderer_warehouse',
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'warehouse',
                )
            );
        }

        $this->addColumn('functionalities',
            array(
                'width'     => '220px',
                'renderer'  => 'barcode/adminhtml_countinventory_grid_renderer_functionalities',
                'type'      => 'action',
                'filter'    => false,
                'sortable'  => false
            )
        );
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('product');
        $this->getMassactionBlock()->setTemplate('sm/barcode/countinventorymassaction.phtml');

        $this->getMassactionBlock()->addItem('correctall', array(
            'label' => Mage::helper('catalog')->__('Correct all selected item'),
        ));
        $this->getMassactionBlock()->addItem('plusall', array(
            'label' => Mage::helper('catalog')->__('Plus all selected item'),
        ));
        $this->getMassactionBlock()->addItem('reduceall', array(
            'label' => Mage::helper('catalog')->__('Reduce all selected item'),
        ));
        $this->getMassactionBlock()->addItem('deleteall', array(
            'label' => Mage::helper('catalog')->__('Remove all selected item'),
        ));

        return $this;
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    public function getRowUrl($row){
        //return $this->getUrl('*/catalog_product/edit', array('id'=>$row->getId()));
        return '';
    }
}