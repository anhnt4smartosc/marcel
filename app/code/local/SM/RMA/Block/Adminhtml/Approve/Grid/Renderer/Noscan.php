<?php
class SM_RMA_Block_Adminhtml_Approve_Grid_Renderer_Noscan extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $plus=1;
        $reduce=0;
        $correct=2;

        $item = Mage::getModel('sales/order_item')->load(intval($row->getItemId()));
        $product = Mage::getModel('catalog/product')->load($item->getProductId());
        if($product['price_type']=="0"){

        }

       // $urlCorrectStock = Mage::helper("adminhtml")->getUrl("*/barcode_countinventory/ajaxcorrectstock");
       // $urlPlusStock    = Mage::helper("adminhtml")->getUrl("*/barcode_countinventory/ajaxplusstock");
       // $urlReduceStock  = Mage::helper("adminhtml")->getUrl("*/barcode_countinventory/ajaxreducestock");
       // $urlDeleteStock  = Mage::helper("adminhtml")->getUrl("*/barcode_countinventory/ajaxdeletestock");

        // Get barcode information
       // $product = Mage::getModel('catalog/product')->load($row->getData('entity_id'));
        //$sm_barcode      = $product->getData('sm_barcode');

        // Check extension XMultiWarehouse is avaiable or not
       /* $validateMWHenabled = Mage::getStoreConfig('xwarehouse/general/enabled');
        if($validateMWHenabled != 1){
            $validateMWHenabled = 0;
        }*/

        // Generate HTML
       $input  = '<div>';
       // $input .= '<input id="reduce-stock-'.$row->getId().'" name="reduce_stock" title="Reduce" type="button" class="scalable task" style="margin: 0 0 0 5px;" onclick=\'_reduceStock('.$row->getID().',"'.$urlReduceStock.'",'.$validateMWHenabled.')\' value="-">';
       // $input .= '</input>';
        //$input .= '<input id="correct-stock-'.$row->getId().'" name="correct_stock" title="Correct" type="button" class="scalable task" style="margin: 0 0 0 5px;" onclick=\'_correctStock('.$row->getID().',"'.$urlCorrectStock.'",'.$validateMWHenabled.')\' value="Correct">';
       // $input .= '</input>';
       // echo "<pre>";
       // var_dump($row->getData());
        $item = Mage::getModel('sales/order_item')->load(intval($row->getItemId()));
        $price= Mage::helper('checkout')->getBasePriceInclTax($item);
      //  echo "<pre>";
       // var_dump($item->getData('parent_item_id'));
        if($item->getData('parent_item_id')){
           // echo "fda";
            $parent_item = Mage::getModel('sales/order_item')->load($item->getData('parent_item_id'));
            $product = Mage::getModel('catalog/product')->load($parent_item->getProductId());
            if($product['price_type']=="0"){

                $input .= '<button pro_id="'. $row->getProductId() . '" name="scanned['. $row->getProductId() . ']"  title="Reduce" type="button" class="scalable task" style="margin: 0 0 0 5px;" onclick="return scanned('. $row->getProductId() . ','.$reduce.');" title="-" ><span>-</span></button>';
                $input .= '<button pro_id="'. $row->getProductId() . '" name="scanned['. $row->getProductId() . ']"  title="Full" type="button" class="scalable task" style="margin: 0 0 0 5px;" onclick="return scanned('. $row->getProductId() . ','.$correct.');" title="Full" ><span>Full</span></button>';
                $input .= '<button pro_id="'. $row->getProductId() . '" name="scanned['. $row->getProductId() . ']"  title="Plus" type="button" class="scalable task" style="margin: 0 0 0 5px;" onclick="return scanned('. $row->getProductId() . ','.$plus.');" title="+" ><span>+</span></button>';

            }
            else{

            }

        }
        else{
           // echo "fdaf" ;
            if($item->getProductType()=="bundle"){
              //  echo "bundle";
                $item = Mage::getModel('sales/order_item')->load(intval($row->getItemId()));
                $product = Mage::getModel('catalog/product')->load($item->getProductId());
                if($product['price_type']=="0"){
                   // echo "tra dc sp con";
                }
                else{
                    $input .= '<button pro_id="'. $row->getProductId() . '" name="scanned['. $row->getProductId() . ']"  title="Reduce" type="button" class="scalable task" style="margin: 0 0 0 5px;" onclick="return scanned('. $row->getProductId() . ','.$reduce.');" title="-" ><span>-</span></button>';
                    $input .= '<button pro_id="'. $row->getProductId() . '" name="scanned['. $row->getProductId() . ']"  title="Full" type="button" class="scalable task" style="margin: 0 0 0 5px;" onclick="return scanned('. $row->getProductId() . ','.$correct.');" title="Full" ><span>Full</span></button>';
                    $input .= '<button pro_id="'. $row->getProductId() . '" name="scanned['. $row->getProductId() . ']"  title="Plus" type="button" class="scalable task" style="margin: 0 0 0 5px;" onclick="return scanned('. $row->getProductId() . ','.$plus.');" title="+" ><span>+</span></button>';
                }
            }
            else{
                $input .= '<button pro_id="'. $row->getProductId() . '" name="scanned['. $row->getProductId() . ']"  title="Reduce" type="button" class="scalable task" style="margin: 0 0 0 5px;" onclick="return scanned('. $row->getProductId() . ','.$reduce.');" title="-" ><span>-</span></button>';
                $input .= '<button pro_id="'. $row->getProductId() . '" name="scanned['. $row->getProductId() . ']"  title="Full" type="button" class="scalable task" style="margin: 0 0 0 5px;" onclick="return scanned('. $row->getProductId() . ','.$correct.');" title="Full" ><span>Full</span></button>';
                $input .= '<button pro_id="'. $row->getProductId() . '" name="scanned['. $row->getProductId() . ']"  title="Plus" type="button" class="scalable task" style="margin: 0 0 0 5px;" onclick="return scanned('. $row->getProductId() . ','.$plus.');" title="+" ><span>+</span></button>';
           }
        }
        //$input .= '</input>';

       // $input .= '<input type="hidden" value="'. $sm_barcode. '" name="sm_barcode_hidden" id="'.$row->getId().'" />';
        $input .= '</div>';
       return $input;


        //return '<div style="text-align:center;"><input onchange="return scanned(this);" name="scanned['. $row->getProductId() . ']" id="scanned['. $row->getProductId() . ']" type="checkbox" /></div>';
    }
}
 
