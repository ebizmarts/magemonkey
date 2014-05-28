<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_AbandonedCart
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_AbandonedCart_Model_Config
{
    const ACTIVE                            = "ebizmarts_abandonedcart/general/active";
    const EMAIL_TEMPLATE_XML_PATH           = 'ebizmarts_abandonedcart/general/template';
    const MANDRILL_TAG                      = 'ebizmarts_abandonedcart/general/mandrill-tag';
    const EMAIL_TEMPLATE_XML_PATH_W_COUPON  = 'ebizmarts_abandonedcart/general/coupon_template';
    const DAYS                              = "ebizmarts_abandonedcart/general/days";
    const UNIT                              = "ebizmarts_abandonedcart/general/unit";
    const SENDER                            = "ebizmarts_abandonedcart/general/identity";
    const MAXTIMES                          = "ebizmarts_abandonedcart/general/max";
    const COUPON_DAYS                       = "ebizmarts_abandonedcart/coupon/sendon";
    const SEND_COUPON                       = "ebizmarts_abandonedcart/coupon/create";
    const FIRST_DATE                        = "ebizmarts_abandonedcart/general/firstdate";
    const COUPON_AMOUNT                     = "ebizmarts_abandonedcart/coupon/discount";
    const COUPON_AUTOMATIC                  = "ebizmarts_abandonedcart/coupon/automatic";
    const COUPON_CODE                       = "ebizmarts_abandonedcart/coupon/couponcode";
    const COUPON_EXPIRE                     = "ebizmarts_abandonedcart/coupon/expire";
    const COUPON_TYPE                       = "ebizmarts_abandonedcart/coupon/discounttype";
    const COUPON_LENGTH                     = "ebizmarts_abandonedcart/coupon/length";
    const COUPON_LABEL                      = "ebizmarts_abandonedcart/coupon/couponlabel";
    const CUSTOMER_GROUPS                   = "ebizmarts_abandonedcart/general/customer";
    const SUBJECT                           = "ebizmarts_abandonedcart/general/subject";
    const LOG                               = "ebizmarts_abandonedcart/general/log";
    const AUTOLOGIN                         = "ebizmarts_abandonedcart/general/autologin";
    const ABANDONED_TAGS                    = 'global/ebizmarts_abandonedcart/mandrill-tag';
    const IN_DAYS                           = 0;
    const IN_HOURS                          = 1;
    const PAGE                              = 'ebizmarts_abandonedcart/general/page';
}