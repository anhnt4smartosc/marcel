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
 * @package     Magestore_Inventorydropship
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorydropship Grid Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventorydropship
 * @author      Magestore Developer
 */
class Magestore_Inventorydropship_Block_Adminhtml_Inventorydropship_Grid extends Magestore_Inventoryplus_Block_Adminhtml_Gridabstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('inventorydropshipGrid');
        $this->setDefaultSort('dropship_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }
    
    /**
     * prepare collection for block to display
     *
     * @return Magestore_Inventorydropship_Block_Adminhtml_Inventorydropship_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('inventorydropship/inventorydropship')->getCollection();
        $collection->getSelect()->columns(array(
            'period_length' => new Zend_Db_Expr('TIMEDIFF(NOW(), main_table.created_on)'),
        ));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare columns for this grid
     *
     * @return Magestore_Inventorydropship_Block_Adminhtml_Inventorydropship_Grid
     */
    protected function _prepareColumns()
    {
//        $this->addColumn('dropship_id', array(
//            'header'    => Mage::helper('inventorydropship')->__('ID'),
//            'align'     =>'center',
//            'width'     => '50px',
//            'index'     => 'dropship_id',
//        ));

        $this->addColumn('increment_id', array(
            'header_css_class' => 'a-center',
            'header'    => Mage::helper('inventorydropship')->__('Order ID'),
            'align'     => 'center',
            'width'     => '50px',
            'index'     => 'increment_id',
            'renderer'  => 'inventorydropship/adminhtml_inventorydropship_renderer_order',
        ));

        $this->addColumn('supplier_name', array(
            'header_css_class' => 'a-center',
            'header'    => Mage::helper('inventorydropship')->__('Supplier Name'),
            'align'     => 'center',
            'width'     => '50px',
            'index'     => 'supplier_name',
            'renderer'  => 'inventorydropship/adminhtml_inventorydropship_renderer_supplier',
        ));

        $this->addColumn('shipping_name', array(
            'header_css_class' => 'a-center',
            'header'    => Mage::helper('inventorydropship')->__('Recipient'),
            'align'     => 'center',
            'width'     => '50px',
            'index'     => 'shipping_name',
        ));

        $this->addColumn('created_on', array(
            'header_css_class' => 'a-center',
            'header'    => Mage::helper('inventorydropship')->__('Created At'),
            'align'     => 'center',
            'width'     => '100px',
            'type'      => 'date',
            'format'    => 'd/M/Y h:m',
            'index'     => 'created_on',
        ));

        $this->addColumn('admin_name', array(
            'header_css_class' => 'a-center',
            'header'    => Mage::helper('inventorydropship')->__('Admin Name'),
            'align'     => 'center',
            'width'     => '50px',
            'index'     => 'admin_name',
        ));

        $this->addColumn('period_length', array(
            'header_css_class' => 'a-center',
            'header'    => Mage::helper('inventorydropship')->__('Outstanding Period'),
            'align'     => 'center',
            'width'     => '20px',
            'index'     => 'period_length',
        ));

        $this->addColumn('status', array(
            'header_css_class' => 'a-center',
            'header'    => Mage::helper('inventorydropship')->__('Status'),
            'align'     => 'center',
            'width'     => '80px',
            'index'     => 'status',
            'type'        => 'options',
            'options' => Mage::getSingleton('inventorydropship/status')->getOptionArray(),
        ));

        $this->addColumn('action',
            array(
                'header_css_class' => 'a-center',
                'header'    =>    Mage::helper('inventorydropship')->__('Action'),
                'align'     => 'center',
                'width'        => '50px',
                'type'        => 'action',
                'getter'    => 'getId',
                'actions'    => array(
                    array(
                        'caption'    => Mage::helper('inventorydropship')->__('Edit'),
                        'url'        => array('base'=> '*/*/edit'),
                        'field'        => 'id'
                    )),
                'filter'    => false,
                'sortable'    => false,
                'index'        => 'stores',
                'is_system'    => true,
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('inventorydropship')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('inventorydropship')->__('XML'));

        return parent::_prepareColumns();
    }
    
    /**
     * prepare mass action for this grid
     *
     * @return Magestore_Inventorydropship_Block_Adminhtml_Inventorydropship_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('dropship_id');
        $this->getMassactionBlock()->setFormFieldName('inventorydropship');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'        => Mage::helper('inventorydropship')->__('Delete'),
            'url'        => $this->getUrl('*/*/massDelete'),
            'confirm'    => Mage::helper('inventorydropship')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('inventorydropship/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
            'label'=> Mage::helper('inventorydropship')->__('Change status'),
            'url'    => $this->getUrl('*/*/massStatus', array('_current'=>true)),
            'additional' => array(
                'visibility' => array(
                    'name'    => 'status',
                    'type'    => 'select',
                    'class'    => 'required-entry',
                    'label'    => Mage::helper('inventorydropship')->__('Status'),
                    'values'=> $statuses
                ))
        ));
        return $this;
    }
    
    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid');
    }

    protected function _getRealFieldFromAlias($alias) {
        $field = null;
        switch ($alias) {
            default:
                $field = $alias;
        }
        if($field != $alias){
            $field = new Zend_Db_Expr($field);
        }
        return $field;
    }
}