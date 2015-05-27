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
    const ACTIVE = "ebizmarts_abandonedcart/general/active";
    const FIRST_EMAIL_TEMPLATE_XML_PATH = 'ebizmarts_abandonedcart/general/template1';
    const SECOND_EMAIL_TEMPLATE_XML_PATH = 'ebizmarts_abandonedcart/general/template2';
    const THIRD_EMAIL_TEMPLATE_XML_PATH = 'ebizmarts_abandonedcart/general/template3';
    const FOURTH_EMAIL_TEMPLATE_XML_PATH = 'ebizmarts_abandonedcart/general/template4';
    const FIFTH_EMAIL_TEMPLATE_XML_PATH = 'ebizmarts_abandonedcart/general/template5';
    const MANDRILL_TAG = 'ebizmarts_abandonedcart/general/mandrill-tag';
    const EMAIL_TEMPLATE_XML_PATH_W_COUPON = 'ebizmarts_abandonedcart/general/coupon_template';
    const DAYS_1 = "ebizmarts_abandonedcart/general/days1";
    const DAYS_2 = "ebizmarts_abandonedcart/general/days2";
    const DAYS_3 = "ebizmarts_abandonedcart/general/days3";
    const DAYS_4 = "ebizmarts_abandonedcart/general/days4";
    const DAYS_5 = "ebizmarts_abandonedcart/general/days5";
    const UNIT = "ebizmarts_abandonedcart/general/unit";
    const SENDER = "ebizmarts_abandonedcart/general/identity";
    const MAXTIMES = "ebizmarts_abandonedcart/general/max";
    const MAXTIMES_NUM = 5;
    const CUSTOMER_GROUPS = "ebizmarts_abandonedcart/general/customer";
    const FIRST_SUBJECT = "ebizmarts_abandonedcart/general/subject1";
    const SECOND_SUBJECT = "ebizmarts_abandonedcart/general/subject2";
    const THIRD_SUBJECT = "ebizmarts_abandonedcart/general/subject3";
    const FOURTH_SUBJECT = "ebizmarts_abandonedcart/general/subject4";
    const FIFTH_SUBJECT = "ebizmarts_abandonedcart/general/subject5";
    const LOG = "ebizmarts_abandonedcart/general/log";
    const AUTOLOGIN = "ebizmarts_abandonedcart/general/autologin";
    const ABANDONED_TAGS = 'global/ebizmarts_abandonedcart/mandrill-tag';
    const IN_DAYS = 0;
    const IN_HOURS = 1;
    const PAGE = 'ebizmarts_abandonedcart/general/page';

    const AB_TESTING_ACTIVE = 'ebizmarts_abandonedcart/A_Btesting/active';
    const AB_TESTING_MANDRILL_SUFFIX = 'ebizmarts_abandonedcart/A_Btesting/mandrill_suffix';
    const AB_TESTING_FIRST_EMAIL = 'ebizmarts_abandonedcart/A_Btesting/template1';
    const AB_TESTING_SECOND_EMAIL = 'ebizmarts_abandonedcart/A_Btesting/template2';
    const AB_TESTING_THIRD_EMAIL = 'ebizmarts_abandonedcart/A_Btesting/template3';
    const AB_TESTING_FOURTH_EMAIL = 'ebizmarts_abandonedcart/A_Btesting/template4';
    const AB_TESTING_FIFTH_EMAIL = 'ebizmarts_abandonedcart/A_Btesting/template5';
    const AB_TESTING_EMAIL_TEMPLATE = 'ebizmarts_abandonedcart/A_Btesting/coupon_template';
    const AB_TESTING_FIRST_SUBJECT = "ebizmarts_abandonedcart/A_Btesting/subject1";
    const AB_TESTING_SECOND_SUBJECT = "ebizmarts_abandonedcart/A_Btesting/subject2";
    const AB_TESTING_THIRD_SUBJECT = "ebizmarts_abandonedcart/A_Btesting/subject3";
    const AB_TESTING_FOURTH_SUBJECT = "ebizmarts_abandonedcart/A_Btesting/subject4";
    const AB_TESTING_FIFTH_SUBJECT = "ebizmarts_abandonedcart/A_Btesting/subject5";
    const AB_TESTING_COUPON_SENDON = "ebizmarts_abandonedcart/A_Btesting/A_Btesting_sendon";


    const COUPON_DAYS = "ebizmarts_abandonedcart/coupon/sendon";
    const SEND_COUPON = "ebizmarts_abandonedcart/coupon/create";
    const FIRST_DATE = "ebizmarts_abandonedcart/general/firstdate";
    const COUPON_AMOUNT = "ebizmarts_abandonedcart/coupon/discount";
    const COUPON_AUTOMATIC = "ebizmarts_abandonedcart/coupon/automatic";
    const COUPON_CODE = "ebizmarts_abandonedcart/coupon/couponcode";
    const COUPON_EXPIRE = "ebizmarts_abandonedcart/coupon/expire";
    const COUPON_TYPE = "ebizmarts_abandonedcart/coupon/discounttype";
    const COUPON_LENGTH = "ebizmarts_abandonedcart/coupon/length";
    const COUPON_LABEL = "ebizmarts_abandonedcart/coupon/couponlabel";


    const ENABLE_POPUP = 'ebizmarts_abandonedcart/emailcatcher/popup_general';
    const POPUP_HEADING = 'ebizmarts_abandonedcart/emailcatcher/popup_heading';
    const POPUP_TEXT = 'ebizmarts_abandonedcart/emailcatcher/popup_text';
    const POPUP_FNAME = 'ebizmarts_abandonedcart/emailcatcher/popup_fname';
    const POPUP_LNAME = 'ebizmarts_abandonedcart/emailcatcher/popup_lname';
    const POPUP_WIDTH = 'ebizmarts_abandonedcart/emailcatcher/popup_width';
    const POPUP_HEIGHT = 'ebizmarts_abandonedcart/emailcatcher/popup_height';
    const POPUP_SUBSCRIPTION = 'ebizmarts_abandonedcart/emailcatcher/popup_subscription';
    const POPUP_CAN_CANCEL = 'ebizmarts_abandonedcart/emailcatcher/popup_cancel';
    const POPUP_COOKIE_TIME = 'ebizmarts_abandonedcart/emailcatcher/popup_cookie_time';
    const POPUP_INSIST = 'ebizmarts_abandonedcart/emailcatcher/popup_insist';
    const POPUP_CREATE_COUPON = 'ebizmarts_abandonedcart/emailcatcher/popup_coupon';
    const POPUP_COUPON_MANDRILL_TAG = 'ebizmarts_abandonedcart/emailcatcher/popup_coupon_mandrill_tag';
    const POPUP_COUPON_MAIL_SUBJECT = 'ebizmarts_abandonedcart/emailcatcher/popup_coupon_mail_subject';
    const POPUP_COUPON_TEMPLATE_XML_PATH = 'ebizmarts_abandonedcart/emailcatcher/popup_coupon_template';
    const POPUP_COUPON_AUTOMATIC = 'ebizmarts_abandonedcart/emailcatcher/popup_automatic';
    const POPUP_COUPON_CODE = 'ebizmarts_abandonedcart/emailcatcher/popup_coupon_code';
    const POPUP_COUPON_EXPIRE = 'ebizmarts_abandonedcart/emailcatcher/popup_expire';
    const POPUP_COUPON_LENGTH = 'ebizmarts_abandonedcart/emailcatcher/popup_length';
    const POPUP_COUPON_DISCOUNTTYPE = 'ebizmarts_abandonedcart/emailcatcher/popup_discounttype';
    const POPUP_COUPON_DISCOUNT = 'ebizmarts_abandonedcart/emailcatcher/popup_discount';
    const POPUP_COUPON_LABEL = 'ebizmarts_abandonedcart/emailcatcher/popup_couponlabel';

}