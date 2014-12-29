<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('monkeyapi/application'), 'app_info', 'TEXT null');

$installer->endSetup();