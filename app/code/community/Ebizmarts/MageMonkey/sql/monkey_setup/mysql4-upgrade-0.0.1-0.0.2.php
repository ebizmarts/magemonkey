<?php

$installer = $this;

$installer->startSetup();

$installer->run("

	CREATE TABLE IF NOT EXISTS `{$this->getTable('magemonkey_ecommerce360')}` (
	  `id` int(10) unsigned NOT NULL auto_increment,
      `order_id` int(10) unsigned NOT NULL,
	  `order_increment_id` varchar(50) NOT NULL default '',
      `mc_campaign_id` varchar(255) NOT NULL default '',
      `mc_email_id` varchar(255) NOT NULL default '',
      `created_at` DATETIME NOT NULL ,
	  PRIMARY KEY  (`id`),
	  KEY `order_increment_id` (`order_increment_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();