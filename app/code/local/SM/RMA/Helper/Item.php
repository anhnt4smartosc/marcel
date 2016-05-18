<?php

class SM_RMA_Helper_Item extends Mage_Core_Helper_Abstract {

    public function getOptions(Mage_Sales_Model_Order_Item $item) {

        $product = Mage::getModel('catalog/product')->load($item->getProductId());
        $optionCode = $this->getOptionCode($product);

        if (isset($product) && $product->getId()) {
            $childProducts = Mage::getModel('catalog/product_type_configurable')
                    ->getUsedProducts(null, $product);

            $sizes = array();
            foreach ($childProducts as $child) {
                $qtyStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($child)->getQty();
                if ($qtyStock > 0) {
                    $sizes[$child->getId()] = $child->getResource()->getAttribute($optionCode)->getSource()->getOptionText($child->getData($optionCode));
                }
            }
        }

        if (isset($sizes) && count($sizes) > 0) {
            return $sizes;
        } else {
            return false;
        }
    }

    public function getOptionCode($product, $isChildProduct = false) {
        $attributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);
        $optionCode = null;

        foreach ($attributes as $attribute) {  // Only get one option, we will add process with multi options later.
            if ($attribute->getData('product_attribute')->getData('attribute_code')) {
                $optionCode = $attribute->getData('product_attribute')->getData('attribute_code');
            }
        }

        return $optionCode;
    }

}