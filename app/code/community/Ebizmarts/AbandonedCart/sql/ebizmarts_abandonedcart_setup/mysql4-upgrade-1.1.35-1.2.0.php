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

$tableName = $installer->getTable('permission_block');
if ($installer->getConnection()->isTableExists($tableName)) {
    $installer->getConnection()->insertMultiple(
        $installer->getTable('admin/permission_block'),
        array(
            array('block_name' => 'ebizmarts_abandonedcart/email_order_items', 'is_allowed' => 1)
        )
    );
}

$installer->getConnection()
    ->addColumn($installer->getTable('ebizmarts_abandonedcart/popup'),'store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
    ), 'Store Id');

$installer->endSetup();