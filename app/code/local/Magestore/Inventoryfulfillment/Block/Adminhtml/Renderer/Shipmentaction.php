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

class Magestore_Inventoryfulfillment_Block_Adminhtml_Renderer_Shipmentaction extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $grid = $this->getColumn()->getGrid();
        $tag = $grid->getTag();
        $rowDetails = $grid->getRowDetails($row);
        $html = "<span style='font-size:18px;color:#7f7f7f;' class='show_hide_plus'" 
                . 'onclick=getRowDetails(this,\'' . $rowDetails['url'] . '\',' . $row['entity_id'] . ',\'' . $tag . '\')>'
                . '<i class="fa fa-plus-square-o"></i></span>';
        return $html;
    }
}