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
 * @package     Magestore_Inventorypurchasing
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorypurchasing Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_Inventorylowstock
 * @author      Magestore Developer
 */
class Magestore_Inventorylowstock_Adminhtml_Inl_LowstockController extends Magestore_Inventoryplus_Controller_Action {
    
    /**
     * init layout and set active for current menu
     *
     * @return Magestore_Inventorylowstock_Adminhtml_NotificationlogController
     */
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('inventoryplus/stock_onhand/lowstock_list')
                ->_addBreadcrumb(
                        Mage::helper('adminhtml')->__('Low Stock Listing'), Mage::helper('adminhtml')->__('Low Stock Listing')
        );
        $this->_title($this->__('Inventory'))
             ->_title($this->__('Low Stock Listing'));
        return $this;
    }
    

    /**
     * index action
     */
    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }

    /**
     * Grid action
     */
    public function gridAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * export grid item to CSV type
     */
    public function exportCsvAction() {
        $fileName = 'lowstocks.csv';
        $content = $this->getLayout()
                ->createBlock('inventorylowstock/adminhtml_lowstock_grid')
                ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export grid item to XML type
     */
    public function exportXmlAction() {
        $fileName = 'lowstocks.xml';
        $content = $this->getLayout()
                ->createBlock('inventorylowstock/adminhtml_lowstock_grid')
                ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('inventoryplus/stock_onhand/lowstock_list');
    }    
}
