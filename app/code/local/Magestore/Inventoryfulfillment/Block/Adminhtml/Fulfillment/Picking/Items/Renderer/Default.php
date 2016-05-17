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
class Magestore_Inventoryfulfillment_Block_Adminhtml_Fulfillment_Picking_Items_Renderer_Default
    extends Magestore_Inventoryfulfillment_Block_Adminhtml_Order_View_Items_Renderer_Default
{
    
    public function getPackages() {
        if(!$this->hasData('packages')) {
            $packages =  Mage::getModel('inventoryfulfillment/package')
                          ->getPackages($this->getOrder()->getId(), Magestore_Inventoryfulfillment_Model_Package::STATUS_PICKING, $this->helper('inventoryfulfillment')->getCurrWarehouseId());
            $this->setData('packages', $packages);
        }
        return $this->getData('packages');
    }  
    
    public function getWarehouse($warehouseId){
        if(!$this->hasData('warehouse'.$warehouseId)){
            $warehouse = Mage::getModel('inventoryplus/warehouse')->load($warehouseId);
            $this->setData('warehouse'.$warehouseId, $warehouse);
        }
        return $this->getData('warehouse'.$warehouseId);
    }
    
    public function getNeedToShipQty($item) {
        $needToShipQty = 0;
        $packages = $this->getPackageItems($item);
        if(count($packages)){
            foreach($packages as $package){
                $needToShipQty = $package->getNeedToPickQty();
            }
        }
        return $needToShipQty;
    }
    
    public function getPackageItems($item) {
        if(!$this->hasData('packageitems_'. $item->getId())) {
            $packages = $this->getPackages();
            $packageItems = array();
            if($item->getChildrenItems()){
                foreach($item->getChildrenItems() as $cItem){
                    foreach($packages as $package){
                        if($cItem->getId() == $package->getItemId()){
                            //$cItem->setData('pick_qty', $package->getQty());
                            //$cItem->setData('warehouse_id', $package->getWarehouseId());
                            $packageItems[$package->getId()] = $package;
                        }
                    }
                }
            } else {
                foreach($packages as $package){
                    if($item->getId() == $package->getItemId()){
                        //$item->setData('pick_qty', $package->getQty());
                        //$item->setData('warehouse_id', $package->getWarehouseId());
                        $packageItems[$package->getId()] = $package;
                    }
                }            
            }
            $this->setData('packageitems_'. $item->getId(), $packageItems);
        }
        return $this->getData('packageitems_'. $item->getId());
    }

}