<?php
/**
 * Created by Ebizmarts
 * User: gonzalo@ebizmarts.com
 * Date: 1/15/13
 * Time: 3:42 PM
 */
$installer = $this;

$installer->startSetup();

$installer->run("

	CREATE TABLE IF NOT EXISTS `{$this->getTable('ebizmarts_autoresponder_review')}` (
	  `id` int(10) unsigned NOT NULL auto_increment,
	  `customer_id` int(10),
	  `store_id` smallint(5),
	  `items` smallint(5) default 0,
	  `counter` smallint(5) default 0,
	  `token` varchar(255) default null,
	  `order_id` int(10) unsigned not null,
	  PRIMARY KEY  (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();