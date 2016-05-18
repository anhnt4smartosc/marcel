<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ADMIN
 * Date: 6/5/13
 * Time: 11:34 AM
 * To change this template use File | Settings | File Templates.
 */

class SM_Barcode_Block_Adminhtml_Countinventory_Grid_Renderer_Qtyscanned extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row) {
        $url = Mage::helper("adminhtml")->getUrl("*/barcode_countinventory/ajaxgetgridinfo");
        $productQty = isset($_SESSION['scanned_product_ids'][$row->getId()]) ? $_SESSION['scanned_product_ids'][$row->getId()] : 0;
        $ajaxLoadImgUrl = $this->getSkinUrl('images/ajax-loader.gif');

        $html  = '<input type="text" id="qty-scanned-'.$row->getId().'" name="qty_scanned_'.$row->getId().'" value="'.$productQty.'" class="input-text" style="text-align:center; width:75px;" onkeypress=\'_updateScannedQty(event, '.$row->getId().', "'.$url.'")\' OnBlur=\'_updateScannedQtyOnBlur('.$row->getId().', "'.$url.'")\'/>';
        $html .= '<img id="scanned-qty-img-ajax-loader-tr'.$row->getId().'" src="'.$ajaxLoadImgUrl.'" style="width: 16px; vertical-align: middle; display:none;"/>';

        return $html;
    }
}