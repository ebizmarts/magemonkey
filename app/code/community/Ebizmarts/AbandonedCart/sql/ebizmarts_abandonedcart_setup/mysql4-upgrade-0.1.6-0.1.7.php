<?php
/**
 * Created by Ebizmarts
 * User: gonzalo@ebizmarts.com
 * Date: 3/12/13
 * Time: 10:25 AM
 */

$installer = $this;

$installer->startSetup();

$installer->run("

		ALTER TABLE `{$this->getTable('sales_flat_order')}`
				add column ebizmarts_abandonedcart_flag int(1) default '0'
	");

$installer->endSetup();