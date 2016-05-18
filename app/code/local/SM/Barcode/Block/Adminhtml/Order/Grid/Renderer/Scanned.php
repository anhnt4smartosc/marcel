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
class SM_Barcode_Block_Adminhtml_Order_Grid_Renderer_Scanned extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        return '<script>
                    var quantityLocal = localStorage.getItem("qty-scanned-' . $row->getProductId().'");
                    if(quantityLocal){
                        document.getElementById("qty-scanned-' . $row->getProductId().'").innerHTML =quantityLocal;
                    }else{
                        document.getElementById("qty-scanned-' . $row->getProductId().'").innerHTML = 0;
                    }
                    </script>
        <div class="qty-scanned-'.$row->getProductId().'" id="qty-scanned-'.$row->getProductId().'" style="text-align:center;">0</div><input type="hidden" id="qty-ship-'.$row->getProductId().'" name="shipment[items]['.(is_null($row->getParentItemId())?$row->getItemId():$row->getParentItemId()).']" value="0" />';
    }
}
 
