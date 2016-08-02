<?php
/**
 * Created by Ebizmarts
 * User: gonzalo@ebizmarts.com
 * Date: 3/12/13
 * Time: 10:25 AM
 */

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn(
    $installer->getTable('sales_flat_order'), 'ebizmarts_abandonedcart_flag', 'int(1)', null, array('default' => '0')
);

$installer->endSetup();