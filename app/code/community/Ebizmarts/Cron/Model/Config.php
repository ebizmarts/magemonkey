<?php
/**
 * Author: info@ebizmarts.com
 * Date: 9/16/15
 * Time: 4:22 PM
 * File: Config.php
 * Module: magemonkey
 */

class Ebizmarts_Cron_Model_Config
{
    const END_POINT = 'ebizmarts_cron/apiendpoint';
    const PK        = 'ebizmarts_cron/pk';
    const NAME      = 'ebizmarts_cron/name';
    const IMAGE     = 'ebizmarts_cron/image';
    const MERCHANT  = 'ebizmarts_cron/general/merchant';

    const ALL_OK        = 0;
    const WRONG_URL     = 1;
    const NO_MERCHANT   = 2;
    const UNPAYED       = 3;
    const NO_SUBSCRIPTION = 4;

}