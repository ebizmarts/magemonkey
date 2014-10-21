<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn(
    $installer->getTable('magemonkey_ecommerce360'), 'store_id', 'smallint(5)'
);

$installer->run("
UPDATE `{$installer->getTable('magemonkey_ecommerce360')}` A JOIN `{$installer->getTable('sales_flat_order')}` B
  ON A.order_id = B.entity_id
  SET A.store_id = B.store_id
");

$installer->run("

	CREATE TABLE IF NOT EXISTS `{$this->getTable('magemonkey_async_subscribers')}` (
	  `id` INT(10) unsigned NOT NULL auto_increment,
	  `email` varchar(128),
	  `confirm` smallint(1) default 0,
      `lists` TEXT NOT NULL,
      `mapfields` TEXT,
      `created_at` DATETIME NOT NULL ,
      `proccessed` smallint(1) default 0,
	  PRIMARY KEY  (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	CREATE TABLE IF NOT EXISTS `{$this->getTable('magemonkey_async_orders')}` (
	  `id` INT(10) unsigned NOT NULL auto_increment,
      `info` TEXT NOT NULL,
      `created_at` DATETIME NOT NULL ,
      `proccessed` smallint(1) default 0,
	  PRIMARY KEY  (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");
$installer->endSetup();

