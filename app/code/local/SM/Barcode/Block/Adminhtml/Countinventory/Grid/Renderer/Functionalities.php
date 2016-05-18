<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ADMIN
 * Date: 6/5/13
 * Time: 11:48 AM
 * To change this template use File | Settings | File Templates.
 */
class SM_Barcode_Block_Adminhtml_Countinventory_Grid_Renderer_Functionalities extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row) {
        // Declaring variables for storing URLs which contain secret key
        $urlCorrectStock = Mage::helper("adminhtml")->getUrl("*/barcode_countinventory/ajaxcorrectstock");
        $urlPlusStock    = Mage::helper("adminhtml")->getUrl("*/barcode_countinventory/ajaxplusstock");
        $urlReduceStock  = Mage::helper("adminhtml")->getUrl("*/barcode_countinventory/ajaxreducestock");
        $urlDeleteStock  = Mage::helper("adminhtml")->getUrl("*/barcode_countinventory/ajaxdeletestock");

        // Get barcode information
        $product = Mage::getModel('catalog/product')->load($row->getData('entity_id'));
        $sm_barcode      = $product->getData('sm_barcode');

        // Check extension XMultiWarehouse is avaiable or not
        $validateMWHenabled = Mage::getStoreConfig('xwarehouse/general/enabled');
        if($validateMWHenabled != 1){
            $validateMWHenabled = 0;
        }

        // Generate HTML
        $input  = '<div id="div_functional_'.$row->getId().'">';
        $input .= '<input id="correct-stock-'.$row->getId().'" name="correct_stock" title="Correct" type="button" class="scalable task" style="margin: 0 0 0 5px;" onclick=\'_correctStock('.$row->getID().',"'.$urlCorrectStock.'",'.$validateMWHenabled.')\' value="Correct">';
        $input .= '</input>';
        $input .= '<input id="plus-stock-'.$row->getId().'" name="plus_stock" title="Plus" type="button" class="scalable task" style="margin: 0 0 0 5px;" onclick=\'_plusStock('.$row->getID().',"'.$urlPlusStock.'",'.$validateMWHenabled.')\' value="+">';
        $input .= '</input>';
        $input .= '<input id="reduce-stock-'.$row->getId().'" name="reduce_stock" title="Reduce" type="button" class="scalable task" style="margin: 0 0 0 5px;" onclick=\'_reduceStock('.$row->getID().',"'.$urlReduceStock.'",'.$validateMWHenabled.')\' value="-">';
        $input .= '</input>';
        $input .= '<input id="delete-stock-'.$row->getId().'" name="delete_stock" title="Delete" type="button" class="scalable task" style="margin: 0 0 0 5px;" onclick=\'_deleteStock('.$row->getID().',"'.$urlDeleteStock.'",'.$validateMWHenabled.')\' value="Remove">';
        $input .= '</input>';
        $input .= '<input type="hidden" value="'. $sm_barcode. '" name="sm_barcode_hidden" id="'.$row->getId().'" />';
        $input .= '</div>';
        return $input;
    }
}
