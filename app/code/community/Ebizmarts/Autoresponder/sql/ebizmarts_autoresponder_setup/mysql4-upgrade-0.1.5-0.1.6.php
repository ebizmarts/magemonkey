<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_Autoresponder
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

$installer = $this;

$installer->startSetup();

$installer->run("

	CREATE TABLE IF NOT EXISTS `{$this->getTable('ebizmarts_autoresponder_backtostock')}` (
	  `backtostock_id` int(10) unsigned NOT NULL auto_increment,
	  `alert_id` int(10),
	  `email` varchar(255),
	  `is_active` smallint(5) unsigned NOT NULL DEFAULT '1',
	  PRIMARY KEY  (`backtostock_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->run("

	CREATE TABLE IF NOT EXISTS `{$this->getTable('ebizmarts_autoresponder_backtostock_alert')}` (
	  `alert_id` int(10) unsigned NOT NULL auto_increment,
	  `product_id` int(10),
	  `is_active` smallint(5) unsigned NOT NULL DEFAULT '1',
	  PRIMARY KEY  (`alert_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->run("
    ALTER TABLE `{$this->getTable('magemonkey_mails_sent')}`
     CHANGE `mail_type` `mail_type` ENUM( 'abandoned cart', 'happy birthday', 'new order', 'related products', 'product review', 'no activity', 'wishlist', 'review coupon', 'back to stock' )
     CHARACTER SET utf8 NOT NULL;
");

$installer->endSetup();