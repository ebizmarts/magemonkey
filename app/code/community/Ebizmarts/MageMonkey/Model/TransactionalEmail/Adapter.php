<?php

/**
 * Transactional Email Adapter
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 */
class Ebizmarts_MageMonkey_Model_TransactionalEmail_Adapter
{
    const ADAPTER_STS      = 'sts';
    const ADAPTER_MANDRILL = 'mandrill';

    public static function factory($adapter)
    {
        switch($adapter) {
            case self::ADAPTER_STS:
                return new Ebizmarts_MageMonkey_Model_TransactionalEmail_STS();
                break;
            case self::ADAPTER_MANDRILL:
                return new Ebizmarts_MageMonkey_Model_TransactionalEmail_MANDRILL();
                break;
            default:
                throw new Exception('Invalid Transactional Email service selected.');
                break;
        }
    }
}
