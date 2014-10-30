<?php

$installer = $this;

$installer->startSetup();

$installer->run("
    ALTER TABLE `{$this->getTable('magemonkey_async_subscribers')}` CHANGE `proccessed` `processed` INT;
    ALTER TABLE `{$this->getTable('magemonkey_async_orders')}` CHANGE `proccessed` `processed` INT;
");

$installer->endSetup();