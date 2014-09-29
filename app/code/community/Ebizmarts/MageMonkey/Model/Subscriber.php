<?php
/**
 * Created by PhpStorm.
 * User: santisp
 * Date: 25/09/14
 * Time: 12:26 PM
 */

class Ebizmarts_MageMonkey_Model_Subscriber extends Mage_Newsletter_Model_Subscriber
{
    public function sendUnsubscriptionEmail()
    {
        return $this;
    }
}