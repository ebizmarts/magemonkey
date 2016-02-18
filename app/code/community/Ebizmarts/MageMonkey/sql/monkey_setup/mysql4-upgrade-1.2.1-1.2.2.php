<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn(
    $installer->getTable('magemonkey_bulksync_import'), 'store_id', 'smallint(5)'
);

$installer->endSetup();