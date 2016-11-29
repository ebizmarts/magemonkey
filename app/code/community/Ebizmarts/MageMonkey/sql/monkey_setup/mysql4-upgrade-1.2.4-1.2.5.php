<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn(
    $installer->getTable('magemonkey_async_subscribers'), 'order_id', 'smallint(5)'
);

$installer->run(
    "
  CREATE TABLE IF NOT EXISTS `{$this->getTable('magemonkey_async_webhooks')}` (
  `id` INT(10) unsigned NOT NULL auto_increment,
  `webhook_type` varchar(24),
  `webhook_data` text,
  `processed` INT(1) NOT NULL default 0,
    PRIMARY KEY  (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
"
);

$installer->endSetup();