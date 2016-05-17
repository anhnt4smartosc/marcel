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

/**
 * Inventoryfulfillment Helper
 *
 * @category    Magestore
 * @package     Magestore_Inventoryfulfillment
 * @author      Magestore Developer
 */
class Magestore_Inventoryfulfillment_Helper_Product extends Magestore_Inventoryfulfillment_Helper_Data
{
    public function getAttributeInfo($product) {
        $attributeData = array();
        $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
        if(isset($parentIds[0])) {
            $parentProduct = Mage::getModel('catalog/product')->load($parentIds[0]);
            $attributes = $parentProduct->getTypeInstance(true)->getConfigurableAttributesAsArray($parentProduct);
            foreach($attributes as $attribute){
                $attributeData[$attribute['id']]['label'] = $attribute['label'];
                $attributeData[$attribute['id']]['value'] = $product->getAttributeText($attribute['attribute_code']);
            }
        }
        return $attributeData;
    }
    
    public function getAttributeInfoHtml($product, $breakSymb=', ') {
        $html = '';
        $lines = array();
        $attributeData = $this->getAttributeInfo($product);
        if(count($attributeData)) {
            foreach($attributeData as $attribute) {
                $lines[] = $attribute['label'] .': ' . $attribute['value'];  
            }
        }
        return implode($breakSymb, $lines);
    }
}