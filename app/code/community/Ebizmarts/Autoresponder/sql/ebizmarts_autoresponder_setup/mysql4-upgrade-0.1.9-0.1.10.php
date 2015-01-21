<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_Autoresponder
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn(
    $installer->getTable('ebizmarts_autoresponder_visited'), 'customer_email', 'varchar(128)'
);

$installer->endSetup();