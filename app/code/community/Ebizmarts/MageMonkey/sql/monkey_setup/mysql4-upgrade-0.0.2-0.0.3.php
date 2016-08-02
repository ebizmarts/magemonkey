<?php

$installer = $this;

$installer->startSetup();

$installer->run("

	CREATE TABLE IF NOT EXISTS `{$this->getTable('magemonkey_bulksync_export')}` (
	  `id` INT(10) unsigned NOT NULL auto_increment,
      `lists` TEXT NOT NULL,
      `processed_count` INT(10) unsigned NOT NULL,
      `last_processed_id` INT(10) unsigned NOT NULL,
      `status` ENUM('idle', 'running', 'chunk_running', 'finished') NOT NULL,
      `data_source_entity` ENUM('newsletter_subscriber', 'customer') NOT NULL,
      `updated_at` DATETIME NOT NULL ,
      `created_at` DATETIME NOT NULL ,
	  PRIMARY KEY  (`id`),
	  KEY `status` (`status`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();