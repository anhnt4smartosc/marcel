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
 * Inventory Supplier Edit Form Content Tab Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */

class Magestore_Inventorypurchasing_Block_Adminhtml_Purchaseorder_Edit_Tab_Renderer_Delivery_Warehouse
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row) 
    {
        $columnName = $this->getColumn()->getName();
        $columnName = explode('_',$columnName);
        if($columnName[1]){
            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');
            $installer = Mage::getModel('core/resource');
            $warehouseId = $columnName[1];
            $purchase_order_id = $this->getRequest()->getParam('id');
            $sql = 'SELECT qty_delivery,product_id,warehouse_id from '.$installer->getTableName("erp_inventory_delivery_warehouse").' WHERE (purchase_order_id = '.$purchase_order_id.') AND (product_id = '.$row->getProductId().') AND (warehouse_id = '.$warehouseId.') AND (sametime = '.$row->getSametime().')';
            $results = $readConnection->fetchAll($sql);
            
            /* get location product*/
            $location=Mage::helper('inventoryfulfillment')->getProductLocation($warehouseId,$row->getProductId());
            if(!$location){
                $location = $this->__('N/A Location');
            }
               
            
            $haveDelivery = 0;
            foreach($results as $result){
                if($result['qty_delivery']){
                    $haveDelivery = 1;
                    $qty = $result['qty_delivery'];
                }
                $content = "".$qty."<br><span title='Product Location'>(".$location.")</span>";
                return $content;
            }
            if($haveDelivery == '0')
                $content1 = "0<br><span title='Product Location'>(".$location.")</span>";
                return $content1;
        }else{
            return parent::render($row);
        }
    }
}