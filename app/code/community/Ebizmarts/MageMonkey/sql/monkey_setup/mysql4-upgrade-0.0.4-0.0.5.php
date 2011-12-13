<?php

$installer = $this;

$installer->getConnection()->addColumn(
    $installer->getTable('magemonkey_bulksync_import'), 'since', 'DATETIME'
);