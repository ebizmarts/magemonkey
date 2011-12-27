<?php

$installer = $this;

$installer->getConnection()->addColumn(
    $installer->getTable('magemonkey_bulksync_import'), 'total_count', 'INT(10) unsigned NOT NULL'
);
$installer->getConnection()->addColumn(
    $installer->getTable('magemonkey_bulksync_export'), 'total_count', 'INT(10) unsigned NOT NULL'
);