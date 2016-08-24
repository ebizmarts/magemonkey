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
$result = $installer->getConnection()->fetchAll("SHOW TABLES LIKE '".$tableName."'");
$tableExists = count($result) > 0;
if ($tableExists) {
    try {
        $installer->getConnection()->insertMultiple(
            $installer->getTable('admin/permission_block'),
            array(
                array('block_name' => 'ebizmarts_autoresponder/email_backtostock_item', 'is_allowed' => 1),
                array('block_name' => 'ebizmarts_autoresponder/email_related_items', 'is_allowed' => 1),
                array('block_name' => 'ebizmarts_autoresponder/email_review_items', 'is_allowed' => 1),
                array('block_name' => 'ebizmarts_autoresponder/email_wishlist_items', 'is_allowed' => 1),
            )
        );
    }catch (Exception $e){
        Mage::log($e->getMessage());
    }
}

$installer->endSetup();
