<?php
$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn(
    $installer->getTable('sales_flat_quote'), 'ebizmarts_abandonedcart_counter', 'int(5)', null, array('default' => '0')
);
$installer->endSetup();