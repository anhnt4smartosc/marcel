<?php

$resource = Mage::getSingleton('core/resource');
$readConnection = $resource->getConnection('core_read');
$writeConnection= $resource->getConnection('core_write');
$tableName      = $resource->getTableName('sm_xbar_countinventory');


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

 $installer->run('
 CREATE TABLE IF NOT EXISTS '.$tableName.' (
  countinvent_id int(10) unsigned NOT NULL auto_increment,
  product_id int(10) unsigned NOT NULL,
  scanned_qty int(10) unsigned NOT NULL,
  PRIMARY KEY  (countinvent_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
 ');

$installer->endSetup();

