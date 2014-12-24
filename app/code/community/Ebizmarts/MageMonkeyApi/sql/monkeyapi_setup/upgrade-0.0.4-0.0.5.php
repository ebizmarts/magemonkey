<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('monkeyapi/log'), 'response_code', 'smallint(3) unsigned null');

$installer->endSetup();