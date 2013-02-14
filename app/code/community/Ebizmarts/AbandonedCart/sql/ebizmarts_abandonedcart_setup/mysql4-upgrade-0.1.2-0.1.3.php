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

		ALTER TABLE `{$this->getTable('sales_flat_quote')}`
				add column ebizmarts_abandonedcart_flag int(1) default '0'
	");

$installer->endSetup();