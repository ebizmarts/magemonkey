<?php

$installer = $this;

$installer->startSetup();

$installer->run("

	CREATE TABLE IF NOT EXISTS `{$this->getTable('magemonkey_api_debug')}` (
	  `debug_id` int(10) unsigned NOT NULL auto_increment,
	  `debug_at` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
	  `request_body` text,
	  `response_body` text,
	  PRIMARY KEY  (`debug_id`),
	  KEY `debug_at` (`debug_at`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();