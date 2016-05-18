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
class SM_Barcode_Block_Adminhtml_Barcode_Grid_Renderer_Qty extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $productId = intval($row->getEntityId());

        $product = Mage::getModel('catalog/product')->load($productId);
        $printArr = Mage::getSingleton('core/session')->getPrintArr();
        if(is_array($printArr) && count($printArr)>0 ){
            $qty = intval($printArr[$product->getId()]);
        }
        else{
            $qty = intval($row->getQty());
        }
        return '<input type="text" onkeypress="return isNumberKey(event)" id="xbar_product_'.$row->getEntityId().'" name="product_'.$row->getEntityId().'" value="'.$qty.'" class="input-text" style="text-align:center;" />';
    }
}
 
