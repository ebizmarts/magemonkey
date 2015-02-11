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
	CREATE TABLE IF NOT EXISTS `{$this->getTable('ebizmarts_abandonedcart_popup')}` (
	  `id` INT(10) unsigned NOT NULL auto_increment,
	  `email` varchar(128),
	  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	  `counter` INT(10),
	  `processed` smallint(1) default 0,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();