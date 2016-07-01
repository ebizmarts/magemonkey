<?php

$installer = $this;

$installer->startSetup();

$installer->run("
  CREATE TABLE IF NOT EXISTS `{$this->getTable('magemonkey_async_webhooks')}` (
  `id` INT(10) unsigned NOT NULL auto_increment,
  `webhook_type` varchar(24),
  `webhook_data` text,
  `processed` INT(1) NOT NULL default 0,
    PRIMARY KEY  (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();