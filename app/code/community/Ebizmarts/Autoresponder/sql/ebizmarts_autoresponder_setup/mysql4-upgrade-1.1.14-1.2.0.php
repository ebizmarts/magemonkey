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
            array('block_name' => 'ebizmarts_autoresponder/email_backtostock_item', 'is_allowed' => 1),
            array('block_name' => 'ebizmarts_autoresponder/email_related_items', 'is_allowed' => 1),
            array('block_name' => 'ebizmarts_autoresponder/email_review_items', 'is_allowed' => 1),
            array('block_name' => 'ebizmarts_autoresponder/email_wishlist_items', 'is_allowed' => 1),
        )
    );
}

$installer->endSetup();