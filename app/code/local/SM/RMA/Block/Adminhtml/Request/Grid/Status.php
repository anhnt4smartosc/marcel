<?php
class SM_RMA_Block_Adminhtml_Request_Grid_Status extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        return Mage::getModel('rma/request')->getStatusLabel($row->getStatus());
    }
}
