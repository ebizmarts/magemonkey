<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_Autoresponder
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_Autoresponder_Model_Config
{
    const GENERAL_ACTIVE = 'ebizmarts_autoresponder/general/active';
    const GENERAL_SENDER = 'ebizmarts_autoresponder/general/identity';

    const NEWORDER_ACTIVE = 'ebizmarts_autoresponder/neworder/active';
    const NEWORDER_DAYS = 'ebizmarts_autoresponder/neworder/days';
    const NEWORDER_CUSTOMER_GROUPS = 'ebizmarts_autoresponder/neworder/customer';
    const NEWORDER_TRIGGER = 'ebizmarts_autoresponder/neworder/trigger';
    const NEWORDER_ORDER_STATUS = 'ebizmarts_autoresponder/neworder/order_status';
    const NEWORDER_TEMPLATE = 'ebizmarts_autoresponder/neworder/template';
    const NEWORDER_MANDRILL_TAG = 'ebizmarts_autoresponder/neworder/mandrill-tag';
    const NEWORDER_SUBJECT = 'ebizmarts_autoresponder/neworder/subject';
    const NEWORDER_CRON_TIME = 'ebizmarts_autoresponder/neworder/cron-time';

    const RELATED_ACTIVE = 'ebizmarts_autoresponder/related/active';
    const RELATED_DAYS = 'ebizmarts_autoresponder/related/days';
    const RELATED_CUSTOMER_GROUPS = 'ebizmarts_autoresponder/related/customer';
    const RELATED_TEMPLATE = 'ebizmarts_autoresponder/related/template';
    const RELATED_MANDRILL_TAG = 'ebizmarts_autoresponder/related/mandrill-tag';
    const RELATED_SUBJECT = 'ebizmarts_autoresponder/related/subject';
    const RELATED_MAX = 'ebizmarts_autoresponder/related/max-related';
    const RELATED_STATUS = 'ebizmarts_autoresponder/related/status';
    const RELATED_CRON_TIME = 'ebizmarts_autoresponder/related/cron-time';

    const REVIEW_ACTIVE = 'ebizmarts_autoresponder/review/active';
    const REVIEW_DAYS = 'ebizmarts_autoresponder/review/days';
    const REVIEW_CUSTOMER_GROUPS = 'ebizmarts_autoresponder/review/customer';
    const REVIEW_TEMPLATE = 'ebizmarts_autoresponder/review/template';
    const REVIEW_MANDRILL_TAG = 'ebizmarts_autoresponder/review/mandrill-tag';
    const REVIEW_SUBJECT = 'ebizmarts_autoresponder/review/subject';
    const REVIEW_STATUS = 'ebizmarts_autoresponder/review/status';
    const REVIEW_HAS_COUPON = 'ebizmarts_autoresponder/review/coupon';
    const REVIEW_COUPON_CUSTOMER_GROUP = 'ebizmarts_autoresponder/review/customer_coupon';
    const REVIEW_COUPON_AUTOMATIC = 'ebizmarts_autoresponder/review/automatic';
    const REVIEW_COUPON_CODE = 'ebizmarts_autoresponder/review/coupon_code';
    const REVIEW_COUPON_EXPIRE = 'ebizmarts_autoresponder/review/expire';
    const REVIEW_COUPON_LENGTH = 'ebizmarts_autoresponder/review/length';
    const REVIEW_COUPON_DISCOUNT_TYPE = 'ebizmarts_autoresponder/review/discounttype';
    const REVIEW_COUPON_DISCOUNT = 'ebizmarts_autoresponder/review/discount';
    const REVIEW_COUPON_LABEL = 'ebizmarts_autoresponder/review/couponlabel';
    const REVIEW_COUPON_COUNTER = 'ebizmarts_autoresponder/review/coupon_counter';
    const REVIEW_COUPON_GENERAL_QUANTITY = 'ebizmarts_autoresponder/review/coupon_general_quantity';
    const REVIEW_COUPON_GENERAL_TYPE = 'ebizmarts_autoresponder/review/coupon_general_type';
    const REVIEW_COUPON_SPECIFIC_QUANTITY = 'ebizmarts_autoresponder/review/coupon_specific_quantity';
    const REVIEW_COUPON_ORDER_COUNTER = 'ebizmarts_autoresponder/review/coupon_order_counter';
    const REVIEW_COUPON_ORDER_ALMOST = 'ebizmarts_autoresponder/review/coupon_order_almost';
    const REVIEW_COUPON_ORDER_MAX = 'ebizmarts_autoresponder/review/coupon_order_max';
    const REVIEW_COUPON_MANDRILL_TAG = 'ebizmarts_autoresponder/review/coupon_mandrill_tag';
    const REVIEW_COUPON_SUBJECT = 'ebizmarts_autoresponder/review/coupon_mail_subject';
    const REVIEW_COUPON_EMAIL = 'ebizmarts_autoresponder/review/coupon_template';
    const REVIEW_CRON_TIME = 'ebizmarts_autoresponder/review/cron-time';

    const BIRTHDAY_ACTIVE = 'ebizmarts_autoresponder/birthday/active';
    const BIRTHDAY_DAYS = 'ebizmarts_autoresponder/birthday/days';
    const BIRTHDAY_CUSTOMER_GROUPS = 'ebizmarts_autoresponder/birthday/customer';
    const BIRTHDAY_TEMPLATE = 'ebizmarts_autoresponder/birthday/template';
    const BIRTHDAY_SUBJECT = 'ebizmarts_autoresponder/birthday/subject';
    const BIRTHDAY_MANDRILL_TAG = 'ebizmarts_autoresponder/birthday/mandrill-tag';
    const BIRTHDAY_COUPON = 'ebizmarts_autoresponder/birthday/coupon';
    const BIRTHDAY_CUSTOMER_COUPON = 'ebizmarts_autoresponder/birthday/customer_coupon';
    const BIRTHDAY_AUTOMATIC = 'ebizmarts_autoresponder/birthday/automatic';
    const BIRTHDAY_COUPON_CODE = 'ebizmarts_autoresponder/birthday/coupon_code';
    const BIRTHDAY_EXPIRE = 'ebizmarts_autoresponder/birthday/expire';
    const BIRTHDAY_LENGTH = 'ebizmarts_autoresponder/birthday/length';
    const BIRTHDAY_DISCOUNT_TYPE = 'ebizmarts_autoresponder/birthday/discounttype';
    const BIRTHDAY_DISCOUNT = 'ebizmarts_autoresponder/birthday/discount';
    const BIRTHDAY_COUPON_LABEL = 'ebizmarts_autoresponder/birthday/couponlabel';
    const BIRTHDAY_CRON_TIME = 'ebizmarts_autoresponder/birthday/cron-time';


    const NOACTIVITY_ACTIVE = 'ebizmarts_autoresponder/noactivity/active';
    const NOACTIVITY_DAYS = 'ebizmarts_autoresponder/noactivity/days';
    const NOACTIVITY_CUSTOMER_GROUPS = 'ebizmarts_autoresponder/noactivity/customer';
    const NOACTIVITY_TEMPLATE = 'ebizmarts_autoresponder/noactivity/template';
    const NOACTIVITY_MANDRILL_TAG = 'ebizmarts_autoresponder/noactivity/mandrill-tag';
    const NOACTIVITY_SUBJECT = 'ebizmarts_autoresponder/noactivity/subject';
    const NOACTIVITY_CRON_TIME = 'ebizmarts_autoresponder/noactivity/cron-time';

    const WISHLIST_ACTIVE = 'ebizmarts_autoresponder/wishlist/active';
    const WISHLIST_DAYS = 'ebizmarts_autoresponder/wishlist/days';
    const WISHLIST_CUSTOMER_GROUPS = 'ebizmarts_autoresponder/wishlist/customer';
    const WISHLIST_TEMPLATE = 'ebizmarts_autoresponder/wishlist/template';
    const WISHLIST_MANDRILL_TAG = 'ebizmarts_autoresponder/wishlist/mandrill-tag';
    const WISHLIST_SUBJECT = 'ebizmarts_autoresponder/wishlist/subject';
    const WISHLIST_CRON_TIME = 'ebizmarts_autoresponder/wishlist/cron-time';

    const VISITED_ACTIVE = 'ebizmarts_autoresponder/visitedproducts/active';
    const VISITED_DAYS = 'ebizmarts_autoresponder/visitedproducts/days';
    const VISITED_TEMPLATE = 'ebizmarts_autoresponder/visitedproducts/template';
    const VISITED_MANDRILL_TAG = 'ebizmarts_autoresponder/visitedproducts/mandrill_tag';
    const VISITED_SUBJECT = 'ebizmarts_autoresponder/visitedproducts/subject';
    const VISITED_CUSTOMER_GROUPS = 'ebizmarts_autoresponder/visitedproducts/customer';
    const VISITED_TIME = 'ebizmarts_autoresponder/visitedproducts/time';
    const VISITED_MAX = 'ebizmarts_autoresponder/visitedproducts/max_visited';
    const VISITED_CRON_TIME = 'ebizmarts_autoresponder/visitedproducts/cron-time';

    const BACKTOSTOCK_ACTIVE = 'ebizmarts_autoresponder/backtostock/active';
    const BACKTOSTOCK_TEMPLATE = 'ebizmarts_autoresponder/backtostock/template';
    const BACKTOSTOCK_SUBJECT = 'ebizmarts_autoresponder/backtostock/subject';
    const BACKTOSTOCK_MANDRILL_TAG = 'ebizmarts_autoresponder/backtostock/mandrill_tag';
    const BACKTOSTOCK_ALLOW_GUESTS = 'ebizmarts_autoresponder/backtostock/allow_guests';
    const BACKTOSTOCK_MAIL_TYPE_NAME = 'back to stock';
    const BACKTOSTOCK_CRON_TIME = 'ebizmarts_autoresponder/backtostock/cron-time';

    const COUPON_AUTOMATIC = 2;
    const COUPON_MANUAL = 1;
    const COUPON_GENERAL = 2;
    const COUPON_PER_ORDER = 1;
    const TYPE_EACH = 1;
    const TYPE_ONCE = 2;
    const TYPE_SPECIFIC = 3;
}