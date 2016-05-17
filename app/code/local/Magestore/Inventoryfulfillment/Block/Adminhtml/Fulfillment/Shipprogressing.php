<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Inventoryfulfillment
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventoryfulfillment Edit Block
 *
 * @category     Magestore
 * @package     Magestore_Inventoryfulfillment
 * @author      Magestore Developer
 */
class Magestore_Inventoryfulfillment_Block_Adminhtml_Fulfillment_Shipprogressing extends Magestore_Inventoryfulfillment_Block_Adminhtml_Gridabstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_order_grid');
        $this->setDefaultSort('order_created_at');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->setTag('shipprogressing');
    }

    /**
     * prepare collection for block to display
     *
     * @return Magestore_Inventoryshipment_Block_Adminhtml_Inventoryshipment_Grid
     */

    protected function _prepareCollection()
    {
        $resource = Mage::getSingleton('core/resource');
        $collection = Mage::getResourceModel('sales/order_grid_collection');
        $collection->getSelect()
            ->joinLeft(array('order' => $resource->getTableName('sales/order')), 'main_table.entity_id=order.entity_id', array('shipping_progress', 'order_created_at' => 'created_at', 'order_store_id' => 'store_id', 'order_base_grand_total' => 'base_grand_total', 'order_grand_total' => 'grand_total'))
            ->where('order.fulfillment_status = ' . Magestore_Inventoryfulfillment_Model_Order_Fulfillmentstatus::STATUS_SHIPPING)
            ->group('main_table.entity_id');
        $this->setCollection($collection);
        try {
            parent::_prepareCollection();
        } catch (Exception $e) {

        }
        return $this;
    }


    /**
     * prepare columns for this grid
     *
     * @return Magestore_Inventoryshipment_Block_Adminhtml_Inventoryshipment_Grid
     */

    protected function _prepareColumns()
    {

        $this->addColumn('real_order_id', array(
            'header' => Mage::helper('sales')->__('Order #'),
            'width' => '80px',
            'align' => 'right',
            'type' => 'text',
            'index' => 'increment_id',
            'filter_condition_callback' => array($this, '_filterTextCallback')
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('order_store_id', array(
                'header' => Mage::helper('sales')->__('Purchased From (Store)'),
                'index' => 'order_store_id',
                'type' => 'store',
                'align' => 'left',
                'store_view' => true,
                'display_deleted' => true,
                'filter_condition_callback' => array($this, '_filterStoreCallBack')
            ));
        }

        $this->addColumn('order_created_at', array(
            'header' => Mage::helper('sales')->__('Purchased On'),
            'index' => 'order_created_at',
            'type' => 'datetime',
            'align' => 'right',
            'width' => '100px',
            'filter_condition_callback' => array($this, '_filterDateCallback')
        ));

        $this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'align' => 'left',
            'index' => 'billing_name',
        ));

        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'align' => 'left',
            'index' => 'shipping_name',
        ));

        $this->addColumn('order_base_grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Base)'),
            'index' => 'order_base_grand_total',
            'type' => 'currency',
            'align' => 'right',
            'currency' => 'base_currency_code',
            'filter_condition_callback' => array($this, '_filterNumberCallback')
        ));

        $this->addColumn('order_grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'order_grand_total',
            'type' => 'currency',
            'align' => 'right',
            'currency' => 'order_currency_code',
            'filter_condition_callback' => array($this, '_filterNumberCallback')
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Order Status'),
            'index' => 'status',
            'type' => 'options',
            'align' => 'left',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
            'filter_index' => 'main_table.status',
        ));

        $this->addColumn('shipping_progress', array(
            'header' => Mage::helper('sales')->__('Shipping Progress'),
            'width' => '70px',
            'type' => 'options',
            'align' => 'center',
            'options' => array(
                0 => Mage::helper('inventoryshipment')->__('Not shipped'),
                1 => Mage::helper('inventoryshipment')->__('Partially shipped'),
                2 => Mage::helper('inventoryshipment')->__('Complete'),
                //3 => Mage::helper('inventoryshipment')->__('Canceled'),
                //4 => Mage::helper('inventoryshipment')->__('Closed')
            ),
            'sortable' => false,
            'index' => 'shipping_progress',
            'filter_index' => 'order.shipping_progress',
            'renderer' => 'inventoryfulfillment/adminhtml_fulfillment_shipprogressing_renderer_shipping'
        ));

        $this->addColumn('names', array(
            'header' => Mage::helper('sales')->__('Warehouses Shipped'),
            'index' => 'names',
            'align' => 'left',
            'filter_index' => 'inventory_shipment.warehouse_name',
            'type' => 'options',
            'options' => Mage::helper('inventoryshipment')->getAllWarehouseName(),
            'filter_condition_callback' => array($this, 'filterCallback'),
            'sortable' => false,
            'renderer' => 'inventoryfulfillment/adminhtml_fulfillment_shipprogressing_renderer_warehouse'
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('inventoryshipment')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('inventoryshipment')->__('XML'));

        return parent::_prepareColumns();
    }

    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row)
    {
//        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid');
    }

    protected function _getRealFieldFromAlias($alias)
    {
        $field = null;
        switch ($alias) {
            case 'order_base_grand_total':
                $field = 'order.base_grand_total';
                break;
            case 'order_grand_total':
                $field = 'order.grand_total';
                break;
            case 'order_created_at':
                $field = 'order.created_at';
                break;
            case 'increment_id':
                $field = 'order.increment_id';
                break;
            case 'order_store_id':
                $field = 'order.store_id';
                break;
            default:
                $field = $alias;
        }
        return $field;
    }

}