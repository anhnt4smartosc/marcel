<?php
/**
 * Date: 1/30/13
 * Time: 2:35 PM
 */

$installer = $this;
$installer->startSetup();
$entityTypeId     = $installer->getEntityTypeId('catalog_product');
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);

$installer->addAttribute('catalog_product', 'sm_barcode', array(
    'group'			=>'General',
    'input'         => 'text',
    'label'         => 'SmartOSC XBarcode',
    'required'      => 0,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'       => 1,
    'is_unique'     => '1',
    'input_renderer'=> 'barcode/adminhtml_catalog_product_barcode',
));

$installer->endSetup();