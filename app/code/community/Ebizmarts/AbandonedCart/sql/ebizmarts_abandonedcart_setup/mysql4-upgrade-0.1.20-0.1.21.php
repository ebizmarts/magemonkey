<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 7/31/13
 * Time   : 1:56 PM
 * File   : mysql4-upgrade-0.1.20-0.1.21.php
 * Module : Ebizmarts_Magemonkey
 */
$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn(
    $installer->getTable('sales_flat_quote'), 'ebizmarts_abandonedcart_token', 'varchar(255)', null, array('default' => 'null')
);

$installer->endSetup();