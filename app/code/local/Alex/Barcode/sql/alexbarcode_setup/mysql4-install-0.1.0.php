<?php

$installer = $this;
$installer->startSetup();
$entityTypeId     = $installer->getEntityTypeId('catalog_product');
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);

$installer->addAttribute('catalog_product', 'expired_date', array(
    'group'			=>'General',
    'input'         => 'date',
    'label'         => 'Expired Date',
    'required'      => true,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'       => 1,
    'is_unique'     => false
));

$installer->endSetup();