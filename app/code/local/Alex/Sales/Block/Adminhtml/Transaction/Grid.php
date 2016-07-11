<?php

class Alex_Sales_Block_Adminhtml_Transaction_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('transaction_grid');
        $this->setDefaultSort('transaction_id');
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('alexsales/transaction')->getCollection();

        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _getCashiers()
    {
        $cashiers = Mage::getModel('xpos/user')->getCollection();

        $result = array();
        foreach($cashiers as $cashier) {
            $result[$cashier->getId()] = $cashier->getName();
        }
        return $result;
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('alexsales');

        $this->addColumn('transaction_id', array(
            'header' => $helper->__('Id'),
            'width' => '100px',
            'index'  => 'transaction_id'
        ));

        $this->addColumn('type', array(
            'header' => $helper->__('Type'),
            'type'   => 'options',
            'options' => Mage::getModel('alexsales/transaction')->getAllTypeOptions(),
            'index'  => 'type'
        ));

        $this->addColumn('comment', array(
            'header' => $helper->__('Comment'),
            'index'  => 'comment'
        ));

        $this->addColumn('xpos_user_id', array(
            'type'   => 'options',
            'header' => $helper->__('User'),
            'index'  => 'xpos_user_id',
            'options' => $this->_getCashiers()
        ));

        $this->addColumn('points', array(
            'type'   => 'number',
            'header' => $helper->__('Points'),
            'index'  => 'points'
        ));

        $this->addColumn('order_id', array(
            'header' => $helper->__('Order'),
            'index'  => 'order_id'
        ));

        $this->addColumn('create_time', array(
            'type'   => 'date',
            'header' => $helper->__('Created Date'),
            'index'  => 'created_time'
        ));

        $this->addExportType('*/*/exportInchooCsv', $helper->__('CSV'));
        $this->addExportType('*/*/exportInchooExcel', $helper->__('Excel XML'));

        return parent::_prepareColumns();
    }
}