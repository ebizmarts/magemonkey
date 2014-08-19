<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn(
    $installer->getTable('magemonkey_ecommerce360'), 'store_id', 'smallint(5)'
);

$installer->run("
UPDATE `{$installer->getTable('magemonkey_ecommerce360')}` A JOIN `{$installer->getTable('sales_flat_order')}` B
  ON A.order_id = B.entity_id
  SET A.store_id = B.store_id
");

$installer->endSetup();

