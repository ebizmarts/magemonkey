<?php

$installer = $this;

$installer->getConnection()->addColumn(
    $installer->getTable('magemonkey_bulksync_import'), 'store_id', 'SMALLINT(5) unsigned NULL'
);
$installer->getConnection()->addColumn(
    $installer->getTable('magemonkey_bulksync_export'), 'store_id', 'SMALLINT(5) unsigned NULL'
);
