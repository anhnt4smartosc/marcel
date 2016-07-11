<?php

class Alex_Sales_Block_Adminhtml_Commit_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('commit_grid');
        $this->setDefaultSort('commit_id');
        $this->setDefaultDir('DESC');
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

    protected function _prepareCollection()
    {
        if(!$date = $this->getRequest()->getParam('date'))
        {
            $filterDate = Mage::getSingleton('core/date')->date('Y-m');
        } else {
            $filterDate = $date;
        }

        $collection = Mage::getModel('alexsales/commit')->getCollection();

        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('alexsales');

        $this->addColumn('commit_id', array(
            'header' => $helper->__('Id'),
            'width' => '100px',
            'index'  => 'commit_id'
        ));

        $this->addColumn('xpos_user_id', array(
            'header' => $helper->__('Cashier'),
            'type'   => 'options',
            'options' => $this->_getCashiers(),
            'index'  => 'xpos_user_id'
        ));

        $this->addColumn('balance', array(
            'type'   => 'number',
            'header' => $helper->__('Balance'),
            'index'  => 'balance'
        ));

        $this->addColumn('points', array(
            'type'   => 'number',
            'header' => $helper->__('Points'),
            'index'  => 'points'
        ));

        $this->addColumn('from_date', array(
            'type'   => 'date',
            'header' => $helper->__('From Date'),
            'index'  => 'time'
        ));

        $this->addExportType('*/*/exportInchooCsv', $helper->__('CSV'));
        $this->addExportType('*/*/exportInchooExcel', $helper->__('Excel XML'));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/index', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id'=>$row->getId()));
    }
}