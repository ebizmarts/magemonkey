<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 6/14/13
 * Time   : 5:10 PM
 * File   : Config.php
 * Module : Ebizmarts_Magemonkey
 */
class Ebizmarts_Autoresponder_Model_Config
{
    const GENERAL_ACTIVE                = 'ebizmarts_autoresponder/general/active';
    const GENERAL_SENDER                = 'ebizmarts_autoresponder/general/identity';

    const NEWORDER_ACTIVE               = 'ebizmarts_autoresponder/neworder/active';
    const NEWORDER_DAYS                 = 'ebizmarts_autoresponder/neworder/days';
    const NEWORDER_CUSTOMER_GROUPS      = 'ebizmarts_autoresponder/neworder/customer';
    const NEWORDER_TEMPLATE             = 'ebizmarts_autoresponder/neworder/template';
    const NEWORDER_MANDRILL_TAG         = 'ebizmarts_autoresponder/neworder/mandrill-tag';
    const NEWORDER_SUBJECT              = 'ebizmarts_autoresponder/neworder/subject';

    const RELATED_ACTIVE                = 'ebizmarts_autoresponder/related/active';
    const RELATED_DAYS                  = 'ebizmarts_autoresponder/related/days';
    const RELATED_CUSTOMER_GROUPS       = 'ebizmarts_autoresponder/related/customer';
    const RELATED_TEMPLATE              = 'ebizmarts_autoresponder/related/template';
    const RELATED_MANDRILL_TAG          = 'ebizmarts_autoresponder/related/mandrill-tag';
    const RELATED_SUBJECT               = 'ebizmarts_autoresponder/related/subject';
    const RELATED_MAX                   = 'ebizmarts_autoresponder/related/max-related';

    const REVIEW_ACTIVE                 = 'ebizmarts_autoresponder/review/active';
    const REVIEW_DAYS                   = 'ebizmarts_autoresponder/review/days';
    const REVIEW_CUSTOMER_GROUPS        = 'ebizmarts_autoresponder/review/customer';
    const REVIEW_TEMPLATE               = 'ebizmarts_autoresponder/review/template';
    const REVIEW_MANDRILL_TAG           = 'ebizmarts_autoresponder/review/mandrill-tag';
    const REVIEW_SUBJECT                = 'ebizmarts_autoresponder/review/subject';

    const BIRTHDAY_ACTIVE               = 'ebizmarts_autoresponder/birthday/active';
    const BIRTHDAY_DAYS                 = 'ebizmarts_autoresponder/birthday/days';
    const BIRTHDAY_CUSTOMER_GROUPS      = 'ebizmarts_autoresponder/birthday/customer';
    const BIRTHDAY_TEMPLATE             = 'ebizmarts_autoresponder/birthday/template';
    const BIRTHDAY_SUBJECT              = 'ebizmarts_autoresponder/birthday/subject';
    const BIRTHDAY_MANDRILL_TAG         = 'ebizmarts_autoresponder/birthday/mandrill-tag';
    const BIRTHDAY_COUPON               = 'ebizmarts_autoresponder/birthday/coupon';
    const BIRTHDAY_CUSTOMER_COUPON      = 'ebizmarts_autoresponder/birthday/customer_coupon';
    const BIRTHDAY_AUTOMATIC            = 'ebizmarts_autoresponder/birthday/automatic';
    const BIRTHDAY_COUPON_CODE          = 'ebizmarts_autoresponder/birthday/coupon_code';
    const BIRTHDAY_EXPIRE               = 'ebizmarts_autoresponder/birthday/expire';
    const BIRTHDAY_LENGTH               = 'ebizmarts_autoresponder/birthday/length';
    const BIRTHDAY_DISCOUNT_TYPE        = 'ebizmarts_autoresponder/birthday/discounttype';
    const BIRTHDAY_DISCOUNT             = 'ebizmarts_autoresponder/birthday/discount';
    const BIRTHDAY_COUPON_LABEL         = 'ebizmarts_autoresponder/birthday/couponlabel';


    const NOACTIVITY_ACTIVE             = 'ebizmarts_autoresponder/noactivity/active';
    const NOACTIVITY_DAYS               = 'ebizmarts_autoresponder/noactivity/days';
    const NOACTIVITY_CUSTOMER_GROUPS    = 'ebizmarts_autoresponder/noactivity/customer';
    const NOACTIVITY_TEMPLATE           = 'ebizmarts_autoresponder/noactivity/template';
    const NOACTIVITY_MANDRILL_TAG       = 'ebizmarts_autoresponder/noactivity/mandrill-tag';
    const NOACTIVITY_SUBJECT            = 'ebizmarts_autoresponder/noactivity/subject';

    const WISHLIST_ACTIVE             = 'ebizmarts_autoresponder/wishlist/active';
    const WISHLIST_DAYS               = 'ebizmarts_autoresponder/wishlist/days';
    const WISHLIST_CUSTOMER_GROUPS    = 'ebizmarts_autoresponder/wishlist/customer';
    const WISHLIST_TEMPLATE           = 'ebizmarts_autoresponder/wishlist/template';
    const WISHLIST_MANDRILL_TAG       = 'ebizmarts_autoresponder/wishlist/mandrill-tag';
    const WISHLIST_SUBJECT            = 'ebizmarts_autoresponder/wishlist/subject';

    const VISITED_ACTIVE              = 'ebizmarts_autoresponder/visitedproducts/active';
    const VISITED_DAYS                = 'ebizmarts_autoresponder/visitedproducts/days';
    const VISITED_TEMPLATE            = 'ebizmarts_autoresponder/visitedproducts/template';
    const VISITED_MANDRILL_TAG        = 'ebizmarts_autoresponder/visitedproducts/mandrill_tag';
    const VISITED_SUBJECT             = 'ebizmarts_autoresponder/visitedproducts/subject';
    const VISITED_CUSTOMER_GROUPS     = 'ebizmarts_autoresponder/visitedproducts/customer';
    const VISITED_TIME                = 'ebizmarts_autoresponder/visitedproducts/time';
    const VISITED_MAX                 = 'ebizmarts_autoresponder/visitedproducts/max_visited';

    const COUPON_AUTOMATIC            = 2;
    const COUPON_MANUAL               = 1;
}