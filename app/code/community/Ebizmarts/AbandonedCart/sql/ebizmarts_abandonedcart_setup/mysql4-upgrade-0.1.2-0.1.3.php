<?php
/**
 * Created by Ebizmarts
 * User: gonzalo@ebizmarts.com
 * Date: 1/15/13
 * Time: 3:42 PM
 */
$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn(
    $installer->getTable('sales_flat_quote'), 'ebizmarts_abandonedcart_flag', 'int(1)', null, array('default' => '0')
);

$installer->endSetup();