<?php

$installer = $this;

$installer->startSetup();

$installer->run("

	CREATE TABLE IF NOT EXISTS `{$this->getTable('magemonkey_bulksync_import')}` (
	  `id` INT(10) unsigned NOT NULL auto_increment,
      `lists` TEXT NOT NULL,
      `import_types` TEXT NOT NULL,
      `status` ENUM('idle', 'running', 'chunk_running', 'finished') NOT NULL,
      `create_customer` TINYINT(1) unsigned NOT NULL,
      `last_processed_id` INT(10) unsigned NOT NULL,
      `processed_count` INT(10) unsigned NOT NULL,
      `updated_at` DATETIME NOT NULL ,
      `created_at` DATETIME NOT NULL ,
	  PRIMARY KEY  (`id`),
	  KEY `status` (`status`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();