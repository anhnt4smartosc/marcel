<?php

/**
 * SmartOSC Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 *
 * @category   SM
 * @package    SM_Barcode
 * @version    2.0
 * @author     hoadx@smartosc.com
 * @copyright  Copyright (c) 2010-2011 SmartOSC Co. (http://www.smartosc.com)
 */
class SM_Barcode_Block_Adminhtml_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_defaultLimit = 999999999;
    private $_orderId = 1;

    public function __construct()
    {
        parent::__construct();
        $this->setId('barcodeOrderProductsGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->setPagerVisibility(false);
    }

    protected function _prepareCollection()
    {
        if ($this->getRequest()->getParam('order_id')) {
            $order_id = $this->getRequest()->getParam('order_id');

        } else {
            $order_id = 0;
        }

        $order = Mage::getModel('sales/order')->loadByIncrementID($order_id);
        if ($order->getId()) {
            $orderId = intval($order->getId());
        } else {
            $orderId = 99999999;
        }
        $this->_orderId = $order;
        $collection = Mage::getModel('sales/order_item')->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('product_type', 'simple');
        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('valid',
            array(
                'header' => Mage::helper('barcode')->__(''),
                'width' => '50px',
                'renderer' => 'barcode/adminhtml_order_grid_renderer_valid'
            ));
        $this->addColumn('product_id',
            array(
                'header' => Mage::helper('barcode')->__('Product ID'),
                'type' => 'number',
                'index' => 'product_id',
            ));
        $this->addColumn('name',
            array(
                'header' => Mage::helper('barcode')->__('Product Name'),
                'index' => 'name',
            ));
        $this->addColumn('qty_ordered',
            array(
                'header' => Mage::helper('barcode')->__('Qty Ordered'),
                'width' => '50px',
                'renderer' => 'barcode/adminhtml_order_grid_renderer_ordered',
                'index' => 'qty_ordered',
            ));
        $this->addColumn('qty_shipped',
            array(
                'header' => Mage::helper('barcode')->__('Qty Shipped'),
                'width' => '50px',
                'renderer' => 'barcode/adminhtml_order_grid_renderer_shipped',
                'index' => 'qty_shipped',
            ));
        $this->addColumn('qty_scanned',
            array(
                'header' => Mage::helper('barcode')->__('Qty Scanned'),
                'width' => '50px',
                'filter' => false,
                'sortable' => false,
                'renderer' => 'barcode/adminhtml_order_grid_renderer_scanned',
            ));

        /*
         * XBAR-213
         * Temporarily remove
         *
        $this->addColumn('check',
            array(
                'header'=> Mage::helper('barcode')->__('Manual Scan'),
                'width' => '110px',
                'filter' => false,
                'renderer' => 'barcode/adminhtml_order_grid_renderer_noscan',
            ));
        */

        $store = $this->_getStore();

        /*$this->addColumn('action',
            array(
                'header'    => Mage::helper('catalog')->__('Action'),
                'width'     => '100px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
					array(
                        'caption' => Mage::helper('catalog')->__('Print Barcode'),
                        'id' => "printbarcode",
                        'url'     => array(
                            'base'=>'barcode/adminhtml_print/show',
                            'params'=>array(
								'store'=>$this->getRequest()->getParam('store'),
								//'required' => $this->_getRequiredAttributesIds(),
								'popup'    => 1,
								//'product'  => $this->_getProduct()->getId()
							)
                        ),
						'onclick'  => 'window.open(this.href, "_blank","toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, copyhistory=no, width=800, height=600, left=20, top=20"); return false;',
                        'field'   => 'id',
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
        ));*/

        return parent::_prepareColumns();
    }

    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    public function getRowId($row)
    {
        return parent::getRowId($row); // TODO: Change the autogenerated stub
    }
}
