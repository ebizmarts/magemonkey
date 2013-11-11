<?php
/**
 * Created by Ebizmarts
 * User: gonzalo@ebizmarts.com
 * Date: 1/15/13
 * Time: 3:42 PM
 */
$installer = $this;

$installer->startSetup();

$installer->run("

	CREATE TABLE IF NOT EXISTS `{$this->getTable('ebizmarts_autoresponder_review')}` (
	  `id` int(10) unsigned NOT NULL auto_increment,
	  `customer_id` int(10),
	  `store_id` smallint(5),
	  `items` smallint(5) default 0,
	  `counter` smallint(5) default 0,
	  `token` varchar(255) default null,
	  `order_id` int(10) unsigned not null,
	  PRIMARY KEY  (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->run("
    ALTER TABLE `{$this->getTable('magemonkey_mails_sent')}`
     CHANGE `mail_type` `mail_type` ENUM( 'abandoned cart', 'happy birthday', 'new order', 'related products', 'product review', 'no activity', 'wishlist', 'review coupon' )
     CHARACTER SET utf8 NOT NULL;
");

$installer->addAttribute(
    'customer',
    'ebizmarts_reviews_cntr_total',
    array(
        'type'                 => 'int',
        'input'                => 'hidden',
        'required'             => 0,
        'default'              => 0,
        'visible_on_front'     => 0,
        'user_defined'         => true,
    )
);
$installer->addAttribute(
    'customer',
    'ebizmarts_reviews_coupon_total',
    array(
        'type'                 => 'int',
        'input'                => 'hidden',
        'required'             => 0,
        'default'              => 0,
        'visible_on_front'     => 0,
        'user_defined'         => true,
    )
);

$installer->endSetup();