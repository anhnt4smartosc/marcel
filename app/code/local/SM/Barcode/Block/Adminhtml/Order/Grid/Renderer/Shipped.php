<?php

/**
 * SmartOSC Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 *
 * @category   SM
 * @package    SM_Barcode
 * @version    2.0
 * @author     hoadx@smartosc.com
 * @copyright  Copyright (c) 2010-2011 SmartOSC Co. (http://www.smartosc.com)
 */
class SM_Barcode_Block_Adminhtml_Order_Grid_Renderer_Shipped extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
//        $orderId = $row->getData($this->getColumn()->getIndex());
        if ($parentId = $row->getParent_item_id()) {
            $orderId = $row->getOrder_id();
            $collection = Mage::getModel('sales/order_item')->getCollection()
                ->addFieldToFilter('order_id', $orderId)
                ->addFieldToFilter('item_id', $parentId);
            $qty_shipped = $collection->getData('qty_shipped');
            $qty = (int)$qty_shipped[0]['qty_shipped'];
            return '<div class="qty-shipped-' . $row->getProductId() . '" id="qty-shipped-' . $row->getProductId() . '" style="text-align:center;">' . $qty . '</div>';
        } else {
            return '<div class="qty-shipped-' . $row->getProductId() . '" id="qty-shipped-' . $row->getProductId() . '" style="text-align:center;">' . (int)$row->getQtyShipped() . '</div>';
        }
    }

    public function createOrderModel()
    {
        return Mage::getModel('sales/order');
    }
}
 
