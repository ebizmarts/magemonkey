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
    const ADAPTER_STS      = 'STS';
    const ADAPTER_MANDRILL = 'MANDRILL';

    public static function factory($adapter)
    {
        switch( $adapter ) {
            case self::ADAPTER_STS:
                return new Varien_Image_Adapter_Gd();
                break;
            case self::ADAPTER_MANDRILL:
                return new Varien_Image_Adapter_Gd2();
                break;
            default:
                throw new Exception('Invalid Transactional Email service selected.');
                break;
        }
    }
}
