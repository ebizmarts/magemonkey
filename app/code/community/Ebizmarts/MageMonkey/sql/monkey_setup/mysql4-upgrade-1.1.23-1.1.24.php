<?php

$installer = $this;

$installer->startSetup();

try {
    $installer->run(
        "
    ALTER TABLE `{$this->getTable('magemonkey_async_subscribers')}` CHANGE `proccessed` `processed` INT;
    ALTER TABLE `{$this->getTable('magemonkey_async_orders')}` CHANGE `proccessed` `processed` INT;
"
    );
} catch(Exception $e) {
    Mage::log($e->getMessage());
}

$installer->endSetup();