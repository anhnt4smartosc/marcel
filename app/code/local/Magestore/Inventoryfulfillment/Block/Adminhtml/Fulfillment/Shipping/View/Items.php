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
 * Inventoryfulfillment Block
 * 
 * @category     Magestore
 * @package     Magestore_Inventoryfulfillment
 * @author      Magestore Developer
 */
class Magestore_Inventoryfulfillment_Block_Adminhtml_Fulfillment_Shipping_View_Items
    extends Mage_Adminhtml_Block_Sales_Order_Shipment_View_Items
{
    
    protected $_extendColumns = array();
    
    protected function _prepareLayout() {
        parent::_prepareLayout();
        $this->setTemplate('inventoryfulfillment/shipping/view/items.phtml');
        $this->addItemRender('default', 'adminhtml/sales_items_renderer_default', 'inventoryfulfillment/shipping/view/renderer/default.phtml');
    }
    
    /**
     * Add column to item list
     * 
     * @param string $title
     * @param block $renderer
     * @return \Magestore_Inventoryfulfillment_Block_Adminhtml_Order_View_Items
     */
    public function addColumn($title, $renderer) {
        $this->_extendColumns[] = array('title' => $title, 'renderer' => $renderer);
        return $this;
    }
    
    /**
     * Get extended columns title
     * 
     * @return array
     */
    public function getExtendColumnTitles(){
        $titles = array();
        if(count($this->_extendColumns)){
            foreach($this->_extendColumns as $column){
                $titles = $column['title'];
            }
        }
        return $titles;
    }
    
    /**
     * Get extended columns renderer
     * 
     * @return array
     */    
    public function getExtendColumnRenderers(){
        $renderers = array();
        if(count($this->_extendColumns)){
            foreach($this->_extendColumns as $column){
                $renderers = $column['renderer'];
            }
        }
        return $renderers;
    }  
    
}