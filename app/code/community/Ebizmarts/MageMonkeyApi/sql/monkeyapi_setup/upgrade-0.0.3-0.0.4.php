<?php

$installer = $this;

$installer->startSetup();

$tableApp = $installer->getTable('monkeyapi/application');

$installer->getConnection()->addIndex($tableApp, $installer->getIdxName('monkeyapi/application', array('application_key')), array('application_key'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE);

$installer->getConnection()->addColumn($installer->getTable('monkeyapi/log'), 'response_headers', 'TEXT null');
$installer->getConnection()->addColumn($installer->getTable('monkeyapi/log'), 'response_params', 'TEXT null');

$installer->endSetup();