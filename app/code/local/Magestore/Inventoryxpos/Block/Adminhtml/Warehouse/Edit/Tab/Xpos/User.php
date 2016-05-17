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
 * @package     Magestore_Inventory
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Warehouse Edit Form Content Tab Block
 *
 * @category    Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */
class Magestore_Inventoryxpos_Block_Adminhtml_Warehouse_Edit_Tab_Xpos_User extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('xposUserGrid');
        $this->setDefaultSort('xpos_user_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * prepare collection for block to display
     *
     * @return Magestore_Inventoryxpos_Block_Adminhtml_Warehouse_Edit_Tab_Xpos_User
     */
    protected function _prepareCollection() {
        $collection = Mage::getModel('xpos/user')->getCollection();
        $collection->getSelect()
            ->joinLeft(array('warehouse_xpos_user' => $collection->getTable('inventoryxpos/xposuser')), "main_table.xpos_user_id = warehouse_xpos_user.xpos_user_id", array('id' => 'main_table.xpos_user_id', 'warehouse_id'));
        $collection->getSelect()
            ->joinLeft(array('warehouse' => $collection->getTable('inventoryplus/warehouse')), "warehouse_xpos_user.warehouse_id = warehouse.warehouse_id", array('warehouse_name'));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare columns for this grid
     *
     * @return Magestore_Inventoryxpos_Block_Adminhtml_Warehouse_Edit_Tab_Xpos_User
     * @throws Exception
     */
    protected function _prepareColumns() {
        $this->addColumn('xpos_user', array(
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'xpos_user_id',
            'field_name' => 'xpos_user[]',
            'values' => $this->_getSelectedXposUsers(),
            'align' => 'center',
            'index' => 'id',
            'disabled_values' => array()
        ));
        $this->addColumn('xpos_user_id', array(
            'header' => Mage::helper('inventoryxpos')->__('ID'),
            'align' => 'center',
            'width' => '100px',
            'index' => 'id',
        ));
        $this->addColumn('username', array(
            'header' => Mage::helper('inventoryxpos')->__('User Name'),
            'align' => 'left',
            'index' => 'username',
        ));
        $this->addColumn('email', array(
            'header' => Mage::helper('inventoryxpos')->__('Email'),
            'align' => 'left',
            'index' => 'email',
        ));
        $this->addColumn('warehouse', array(
            'header' => Mage::helper('inventoryxpos')->__('Warehouse'),
            'align' => 'left',
            'index' => 'warehouse_name',
        ));
        $this->addColumn('is_active', array(
            'header' => Mage::helper('inventoryxpos')->__('Status'),
            'align' => 'left',
            'index' => 'is_active',
            'type' => 'options',
            'options' => array(
                1 => 'Enable',
                2 => 'Disable',
            ),
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('inventoryxpos')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('inventoryxpos')->__('XML'));
        return parent::_prepareColumns();
    }

    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row) {
        return false;
    }

    public function _getSelectedXposUsers() {
        $warehouseId = $this->getRequest()->getParam('id');
        if (!isset($warehouseId)) {
            $warehouseId = 0;
        }

        $warehouseXposUsers = Mage::getModel('inventoryxpos/xposuser')->getCollection()
            ->addFieldToFilter('warehouse_id', $warehouseId);
        $selectedXposUsers = array();
        foreach ($warehouseXposUsers as $xposUser) {
            $selectedXposUsers[] = $xposUser->getXposUserId();
        }

        return $selectedXposUsers;
    }

}
