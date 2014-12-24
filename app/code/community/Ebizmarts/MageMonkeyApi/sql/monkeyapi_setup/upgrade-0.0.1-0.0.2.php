<?php

$installer = $this;

$installer->startSetup();

$tableApp = $installer->getTable('monkeyapi/application');

$installer->getConnection()->addColumn($tableApp, 'uuid', 'CHAR(36) null');
$installer->getConnection()->addColumn($tableApp, 'last_call_ts', 'BIGINT null');
$installer->getConnection()->addColumn($tableApp, 'application_name', 'VARCHAR(255) null');
$installer->getConnection()->addColumn($tableApp, 'device_info', 'TEXT null');
$installer->getConnection()->addIndex($tableApp, $installer->getIdxName('monkeyapi/application', array('uuid')), array('uuid'));

$installer->run("
	CREATE TABLE IF NOT EXISTS `{$installer->getTable('monkeyapi/log')}` (
	  `id` int(10) unsigned NOT NULL auto_increment,
	  `http_user_agent` VARCHAR(255) null,
	  `http_headers` TEXT null,
	  `http_params` TEXT null,
	  `remote_addr` VARCHAR(255) null,
	  `uuid` CHAR(36) null,
      `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
      `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
	  PRIMARY KEY  (`id`),
	  KEY `uuid` (`uuid`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();