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

	 ALTER TABLE  `{$this->getTable('sales_flat_quote')}` CHANGE `ebizmarts_abandonedcart_counter` `ebizmarts_abandonedcart_counter` INT( 5 ) NOT NULL DEFAULT '0';
	 ALTER TABLE  `{$this->getTable('sales_flat_quote')}` CHANGE `ebizmarts_abandonedcart_flag` `ebizmarts_abandonedcart_flag` INT( 5 ) NOT NULL DEFAULT '0';


");

$installer->endSetup();