<style>body{margin:0;padding:30px;background:#fafafa;}h1{margin-bottom:0;font-size:1.5em;}hr{margin:1em 0;border-top-color:#f8f8f8;border-bottom-color:#fff;}span{color:#888;}</style>

<?php die('Uncomment this code in the file to run uninstall script');

require_once ('../app/Mage.php');
session_start();
Mage::reset();
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

ini_set('display_errors', 1);
$tablePrefix = Mage::getConfig()->getTablePrefix();

function executeSql($query)
{
    $resource = Mage::getSingleton('core/resource');
    $writeConnection = $resource->getConnection('core_write');
    try
    {
        $writeConnection->query($query);
        return '<font color="green">OK</font>';
    }
    catch(Exception $ex)
    {
        return '<font color="red">NOK</font>';
    }
}

//remove attributes
echo "<h1>Remove attributes</h1>";
$attributes = array(
    'ebizmarts_mark_visited',
    'ebizmarts_reviews_cntr_total',
    'ebizmarts_reviews_coupon_total'
);
$installer = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();
foreach($attributes as $attribute)
{
    echo "<br><span>Remove attribute:</span> ".$attribute." ";
    $installer->removeAttribute('catalog_product', $attribute);
}
$installer->endSetup();

//remove tables
echo "<hr><h1>Remove tables</h1>";
$tables = array('magemonkey_mails_sent',
    'ebizmarts_abandonedcart_popup',
    'ebizmarts_abandonedcart_abtesting',
    'ebizmarts_autoresponder_unsubscribe',
    'ebizmarts_autoresponder_visited',
    'ebizmarts_autoresponder_review',
    'ebizmarts_autoresponder_backtostock',
    'ebizmarts_autoresponder_backtostock_alert',
    'magemonkey_api_debug',
    'magemonkey_ecommerce360',
    'magemonkey_bulksync_export',
    'magemonkey_bulksync_import',
    'magemonkey_async_subscribers',
    'magemonkey_async_orders',
    'magemonkey_last_order',
    'magemonkey_async_webhooks'
);
foreach($tables as $table)
{
    echo "<br><span>Remove table:</span> ".$table." : ".executeSql('drop table IF EXISTS '.$tablePrefix.$table);
}

//remove columns
echo "<hr><h1>Remove columns</h1>";
$columns = array(
    array('table' => 'sales_flat_quote', 'column' => 'ebizmarts_abandonedcart_counter'),
    array('table' => 'sales_flat_quote', 'column' => 'ebizmarts_abandonedcart_flag'),
    array('table' => 'sales_flat_quote', 'column' => 'ebizmarts_abandonedcart_token'),
    array('table' => 'sales_flat_order', 'column' => 'ebizmarts_magemonkey_campaign_id'),
    array('table' => 'newsletter_subscriber', 'column' => 'subscriber_firstname'),
    array('table' => 'newsletter_subscriber', 'column' => 'subscriber_lastname')
);
foreach($columns as $column)
{
    echo "<br><span>Remove column:</span> ".implode('/', $column)." : ".executeSql('ALTER TABLE '.$tablePrefix.$column['table'].' DROP COLUMN '.$column['column'].'; ');
}

//remove core_resource records
echo "<hr><h1>Remove modules</h1>";
$modules = array('monkey_setup',
    'ebizmarts_abandonedcart_setup',
    'ebizmarts_autoresponder_setup'
);
foreach ($modules as $module)
{
    echo "<br><span>Remove module:</span> ".$module." : ".executeSql('delete from '.$tablePrefix.'core_resource where code = "'.$module.'"; ');
}