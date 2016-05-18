<?php

class SM_Barcode_Block_Adminhtml_Order_Grid_Renderer_Noscan
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {

        $reduce     = 0;
        $plus       = 1;
        $correct    = 2;

        $input  = '<div>';
        $input .= '<button pro_id="'. $row->getProductId() . '" name="scanned['. $row->getProductId() . ']"  title="Reduce" type="button" class="scalable task" style="margin: 0 0 0 5px;" onclick="return scanned('. $row->getProductId() . ','.$reduce.');" title="-" ><span>-</span></button>';
        $input .= '<button pro_id="'. $row->getProductId() . '" name="scanned['. $row->getProductId() . ']"  title="Full" type="button" class="scalable task" style="margin: 0 0 0 5px;" onclick="return scanned('. $row->getProductId() . ','.$correct.');" title="Full" ><span>Full</span></button>';
        $input .= '<button pro_id="'. $row->getProductId() . '" name="scanned['. $row->getProductId() . ']"  title="Plus" type="button" class="scalable task" style="margin: 0 0 0 5px;" onclick="return scanned('. $row->getProductId() . ','.$plus.');" title="+" ><span>+</span></button>';

        $input .= '</div>';
        return $input;

    }

}