<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn(
    $installer->getTable('magemonkey_async_subscriber'), 'order_id', 'smallint(5)'
);

$installer->endSetup();