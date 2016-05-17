<?php
class Magestore_Inventoryphysicalstocktaking_Block_Adminhtml_Physicalstocktaking_Renderer_Category extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $product_id = $row->getEntityId();
        $content = '';

        $product = Mage::getModel('catalog/product')->load($product_id);
        $categoryIds = $product->getCategoryIds();
        $i = 0;
        $numItems = count($categoryIds);
        $result = '';
        foreach($categoryIds as $categoryId){
            $category = Mage::getModel('catalog/category')->load($categoryId);
            $result .= $category->getName().',';
            if(++$i === $numItems) {
                $result = substr($result, 0, -1);
            }
        }
        $content .= '<span>'.$result.'</span>';
        return $content;
    }

}
