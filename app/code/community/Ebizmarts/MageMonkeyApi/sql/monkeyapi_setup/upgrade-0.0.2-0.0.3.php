<?php

$installer = $this;

$installer->startSetup();

$tableApp = $installer->getTable('monkeyapi/application');

$installer->run("
  ALTER TABLE `{$tableApp}` MODIFY `application_key` CHAR(4);
  ALTER TABLE `{$tableApp}` MODIFY `application_request_key` CHAR(22);
");

$installer->endSetup();