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

$installer->getConnection()->insertMultiple(
    $installer->getTable('admin/permission_block'),
    array(
        array('block_name' => 'ebizmarts_autoresponder/email_backtostock_item', 'is_allowed' => 1)
    )
);

$installer->getConnection()
    ->addColumn($installer->getTable('ebizmarts_abandonedcart/popup'),'store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5, array(
        'nullable'  => false,
    ), 'Store Id');

$installer->endSetup();