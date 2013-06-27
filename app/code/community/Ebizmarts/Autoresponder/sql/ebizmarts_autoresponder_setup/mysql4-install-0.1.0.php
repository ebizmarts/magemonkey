<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 4/28/13
 * Time   : 11:20 AM
 * File   : ${FILE_NAME}
 * Module : ${PROJECT_NAME}
 */ 
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("

	CREATE TABLE IF NOT EXISTS `{$this->getTable('ebizmarts_autoresponder_unsubscribe')}` (
	  `id` int(10) unsigned NOT NULL auto_increment,
	  `email` varchar(255),
	  `list`  varchar(255),
	  `store_id` smallint(5),
	  `unsubscribed_at` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
	  PRIMARY KEY  (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();