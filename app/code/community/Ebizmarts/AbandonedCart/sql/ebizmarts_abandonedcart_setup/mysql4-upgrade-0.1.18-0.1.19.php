<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 6/5/13
 * Time   : 12:42 PM
 * File   : mysql4-upgrade-0.1.7-0.1.8.php
 * Module : Ebizmarts_Magemonkey
 */
$installer = $this;

$installer->startSetup();

$installer->run("
	CREATE TABLE IF NOT EXISTS `{$this->getTable('magemonkey_mails_sent')}` (
	  `id` INT(10) unsigned NOT NULL auto_increment,
	  `store_id` smallint(5),
	  `mail_type` ENUM('abandoned cart','happy birthday','new order', 'related products', 'product review', 'no activity', 'wishlist') NOT NULL,
	  `customer_email` varchar(255),
	  `customer_name` varchar(255),
	  `coupon_number` varchar(255),
	  `coupon_type` smallint(2),
	  `coupon_amount` decimal(10,2),
      `sent_at` DATETIME NOT NULL ,
	  PRIMARY KEY  (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();