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
 * Inventoryfulfillment Adminhtml Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryfulfillment
 * @author      Magestore Developer
 */
class Magestore_Inventoryfulfillment_Block_Adminhtml_Js extends Mage_Adminhtml_Block_Template
{
    protected function _prepareLayout() {
        parent::_prepareLayout();
        $this->setTemplate('inventoryfulfillment/js.phtml');
    }

    public function getPickOrderUrl() {
        return $this->getUrl('adminhtml/inf_orderverifying/pick',  array(
                '_current' => true,
                '_secure' => true,
            ));
    }
    
    public function getPackOrderUrl() {
        return $this->getUrl('adminhtml/inf_orderpicking/pack',  array(
                '_current' => true,
                '_secure' => true,
            ));        
    }
    
    public function getShipOrderUrl() {
        return $this->getUrl('adminhtml/inf_orderpacking/ship',  array(
                '_current' => true,
                '_secure' => true,
            ));           
    }
    
    public function getShipPackagesUrl() {
        return $this->getUrl('adminhtml/inf_ordershipping/ship',  array(
                '_current' => true,
                '_secure' => true,
            ));          
    }
    
    public function getPrintOrderListUrl() {
        return $this->getUrl('adminhtml/inf_ordershipping/printorderlist',  array(
                '_current' => true,
                '_secure' => true,
            ));            
    }
    
    public function getCheckGlobalStockUrl() {
        return $this->getUrl('adminhtml/inf_fulfillment/checkgstock',  array(
                '_current' => true,
                '_secure' => true,
            ));         
    }
    
    public function getRefreshWarehouseListUrl() {
        return $this->getUrl('adminhtml/inf_fulfillment/refreshwarehouselist',  array(
                '_current' => true,
                '_secure' => true,
            ));            
    }
    
    public function getCancelUrl() {
        return $this->getUrl('adminhtml/inf_orderverifying/cancel',  array(
                '_current' => true,
                '_secure' => true,
            ));            
    }    
    
    public function getGridObject() {
        if($gridId = $this->helper('inventoryfulfillment')->getCurrGridId()){
            return $gridId .'JsObject';
        }
        return null;
    }
    
    public function getOrderErrorMessage() {
        return $this->__('An error has occurred when processing this order. Please try again.');
    }
    
    public function getUnCheckItemMessage() {
        return $this->__('Some ordered items are missing from the Picking/ PackingList. Please check again.');
    }
    
    public function getShipOrderConfirmMessage() {
        return $this->__('Are you sure to ship these packages?');
    }
    
    public function getCheckGlobalStockErrorMessage() {
        return $this->__('An error has occurred when checking stock. Please try again.');
    }
    
    public function getRefreshWarehouseListErrorMessage() {
        return $this->__('An error has occurred when refreshing warehouse list. Please try again!');
    }   
    
    public function getConfirmOrderMessage() {
        return $this->__('Are you sure that you want to process this action? It cannot be undone.');
    }
}