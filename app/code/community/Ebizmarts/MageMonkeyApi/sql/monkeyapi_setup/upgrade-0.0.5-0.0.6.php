<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('monkeyapi/log'), 'call_method', 'varchar(50) null');
$installer->getConnection()->addColumn($installer->getTable('monkeyapi/log'), 'call_time', 'decimal(10,4) null');

$installer->endSetup();