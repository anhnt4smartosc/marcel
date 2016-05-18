<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ADMIN
 * Date: 6/5/13
 * Time: 10:43 AM
 * To change this template use File | Settings | File Templates.
 */

class SM_Barcode_Block_Adminhtml_Countinventory_Grid_Renderer_Currentinventory extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row) {
        $url = Mage::helper("adminhtml")->getUrl("*/barcode_countinventory/ajaxupdatecurrqty");
        $validateMWHenabled = Mage::getStoreConfig('xwarehouse/general/enabled');
        if ($validateMWHenabled != 1) {
            $validateMWHenabled = 0;
        }
        $ajaxLoadImgUrl = $this->getSkinUrl('images/ajax-loader.gif');
        $html  = '<input type="text" id="curr-inventory-'.$row->getId().'" name="curr-inventory" class="input-text" value="'.intval($row->getQty()).'" style="text-align:center; width:75px;" onblur=\'_updateCurrentQtyOnBlur('.$row->getId().', "'.$url.'", '.$validateMWHenabled.')\'/>';
        $html .= '<img id="barcode-img-ajax-loader-tr'.$row->getId().'" src="'.$ajaxLoadImgUrl.'" style="width: 16px; vertical-align: middle; display:none;"/>';
        return $html;
    }
}