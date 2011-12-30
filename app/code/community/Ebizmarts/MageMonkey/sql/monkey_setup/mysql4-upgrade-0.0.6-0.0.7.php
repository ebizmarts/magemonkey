<?php

$installer = $this;

$installer->getConnection()->addColumn(
    $installer->getTable('magemonkey_bulksync_import'), 'started_at', 'DATETIME'
);
$installer->getConnection()->addColumn(
    $installer->getTable('magemonkey_bulksync_export'), 'started_at', 'DATETIME'
);