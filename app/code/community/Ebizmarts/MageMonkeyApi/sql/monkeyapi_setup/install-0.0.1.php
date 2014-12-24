<?php

$installer = $this;

$installer->startSetup();

$installer->run("
	CREATE TABLE IF NOT EXISTS `{$installer->getTable('monkeyapi/application')}` (
	  `id` int(10) unsigned NOT NULL auto_increment,
	  `application_key` text null,
	  `activated` tinyint(1) unsigned default 0,
	  `application_request_key` text null,
      `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
      `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
	  PRIMARY KEY  (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();