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
class SM_Barcode_Block_Adminhtml_Return_Grid_Renderer_Scanned extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        return '<div id="qty-scanned-'.$row->getProductId().'" style="text-align:center;">0</div><input type="hidden" id="qty-ship-'.$row->getProductId().'" name="shipment[items]['.(is_null($row->getParentItemId())?$row->getItemId():$row->getParentItemId()).']" value="0" /><input type="hidden" id="qty-scanned-hidden-'.$row->getProductId().'" name="qty_returned_'.(is_null($row->getParentItemId())?$row->getItemId():$row->getParentItemId()).'" value="0" />';
    }
}
 
