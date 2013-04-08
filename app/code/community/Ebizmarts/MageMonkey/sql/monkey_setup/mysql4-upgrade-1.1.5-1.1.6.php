<?php

$installer = $this;

$installer->getConnection()->addColumn(
    $installer->getTable('sales_flat_order'), 'ebizmarts_magemonkey_campaign_id', 'varchar(10)'
);