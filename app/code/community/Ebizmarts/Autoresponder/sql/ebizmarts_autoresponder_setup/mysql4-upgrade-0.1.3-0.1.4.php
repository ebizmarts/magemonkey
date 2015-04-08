<?php
/**
 * Created by Ebizmarts
 * User: gonzalo@ebizmarts.com
 * Date: 1/15/13
 * Time: 3:42 PM
 */
$installer = $this;

$installer->startSetup();

$installer->run("

	CREATE TABLE IF NOT EXISTS `{$this->getTable('ebizmarts_autoresponder_visited')}` (
	  `id` int(10) unsigned NOT NULL auto_increment,
	  `customer_id` int(10),
	  `product_id`  int(10),
	  `store_id` smallint(5),
	  `visited_at` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
	  PRIMARY KEY  (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$applyTo = array(
    Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
    Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
);

$installer->addAttribute(
    Mage_Catalog_Model_Product::ENTITY,
    'ebizmarts_mark_visited',
    array(
        'group' => 'General',
        'type' => 'int',
        'backend' => '',
        'frontend' => '',
        'label' => 'Send Browsed Product Autoresponder',
        'input' => 'select',
        'source' => 'eav/entity_attribute_source_boolean',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'required' => false,
        'user_defined' => true,
        'default' => '0',
        'searchable' => false,
        'filterable' => false,
        'comparable' => false,
        'visible_on_front' => false,
        'unique' => false,
        'is_configurable' => false,
        'apply_to' => implode(',', $applyTo)
    )
);

$installer->updateAttribute('catalog_product', 'ebizmarts_mark_visited', 'backend_model', '');

$installer->endSetup();