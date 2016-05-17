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
class Magestore_Inventoryfulfillment_Block_Adminhtml_Fulfillment_verifying_View_Items
    extends Magestore_Inventoryfulfillment_Block_Adminhtml_Order_View_Items
{
    protected $_extendColumns = array();
    
    protected function _prepareLayout() {
        parent::_prepareLayout();
        $this->setTemplate('inventoryfulfillment/verifying/view/items.phtml');
        $this->addItemRender('default', 'inventoryfulfillment/adminhtml_fulfillment_verifying_view_items_renderer_default', 'inventoryfulfillment/verifying/view/items/renderer/default.phtml');
    }

}
