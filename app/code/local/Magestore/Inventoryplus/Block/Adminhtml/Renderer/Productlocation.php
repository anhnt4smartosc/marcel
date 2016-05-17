<?php
    class Magestore_Inventoryplus_Block_Adminhtml_Renderer_Productlocation
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row) 
    {
        $html = '<input type="text" ';
        $html .= 'name="product_location" ';
        $html .= 'id="location-' . $row->getId() . '" ';
        $html .= 'value="' . $row->getProductLocation() . '"';
        $html .= 'style="width:160px !important"';
        $html .= 'class="input-text"/>';
        return $html;
    }
}