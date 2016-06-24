<?php

$installer = $this;

$installer->startSetup();

$installer->run("
  CREATE TABLE IF NOT EXISTS `{$this->getTable('magemonkey_last_order')}` (
  `id` INT(10) unsigned NOT NULL auto_increment,
  `email` varchar(128),
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->getConnection()->addColumn(
    $installer->getTable('newsletter_subscriber'), 'subscriber_firstname', 'varchar(50)'
);

$installer->getConnection()->addColumn(
    $installer->getTable('newsletter_subscriber'), 'subscriber_lastname', 'varchar(50)'
);

$installer->endSetup();