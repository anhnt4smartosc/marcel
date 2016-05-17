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
class Magestore_Inventoryfulfillment_Block_Adminhtml_Fulfillment_Verifying_View_Items_Renderer_Default
    extends Magestore_Inventoryfulfillment_Block_Adminhtml_Order_View_Items_Renderer_Default
{
    /**
     * get total qty which we need to ship
     * 
     * @param type $item
     * @return float
     */
    public function getNeedToShipQty($item) {
        if(!$this->hasData('qty_to_ship'. $item->getId())){
            $qty = $this->helper('inventoryfulfillment')->getNeedToShipQty($item);
            $this->setData('qty_to_ship'. $item->getId(), $qty);
        }
        return $this->getData('qty_to_ship'. $item->getId());
    }
    
    /**
     * get total qty which we need to pick
     * 
     * @param type $item
     * @return float
     */
    public function getNeedToPickQty($item) {
        if(!$this->hasData('qty_to_pick'. $item->getId())){
            $qty = $this->helper('inventoryfulfillment')->getNeedToPickQty($item);
            $this->setData('qty_to_pick'. $item->getId(), $qty);
        }
        return $this->getData('qty_to_pick'. $item->getId());
    }    
    
    public function getWarehouseStockJson($item) {
        
    }
    
    /**
     * Get default qty to pick
     * 
     * @param type $item
     * @return float
     */
    public function getDefaultPickQty($item) {
        $warehouses = $this->helper('inventoryfulfillment')->getWarehouseQtys($item);
        $warehouseQty = 0;
        if(count($warehouses)){
            $warehouseQty = reset($warehouses);
        }
        $needToPickQty = $this->getNeedToPickQty($item);
        return min(max(0, $warehouseQty), $needToPickQty);
    }
}